<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
function fail_upload(string $message, int $status=400): never {
  http_response_code($status);
  echo json_encode(['success'=>false,'message'=>$message], JSON_UNESCAPED_UNICODE);
  exit;
}
require_once __DIR__ . '/auth.php';
lav_auth_required();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') fail_upload('Método no permitido.',405);
$id=(string)($_POST['upload_id'] ?? '');
if (!preg_match('/^[a-f0-9]{32}$/',$id)) fail_upload('Identificador de carga inválido.');
$index=filter_var($_POST['index'] ?? null,FILTER_VALIDATE_INT);
$total=filter_var($_POST['total'] ?? null,FILTER_VALIDATE_INT);
if ($index===false || $total===false || $index<0 || $total<1 || $index >= $total || $total>20000) fail_upload('Secuencia de carga inválida.');
if (!isset($_FILES['chunk']) || !is_array($_FILES['chunk']) || ($_FILES['chunk']['error'] ?? 1)!==UPLOAD_ERR_OK) fail_upload('No se recibió el bloque de la fotografía.',413);
$part=$_FILES['chunk'];
if (!is_uploaded_file((string)$part['tmp_name'])) fail_upload('Bloque de carga inválido.');
$bytes=(int)($part['size'] ?? 0);
if ($bytes<1 || $bytes>2*1024*1024) fail_upload('Bloque fuera del tamaño permitido.',413);
$digest=strtolower((string)($_POST['sha256'] ?? ''));
if (!preg_match('/^[a-f0-9]{64}$/',$digest) || !hash_equals($digest,hash_file('sha256',(string)$part['tmp_name']))) fail_upload('El bloque llegó dañado. Reintenta la carga.',422);
$media=__DIR__.'/../data/media/';
$tmpRoot=$media.'.chunks/';
if (!is_dir($tmpRoot) && !mkdir($tmpRoot,0755,true) && !is_dir($tmpRoot)) fail_upload('No se pudo crear almacenamiento temporal.',500);
if (!is_file($tmpRoot.'.htaccess')) @file_put_contents($tmpRoot.'.htaccess',"Options -Indexes\nRequire all denied\n");
$root=$tmpRoot.$id.'/';
if (!is_dir($root) && !mkdir($root,0755,true) && !is_dir($root)) fail_upload('No se pudo crear la sesión de carga.',500);
$lock=fopen($root.'.lock','c+');
if (!$lock || !flock($lock,LOCK_EX)) fail_upload('No se pudo bloquear la carga.',503);
$destination=$root.sprintf('%06d',$index).'.part';
if (is_file($destination)) {
  if (!hash_equals($digest,hash_file('sha256',$destination))) fail_upload('El bloque ya existe con contenido distinto.',409);
} elseif (!move_uploaded_file((string)$part['tmp_name'],$destination)) {
  fail_upload('No se pudo guardar el bloque.',500);
}
if ($index!==$total-1) { echo json_encode(['success'=>true,'complete'=>false,'index'=>$index]);exit; }
for ($i=0;$i<$total;$i++) if (!is_file($root.sprintf('%06d',$i).'.part')) fail_upload('Faltan bloques; reintenta la última parte.',409);
$assembled=tempnam($media,'upload_');
if (!$assembled) fail_upload('No se pudo preparar el archivo final.',500);
$writer=fopen($assembled,'wb');
if (!$writer) { @unlink($assembled); fail_upload('No se pudo abrir el archivo final.',500); }
for ($i=0;$i<$total;$i++){
  $reader=fopen($root.sprintf('%06d',$i).'.part','rb');
  if (!$reader || stream_copy_to_stream($reader,$writer)===false) {
    if ($reader) fclose($reader);
    fclose($writer);@unlink($assembled);fail_upload('No se pudo reconstruir la fotografía.',500);
  }
  fclose($reader);
}
fclose($writer);
$finfo=new finfo(FILEINFO_MIME_TYPE);
$mime=$finfo->file($assembled);
$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/avif'=>'avif','image/bmp'=>'bmp'];
if (!isset($ext[$mime])) { @unlink($assembled);fail_upload('Formato de imagen incompatible.',415); }
$name='img_'.bin2hex(random_bytes(12)).'.'.$ext[$mime];
if (!rename($assembled,$media.$name)) { @unlink($assembled);fail_upload('No se pudo completar la fotografía.',500); }
@chmod($media.$name,0644);
for($i=0;$i<$total;$i++) @unlink($root.sprintf('%06d',$i).'.part');
@flock($lock,LOCK_UN);fclose($lock);@unlink($root.'.lock');@rmdir($root);
$appPath=rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/upload-chunk.php'))),'/');
$url=($appPath===''?'':$appPath).'/data/media/'.$name;
echo json_encode(['success'=>true,'complete'=>true,'url'=>$url,'name'=>$name,'mime'=>$mime,'size'=>filesize($media.$name)],JSON_UNESCAPED_SLASHES);
