<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$id=isset($_GET['id'])?$_GET['id']:'';
if (!preg_match('/^[a-f0-9]{16}$/',$id)) { http_response_code(400); echo json_encode(['error'=>'Identificador inválido']); exit; }
$file=__DIR__.'/data/'.$id.'.json';
if (!is_file($file)) { http_response_code(404); echo json_encode(['error'=>'Tarjeta no encontrada']); exit; }
$data=json_decode(file_get_contents($file),true);
if (!is_array($data)) { http_response_code(500); echo json_encode(['error'=>'Tarjeta dañada']); exit; }
echo json_encode(['card'=>$data],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
?>