<?php
declare(strict_types=1);

/* LAV Studio: authentication shared by the administrator's private API calls. */
function lav_auth_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    session_name('LAV_BRV_ADMIN');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/auth.php'))), '/') . '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
function lav_auth_path(): string {
    return __DIR__ . '/../data/cards/admin-auth.json';
}
function lav_auth_config(): ?array {
    $path=lav_auth_path();
    if (!is_file($path)) return null;
    $v=json_decode((string)file_get_contents($path),true);
    if (!is_array($v)) return [];
    // Update the previous default administrator name without changing its password.
    if (($v['username'] ?? '') === 'administrador' && !empty($v['password_hash'])) {
        $v['username']='lavariega';
        $encoded=json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if ($encoded!==false) @file_put_contents($path,$encoded,LOCK_EX);
        if (isset($_SESSION['lav_admin']) && $_SESSION['lav_admin']==='administrador') {
            $_SESSION['lav_admin']='lavariega';
        }
    }
    return $v;
}
function lav_team_dir(): string {
    return __DIR__ . '/../data/private/';
}
function lav_team_prepare(): string {
    $d=lav_team_dir();
    if (!is_dir($d) && !mkdir($d,0755,true) && !is_dir($d)) throw new RuntimeException('No se pudo crear el directorio privado.');
    if (!is_file($d.'.htaccess')) @file_put_contents($d.'.htaccess',"Options -Indexes\nRequire all denied\n");
    return $d;
}
function lav_team_user_file(string $name): string {
    return lav_team_dir().'user-'.strtolower($name).'.json';
}
function lav_team_get_user(string $name): ?array {
    if (!preg_match('/^[a-z0-9_-]{3,40}$/',$name)) return null;
    $path=lav_team_user_file($name);
    if (!is_file($path)) return null;
    $v=json_decode((string)file_get_contents($path),true);
    return is_array($v) ? $v : null;
}
function lav_auth_role(): ?string {
    lav_auth_start();
    $username=(string)($_SESSION['lav_admin'] ?? '');
    if ($username==='') return null;
    $cfg=lav_auth_config();
    if ($cfg!==null && !empty($cfg['password_hash']) && hash_equals((string)($cfg['username']??''),$username)) return 'admin';
    $user=lav_team_get_user($username);
    if ($user===null || empty($user['active']) || empty($user['password_hash'])) return null;
    if (!empty($user['session_version']) && !hash_equals((string)$user['session_version'],(string)($_SESSION['lav_user_version'] ?? ''))) return null;
    return 'editor';
}
function lav_auth_ok(): bool {
    lav_auth_start();
    $config=lav_auth_config();
    return lav_auth_role() !== null;
}
function lav_auth_respond(array $payload,int $status=200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}
function lav_auth_required(): void {
    if (!lav_auth_ok()) lav_auth_respond(['success'=>false,'message'=>'Inicia sesión como administrador para continuar.'],401);
}
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    lav_auth_start();
    header('Cache-Control: no-store, max-age=0');
    $config=lav_auth_config();
    if (!empty($_SESSION['lav_admin']) && !empty($_SESSION['lav_last_activity']) && time()-(int)$_SESSION['lav_last_activity']>12*3600) {
        $_SESSION=[];session_regenerate_id(true);
    }
    if (!empty($_SESSION['lav_admin'])) $_SESSION['lav_last_activity']=time();
    $method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        lav_auth_respond(['success'=>true,'configured'=>$config !== null,
          'authenticated'=>lav_auth_ok(),
          'username'=>lav_auth_ok()?(string)($_SESSION['lav_admin'] ?? ''):null,
          'role'=>lav_auth_role()]);
    }
    if ($method !== 'POST') lav_auth_respond(['success'=>false,'message'=>'Método no permitido.'],405);
    $origin=(string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $host=strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $originHost=strtolower((string)(parse_url($origin,PHP_URL_HOST) ?? ''));
        if ($originHost === '' || $originHost !== strtolower((string)(parse_url('https://'.$host,PHP_URL_HOST) ?? $host)))
            lav_auth_respond(['success'=>false,'message'=>'Origen no autorizado.'],403);
    }
    $data=json_decode((string)file_get_contents('php://input'),true);
    if (!is_array($data)) lav_auth_respond(['success'=>false,'message'=>'Datos de acceso no válidos.'],400);
    $action=(string)($data['action'] ?? '');
    if ($action === 'logout') {
        $_SESSION=[];session_regenerate_id(true);
        lav_auth_respond(['success'=>true,'authenticated'=>false]);
    }
    $username=trim((string)($data['username'] ?? ''));
    $password=(string)($data['password'] ?? '');
    if ($action === 'setup') {
        if ($config !== null) lav_auth_respond(['success'=>false,'message'=>'El administrador ya está configurado.'],409);
        if ($username !== 'lavariega')
            lav_auth_respond(['success'=>false,'message'=>'El usuario administrador debe ser lavariega.'],422);
        if (strlen($password)<12 || strlen($password)>200)
            lav_auth_respond(['success'=>false,'message'=>'Elige una contraseña de al menos 12 caracteres.'],422);
        $dir=dirname(lav_auth_path());
        if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir))
            lav_auth_respond(['success'=>false,'message'=>'No se pudo crear la carpeta del administrador.'],500);
        if (!is_file($dir.'/.htaccess')) @file_put_contents($dir.'/.htaccess',"Options -Indexes\nRequire all denied\n");
        $file=@fopen(lav_auth_path(),'x');
        if (!$file) lav_auth_respond(['success'=>false,'message'=>'El usuario ya fue creado; entra con tu contraseña.'],409);
        $saved=['username'=>$username,'password_hash'=>password_hash($password,PASSWORD_DEFAULT),'created_at'=>date('c')];
        $json=json_encode($saved,JSON_UNESCAPED_UNICODE);
        if ($json === false || fwrite($file,$json)===false) {
            fclose($file);@unlink(lav_auth_path());
            lav_auth_respond(['success'=>false,'message'=>'No se pudo guardar el administrador.'],500);
        }
        fclose($file);@chmod(lav_auth_path(),0600);
        session_regenerate_id(true);$_SESSION['lav_admin']=$username;
        lav_auth_respond(['success'=>true,'configured'=>true,'authenticated'=>true,'username'=>$username,'role'=>$adminValid?'admin':'editor']);
    }
    if ($action === 'invite_check') {
        $token=strtolower((string)($data['token']??''));
        if (!preg_match('/^[a-f0-9]{64}$/',$token)) lav_auth_respond(['success'=>false,'message'=>'La invitación no es válida.'],400);
        $path=lav_team_dir().'invite-'.hash('sha256',$token).'.json';
        $inv=is_file($path)?json_decode((string)file_get_contents($path),true):null;
        if (!is_array($inv) || ($inv['expires']??0)<time()) lav_auth_respond(['success'=>false,'message'=>'La invitación venció o ya fue utilizada.'],404);
        lav_auth_respond(['success'=>true,'username'=>$inv['username'],'name'=>$inv['name']??'']);
    }
    if ($action === 'invite_accept') {
        $token=strtolower((string)($data['token']??''));
        if (!preg_match('/^[a-f0-9]{64}$/',$token)) lav_auth_respond(['success'=>false,'message'=>'Invitación inválida.'],400);
        $path=lav_team_dir().'invite-'.hash('sha256',$token).'.json';
        $invite=is_file($path)?json_decode((string)file_get_contents($path),true):null;
        if (!is_array($invite) || ($invite['expires']??0)<time()) lav_auth_respond(['success'=>false,'message'=>'La invitación venció o ya fue utilizada.'],404);
        $invited=(string)$invite['username'];
        if (strlen($password)<12 || strlen($password)>200) lav_auth_respond(['success'=>false,'message'=>'La contraseña requiere al menos 12 caracteres.'],422);
        $existing=lav_team_get_user($invited);
        $isReset=($invite['purpose']??'')==='reset';
        if ($isReset && $existing===null) lav_auth_respond(['success'=>false,'message'=>'El usuario ya no existe.'],404);
        if (!$isReset && $existing!==null) lav_auth_respond(['success'=>false,'message'=>'Ese usuario ya tiene una cuenta.'],409);
        $record=$isReset?$existing:[];
        $record['username']=$invited;
        $record['name']=(string)($invite['name']??$invited);
        $record['password_hash']=password_hash($password,PASSWORD_DEFAULT);
        $record['active']=$isReset?(!empty($existing['active'])):true;
        $record['created_at']=$record['created_at']??date('c');
        $record['session_version']=bin2hex(random_bytes(12));
        $handle=@fopen(lav_team_user_file($invited),$isReset?'c+':'x');
        if (!$handle) lav_auth_respond(['success'=>false,'message'=>'No se pudo activar la cuenta.'],409);
        if (!flock($handle,LOCK_EX)) {fclose($handle);lav_auth_respond(['success'=>false,'message'=>'No se pudo proteger la cuenta.'],500);}
        ftruncate($handle,0);rewind($handle);
        fwrite($handle,json_encode($record,JSON_UNESCAPED_UNICODE));fflush($handle);flock($handle,LOCK_UN);fclose($handle);
        @unlink($path);
        session_regenerate_id(true);$_SESSION['lav_admin']=$invited;$_SESSION['lav_user_version']=$record['session_version'];
        lav_auth_respond(['success'=>true,'authenticated'=>true,'username'=>$invited,'role'=>'editor']);
    }
    if ($action === 'login') {
        if ($config === null) lav_auth_respond(['success'=>false,'message'=>'Primero configura al administrador.'],409);
        $attempts=(int)($_SESSION['lav_login_attempts'] ?? 0);
        if ($attempts >= 12) lav_auth_respond(['success'=>false,'message'=>'Demasiados intentos. Cierra el navegador e inténtalo más tarde.'],429);
        $registered=lav_team_get_user($username);
        $adminValid=!empty($config['password_hash']) &&
            hash_equals((string)($config['username'] ?? ''),$username) &&
            password_verify($password,(string)$config['password_hash']);
        $editorValid=$registered!==null && !empty($registered['active']) &&
            !empty($registered['password_hash']) &&
            password_verify($password,(string)$registered['password_hash']);
        if (!$adminValid && !$editorValid) {
            $_SESSION['lav_login_attempts']=$attempts+1;
            lav_auth_respond(['success'=>false,'message'=>'Usuario o contraseña incorrectos.'],401);
        }
        session_regenerate_id(true);$_SESSION['lav_admin']=$username;
        $_SESSION['lav_user_version']=$adminValid?'':(string)($registered['session_version']??'');
        $_SESSION['lav_login_attempts']=0;
        lav_auth_respond(['success'=>true,'configured'=>true,'authenticated'=>true,'username'=>$username]);
    }
    lav_auth_respond(['success'=>false,'message'=>'Acción desconocida.'],400);
}
