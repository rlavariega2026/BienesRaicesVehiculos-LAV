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
function lav_auth_ok(): bool {
    lav_auth_start();
    $config=lav_auth_config();
    return $config !== null && !empty($config['password_hash']) &&
      !empty($_SESSION['lav_admin']) && is_string($_SESSION['lav_admin']) &&
      hash_equals((string)($config['username'] ?? ''), $_SESSION['lav_admin']);
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
    $method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        lav_auth_respond(['success'=>true,'configured'=>$config !== null,
          'authenticated'=>lav_auth_ok(),
          'username'=>lav_auth_ok()?(string)($_SESSION['lav_admin'] ?? ''):null]);
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
        lav_auth_respond(['success'=>true,'configured'=>true,'authenticated'=>true,'username'=>$username]);
    }
    if ($action === 'login') {
        if ($config === null) lav_auth_respond(['success'=>false,'message'=>'Primero configura al administrador.'],409);
        $attempts=(int)($_SESSION['lav_login_attempts'] ?? 0);
        if ($attempts >= 12) lav_auth_respond(['success'=>false,'message'=>'Demasiados intentos. Cierra el navegador e inténtalo más tarde.'],429);
        if (empty($config['password_hash']) ||
            !hash_equals((string)($config['username'] ?? ''),$username) ||
            !password_verify($password,(string)$config['password_hash'])) {
            $_SESSION['lav_login_attempts']=$attempts+1;
            lav_auth_respond(['success'=>false,'message'=>'Usuario o contraseña incorrectos.'],401);
        }
        session_regenerate_id(true);$_SESSION['lav_admin']=$username;$_SESSION['lav_login_attempts']=0;
        lav_auth_respond(['success'=>true,'configured'=>true,'authenticated'=>true,'username'=>$username]);
    }
    lav_auth_respond(['success'=>false,'message'=>'Acción desconocida.'],400);
}
