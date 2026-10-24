<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$raw=file_get_contents('php://input');
if (!$raw || strlen($raw)>12000000) { http_response_code(413); echo json_encode(['error'=>'La tarjeta es demasiado grande']); exit; }
$data=json_decode($raw,true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'Datos inválidos']); exit; }
$dir=__DIR__.'/data'; if (!is_dir($dir)) mkdir($dir,0755,true);
$id=bin2hex(random_bytes(8)); $data['_id']=$id;
$saved=file_put_contents($dir.'/'.$id.'.json',json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
if ($saved===false) { http_response_code(500); echo json_encode(['error'=>'No se pudo guardar la tarjeta en el servidor']); exit; }
$base=rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])),'/');
$url='https://'.$_SERVER['HTTP_HOST'].$base.'/?card='.$id;
echo json_encode(['id'=>$id,'url'=>$url]);
?>