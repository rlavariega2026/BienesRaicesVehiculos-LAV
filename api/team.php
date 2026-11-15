<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
lav_auth_required();
if (lav_auth_role() !== 'admin') lav_auth_respond(['success'=>false,'message'=>'Solo lavariega puede administrar usuarios.'],403);
$dir=lav_team_prepare();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if ($method==='GET') {
  $users=[];
  foreach (glob($dir.'user-*.json')?:[] as $path) {
    $v=json_decode((string)file_get_contents($path),true);
    if (is_array($v)) $users[]=['username'=>$v['username']??'', 'name'=>$v['name']??'', 'active'=>!empty($v['active'])];
  }
  $invites=[];
  foreach (glob($dir.'invite-*.json')?:[] as $path) {
    $v=json_decode((string)file_get_contents($path),true);
    if (is_array($v) && ($v['expires']??0)>=time()) $invites[]=['username'=>$v['username']??'', 'name'=>$v['name']??'', 'expires'=>$v['expires']??0];
  }
  lav_auth_respond(['success'=>true,'users'=>$users,'invites'=>$invites]);
}
if ($method!=='POST') lav_auth_respond(['success'=>false,'message'=>'Método no permitido.'],405);
$origin=(string)($_SERVER['HTTP_ORIGIN']??'');
if ($origin!=='') {
  $host=(string)($_SERVER['HTTP_HOST']??'');
  if (strtolower((string)(parse_url($origin,PHP_URL_HOST)??'')) !== strtolower(preg_replace('/:\\d+$/','',$host))) lav_auth_respond(['success'=>false,'message'=>'Origen no autorizado.'],403);
}
$data=json_decode((string)file_get_contents('php://input'),true);
if (!is_array($data)) lav_auth_respond(['success'=>false,'message'=>'Solicitud inválida.'],400);
$action=(string)($data['action']??'');
if ($action==='invite') {
  $username=strtolower(trim((string)($data['username']??'')));
  $name=trim((string)($data['name']??''));
  if (!preg_match('/^[a-z0-9_-]{3,40}$/',$username) || $username==='lavariega') lav_auth_respond(['success'=>false,'message'=>'Usuario inválido. Usa entre 3 y 40 letras, números o guiones.'],422);
  if ($name==='') $name=$username;
  if (mb_strlen($name)>80) lav_auth_respond(['success'=>false,'message'=>'Nombre demasiado largo.'],422);
  if (is_file(lav_team_user_file($username))) lav_auth_respond(['success'=>false,'message'=>'Ese usuario ya existe.'],409);
  foreach (glob($dir.'invite-*.json')?:[] as $path) {
    $v=json_decode((string)file_get_contents($path),true);
    if (is_array($v) && ($v['username']??'')===$username) @unlink($path);
  }
  $token=bin2hex(random_bytes(32));
  $hash=hash('sha256',$token);
  $inv=['username'=>$username,'name'=>$name,'expires'=>time()+7*86400,'created'=>date('c')];
  if (file_put_contents($dir.'invite-'.$hash.'.json',json_encode($inv,JSON_UNESCAPED_UNICODE),LOCK_EX)===false) lav_auth_respond(['success'=>false,'message'=>'No se pudo generar la invitación.'],500);
  $appPath=rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME']??'/api/team.php'))),'/');
  $url='https://'.($_SERVER['HTTP_HOST']??'lavdigitalmkt.com').$appPath.'/?invite='.$token;
  lav_auth_respond(['success'=>true,'url'=>$url,'username'=>$username,'expires'=>$inv['expires']]);
}
if ($action==='revoke') {
  $username=strtolower(trim((string)($data['username']??'')));
  if ($username==='' || $username==='lavariega' || !preg_match('/^[a-z0-9_-]{3,40}$/',$username)) lav_auth_respond(['success'=>false,'message'=>'No se puede revocar esa cuenta.'],422);
  $path=lav_team_user_file($username);
  if (is_file($path)) {
    $v=json_decode((string)file_get_contents($path),true);
    if (is_array($v)) {
      $v['active']=false;
      file_put_contents($path,json_encode($v,JSON_UNESCAPED_UNICODE),LOCK_EX);
    }
  }
  foreach (glob($dir.'invite-*.json')?:[] as $invPath) {
    $v=json_decode((string)file_get_contents($invPath),true);
    if (is_array($v)&&($v['username']??'')===$username) @unlink($invPath);
  }
  lav_auth_respond(['success'=>true,'username'=>$username]);
}
lav_auth_respond(['success'=>false,'message'=>'Acción no reconocida.'],400);
