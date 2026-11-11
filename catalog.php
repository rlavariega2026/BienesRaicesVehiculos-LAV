<?php
declare(strict_types=1);

/* Página pública independiente para catálogos de perfiles comerciales. */
$id = (string)($_GET['id'] ?? '');
if (!preg_match('/^card_[a-f0-9]{16}$/', $id)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Identificador de catálogo inválido.';
    exit;
}
$file = __DIR__ . '/data/cards/' . $id . '.json';
$profile = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
if (!is_array($profile) || ($profile['status'] ?? '') !== 'published' || ($profile['kind'] ?? '') !== 'profile') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Catálogo no encontrado o no publicado.';
    exit;
}
$html = file_get_contents(__DIR__ . '/index.html');
if ($html === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se pudo cargar el catálogo.';
    exit;
}
$manifestUrl = 'api/manifest.php?id=' . rawurlencode($id) . '&view=catalog';
$html = str_replace(
    '<link rel="manifest" href="./manifest.json">',
    '<link rel="manifest" href="' . htmlspecialchars($manifestUrl, ENT_QUOTES, 'UTF-8') . '">',
    $html
);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo $html;
