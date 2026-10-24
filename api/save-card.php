<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$storageDir = __DIR__ . '/../data/cards/';

if (!file_exists($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $cardId = $_GET['id'] ?? '';
    
    if (empty($cardId)) {
        echo json_encode(['success' => false, 'message' => 'Identificador no proporcionado.']);
        exit;
    }

    $filePath = $storageDir . preg_replace('/[^a-zA-Z0-9_-]/', '', $cardId) . '.json';

    if (file_exists($filePath)) {
        $cardData = json_decode(file_get_contents($filePath), true);
        echo json_encode(['success' => true, 'data' => $cardData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tarjeta no encontrada.']);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }

    $cardId = uniqid('card_');
    $input['id'] = $cardId;
    $input['created_at'] = date('Y-m-d H:i:s');

    $filePath = $storageDir . $cardId . '.json';
    
    if (file_put_contents($filePath, json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $publicUrl = "https://lavdigitalmkt.com/bienes-raices-vehiculos/?card=" . $cardId;
        echo json_encode([
            'success' => true, 
            'id' => $cardId, 
            'url' => $publicUrl
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la tarjeta en el servidor.']);
    }
    exit;
}
