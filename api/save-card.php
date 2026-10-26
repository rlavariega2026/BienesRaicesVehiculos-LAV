<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$storageDir = __DIR__ . '/../data/cards/';
if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'No se pudo crear el almacenamiento.']); exit;
}
$denyFile = $storageDir . '.htaccess';
if (!file_exists($denyFile)) {
    @file_put_contents($denyFile, "Options -Indexes\n<FilesMatch \"\\.(json|txt|log)$\">\nRequire all denied\n</FilesMatch>\n");
}
function respond(array $payload, int $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function clean_id(string $id): string {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $id) ?? '';
}
function card_path(string $dir, string $id): string { return $dir . clean_id($id) . '.json'; }
function load_card(string $path): ?array {
    if (!is_file($path)) return null;
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? $data : null;
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $action = (string)($_GET['action'] ?? '');
    if ($action === 'list') {
        $items = [];
        foreach (glob($storageDir . '*.json') ?: [] as $path) {
            if (basename($path) === 'admin-auth.json') continue;
            $item = load_card($path);
            if (is_array($item)) $items[] = $item;
        }
        usort($items, static fn($a,$b) => strcmp((string)($b['updatedAt'] ?? $b['createdAt'] ?? ''), (string)($a['updatedAt'] ?? $a['createdAt'] ?? '')));
        respond(['success'=>true,'cards'=>$items]);
    }
    $id = clean_id((string)($_GET['id'] ?? ''));
    if ($id === '') respond(['success'=>false,'message'=>'Falta el identificador de la tarjeta.'], 400);
    $card = load_card(card_path($storageDir, $id));
    if (!$card || ($card['status'] ?? 'draft') !== 'published') respond(['success'=>false,'message'=>'Tarjeta no encontrada o no publicada.'], 404);
    respond(['success'=>true,'card'=>$card,'data'=>$card]);
}
if ($method !== 'POST') respond(['success'=>false,'message'=>'Método no permitido.'], 405);
$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 12000000) respond(['success'=>false,'message'=>'La solicitud está vacía o supera el límite de 12 MB. Reduce el tamaño o la cantidad de fotografías.'], 413);
$input = json_decode($raw, true);
if (!is_array($input)) respond(['success'=>false,'message'=>'El JSON recibido no es válido.'], 400);
$action = (string)($input['action'] ?? 'save');
if ($action === 'delete') {
    $id = clean_id((string)($input['id'] ?? ''));
    if ($id === '') respond(['success'=>false,'message'=>'Falta el identificador del proyecto.'], 400);
    $path = card_path($storageDir, $id);
    if (!is_file($path)) respond(['success'=>false,'message'=>'El proyecto ya no existe.'], 404);
    if (!unlink($path)) respond(['success'=>false,'message'=>'No se pudo eliminar el proyecto.'], 500);
    respond(['success'=>true,'id'=>$id]);
}
if ($action !== 'save') respond(['success'=>false,'message'=>'Acción no reconocida.'], 400);
$card = $input['card'] ?? null;
if (!is_array($card)) respond(['success'=>false,'message'=>'No se recibieron los datos de la tarjeta.'], 400);
$id = clean_id((string)($card['id'] ?? ''));
$isUpdate = $id !== '' && is_file(card_path($storageDir, $id));
if (!$isUpdate) {
    try { $id = 'card_' . bin2hex(random_bytes(8)); }
    catch (Throwable $e) { $id = 'card_' . str_replace('.', '', uniqid('', true)); }
}
$now = date('c');
$existing = $isUpdate ? load_card(card_path($storageDir, $id)) : null;
$card['id'] = $id;
$card['createdAt'] = (string)($existing['createdAt'] ?? $card['createdAt'] ?? $now);
$card['updatedAt'] = $now;
$card['status'] = in_array(($card['status'] ?? 'draft'), ['draft','published','archived'], true) ? $card['status'] : 'draft';
$card['clientName'] = trim((string)($card['clientName'] ?? ''));
$card['title'] = trim((string)($card['title'] ?? ''));
if ($card['clientName'] === '') respond(['success'=>false,'message'=>'El nombre del cliente es obligatorio.'], 422);
if (!in_array(($card['kind'] ?? 'property'), ['property','vehicle','profile'], true)) $card['kind'] = 'property';
$encoded = json_encode($card, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($encoded === false) respond(['success'=>false,'message'=>'No se pudieron procesar los datos recibidos.'], 422);
if (strlen($encoded) > 11500000) respond(['success'=>false,'message'=>'La tarjeta es demasiado pesada. Reduce las fotografías y vuelve a guardar.'], 413);
$path = card_path($storageDir, $id);
if (file_put_contents($path, $encoded, LOCK_EX) === false) respond(['success'=>false,'message'=>'No se pudo guardar en el servidor. Revisa los permisos de la carpeta data/cards.'], 500);
$publicUrl = 'https://lavdigitalmkt.com/bienes-raices-vehiculos/card.php?id=' . rawurlencode($id);
respond(['success'=>true,'id'=>$id,'url'=>$publicUrl,'card'=>$card]);
