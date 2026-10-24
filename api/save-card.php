<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$raw=file_get_contents('php://input');
if (!$raw || strlen($raw)>12000000) { http_response_code(413); echo json_encode(['error'=>'La tarjeta es demasiado grande']); exit; }
$data=json_decode($raw,true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'Datos inválidos']); exit; }
$dir=__DIR__.'/data'; if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) { http_response_code(500); echo json_encode(['error'=>'No se pudo crear el almacenamiento']); exit; }
$id=bin2hex(random_bytes(8)); $data['_id']=$id;
$saved=file_put_contents($dir.'/'.$id.'.json',json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
if ($saved===false) { http_response_code(500); echo json_encode(['error'=>'No se pudo guardar la tarjeta en el servidor']); exit; }
/* Fuerza el enlace público a la ruta de esta aplicación, no a la raíz del sitio ni al editor genérico. */
$base='/bienes-raices-vehiculos/';
$url='https://lavdigitalmkt.com'.$base.'?card='.rawurlencode($id);
echo json_encode(['id'=>$id,'url'=>$url],JSON_UNESCAPED_SLASHES);
?>