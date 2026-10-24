<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax','path'=>'/']);
session_start();
$dir = __DIR__ . '/../data/cards/';
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
  http_response_code(500); echo json_encode(['success'=>false,'message'=>'No se pudo crear el almacenamiento.']); exit;
}
$deny = $dir . '.htaccess';
if (!file_exists($deny)) @file_put_contents($deny, "Options -Indexes\n<FilesMatch \"\\.(json|txt|log)$\">\nRequire all denied\n</FilesMatch>\n");
$file = $dir . 'admin-auth.json';
function auth_reply(array $data, int $status=200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
$configured = is_file($file);
$authenticated = !empty($_SESSION['lav_admin']) && $_SESSION['lav_admin'] === true;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') auth_reply(['success'=>true,'configured'=>$configured,'authenticated'=>$authenticated]);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') auth_reply(['success'=>false,'message'=>'Método no permitido.'],405);
$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw)>4096) auth_reply(['success'=>false,'message'=>'Solicitud no válida.'],400);
$input = json_decode($raw,true);
if (!is_array($input)) auth_reply(['success'=>false,'message'=>'Solicitud no válida.'],400);
$action = (string)($input['action'] ?? '');
$password = (string)($input['password'] ?? '');
if ($action === 'setup') {
  if ($configured) auth_reply(['success'=>false,'message'=>'La contraseña ya está configurada. Inicia sesión.'],409);
  if (strlen($password)<10) auth_reply(['success'=>false,'message'=>'Usa una contraseña de al menos 10 caracteres.'],422);
  $saved = json_encode(['password_hash'=>password_hash($password,PASSWORD_DEFAULT),'created_at'=>date('c')],JSON_UNESCAPED_SLASHES);
  if ($saved===false || file_put_contents($file,$saved,LOCK_EX)===false) auth_reply(['success'=>false,'message'=>'No se pudo guardar la configuración de acceso.'],500);
  session_regenerate_id(true); $_SESSION['lav_admin']=true;
  auth_reply(['success'=>true,'authenticated'=>true]);
}
if ($action === 'login') {
  if (!$configured) auth_reply(['success'=>false,'message'=>'Primero configura la contraseña de administrador.'],409);
  $saved = json_decode((string)file_get_contents($file),true);
  if (!is_array($saved) || !isset($saved['password_hash']) || !password_verify($password,(string)$saved['password_hash'])) auth_reply(['success'=>false,'message'=>'Contraseña incorrecta.'],401);
  session_regenerate_id(true); $_SESSION['lav_admin']=true;
  auth_reply(['success'=>true,'authenticated'=>true]);
}
if ($action === 'logout') {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',$p['secure'],$p['httponly']); }
  session_destroy(); auth_reply(['success'=>true,'authenticated'=>false]);
}
auth_reply(['success'=>false,'message'=>'Acción no reconocida.'],400);
