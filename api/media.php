<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
function media_reply(array $data, int $status=200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') media_reply(['success'=>false,'message'=>'Método no permitido.'],405);
if (!isset($_FILES['image']) || !is_array($_FILES['image'])) media_reply(['success'=>false,'message'=>'No se recibió ninguna imagen.'],400);
$file=$_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) media_reply(['success'=>false,'message'=>'La carga falló. Elige una imagen más pequeña.'],400);
if (($file['size'] ?? 0)>8*1024*1024) media_reply(['success'=>false,'message'=>'La imagen supera el límite de 8 MB.'],413);
$tmp=(string)($file['tmp_name'] ?? '');
if (!is_uploaded_file($tmp)) media_reply(['success'=>false,'message'=>'El archivo recibido no es válido.'],400);
$finfo=new finfo(FILEINFO_MIME_TYPE);
$mime=$finfo->file($tmp);
$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
if (!isset($extensions[$mime])) media_reply(['success'=>false,'message'=>'Formato no permitido. Usa JPG, PNG o WEBP.'],415);
$dir=__DIR__.'/../data/media/';
if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) media_reply(['success'=>false,'message'=>'No se pudo crear la carpeta de imágenes.'],500);
$guard=$dir.'.htaccess';
if (!file_exists($guard)) @file_put_contents($guard,"Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh)$\">\nRequire all denied\n</FilesMatch>\n");
try { $name='img_'.bin2hex(random_bytes(12)).'.'.$extensions[$mime]; }
catch (Throwable $e) { $name='img_'.str_replace('.','',uniqid('',true)).'.'.$extensions[$mime]; }
$destination=$dir.$name;
if (!move_uploaded_file($tmp,$destination)) media_reply(['success'=>false,'message'=>'No se pudo guardar la imagen en el servidor.'],500);
$appPath=rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/media.php'))),'/');
$url=($appPath===''?'':$appPath).'/data/media/'.$name;
media_reply(['success'=>true,'url'=>$url,'name'=>$name,'mime'=>$mime,'size'=>(int)filesize($destination)]);
