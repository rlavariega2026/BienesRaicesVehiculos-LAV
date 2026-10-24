<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$data=json_decode(file_get_contents('php://input'),true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'Datos inválidos']); exit; }
$dir=__DIR__.'/data'; if (!is_dir($dir)) mkdir($dir,0755,true);
$id=bin2hex(random_bytes(8)); $data['_id']=$id;
file_put_contents($dir.'/'.$id.'.json',json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
$base=rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])),'/');
$url='https://'.$_SERVER['HTTP_HOST'].$base.'/?card='.$id;
echo json_encode(['id'=>$id,'url'=>$url]);
?>