<?php
declare(strict_types=1);

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

$id = (string)($_GET['id'] ?? '');
if (!preg_match('/^card_[a-f0-9]{16}$/', $id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Identificador de tarjeta inválido.']);
    exit;
}
$cardsDir = __DIR__ . '/../data/cards/';
$file = $cardsDir . $id . '.json';
$card = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
if (!is_array($card) || ($card['status'] ?? '') !== 'published') {
    http_response_code(404);
    echo json_encode(['error' => 'Tarjeta no encontrada o no publicada.']);
    exit;
}
$basePath = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/manifest.php'))), '/');
$basePath = ($basePath === '' ? '' : $basePath) . '/';
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$baseUrl = 'https://' . $host . $basePath;
$name = trim((string)($card['publicName'] ?? $card['title'] ?? $card['clientName'] ?? 'Tarjeta digital'));
$theme = is_array($card['theme'] ?? null) ? $card['theme'] : [];
$manifest = [
    'id' => $baseUrl . 'index.html?card=' . rawurlencode($id),
    'name' => $name . ' | LAV Digital',
    'short_name' => mb_substr($name !== '' ? $name : 'LAV Tarjeta', 0, 24, 'UTF-8'),
    'description' => 'Tarjeta digital de ' . (string)($card['clientName'] ?? 'LAV Digital'),
    'start_url' => $baseUrl . 'index.html?card=' . rawurlencode($id),
    'scope' => $baseUrl,
    'display' => 'standalone',
    'orientation' => 'portrait',
    'background_color' => (string)($theme['background'] ?? '#101820'),
    'theme_color' => (string)($theme['background'] ?? '#101820'),
    'icons' => [[
        'src' => $baseUrl . 'icon.svg',
        'sizes' => 'any',
        'type' => 'image/svg+xml',
        'purpose' => 'any maskable'
    ]]
];
echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
