<?php
declare(strict_types=1);

/* Entrega una tarjeta publicada como vCard nativa para abrir/importar en Contactos. */
$id = (string)($_GET['id'] ?? '');
if (!preg_match('/^card_[a-f0-9]{16}$/', $id)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Identificador de contacto inválido.';
    exit;
}
$file = __DIR__ . '/../data/cards/' . $id . '.json';
if (!is_file($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Contacto no encontrado.';
    exit;
}
$card = json_decode((string)file_get_contents($file), true);
if (!is_array($card) || ($card['status'] ?? '') !== 'published') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'El contacto no está publicado.';
    exit;
}
function vcard_escape($value): string {
    return str_replace(["\\", "\r", "\n", ";", ","], ["\\\\", "", "\\n", "\\;", "\\,"], trim((string)$value));
}
$full = trim((string)($card['contactName'] ?? $card['clientName'] ?? 'Contacto'));
if ($full === '') $full = 'Contacto';
$parts = preg_split('/\s+/', $full) ?: [$full];
$last = count($parts) > 1 ? (string)array_pop($parts) : '';
$first = trim(implode(' ', $parts));
if ($first === '') $first = $full;
$base = rtrim(dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/contact.php'))), '/');
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'https://') . ($_SERVER['HTTP_HOST'] ?? '') . $base . '/index.html?card=' . rawurlencode($id);
$rows = ['BEGIN:VCARD','VERSION:3.0','N:'.vcard_escape($last).';'.vcard_escape($first).';;;','FN:'.vcard_escape($full)];
if (!empty($card['clientName'])) $rows[] = 'ORG:'.vcard_escape($card['clientName']);
if (!empty($card['phone'])) $rows[] = 'TEL;TYPE=WORK,VOICE:'.vcard_escape($card['phone']);
if (!empty($card['whatsapp'])) $rows[] = 'TEL;TYPE=CELL:'.vcard_escape($card['whatsapp']);
if (!empty($card['email'])) $rows[] = 'EMAIL;TYPE=INTERNET:'.vcard_escape($card['email']);
if (!empty($card['location'])) $rows[] = 'ADR;TYPE=WORK:;;'.vcard_escape($card['location']).';;;;';
if (!empty($card['facebook'])) $rows[] = 'URL:'.vcard_escape($card['facebook']);
$rows[] = 'URL:'.vcard_escape($url);
$rows[] = 'END:VCARD';
header('Content-Type: text/vcard; charset=utf-8');
$vcfName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $full) . '.vcf';
/* Entregar la vCard en línea; el navegador decide si la abre o permite importarla. */
header('Content-Disposition: inline; filename="' . $vcfName . '"');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo implode("\r\n", $rows) . "\r\n";
