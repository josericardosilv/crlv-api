<?php
header('Content-Type: application/json');

$placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['placa'] ?? ''));

if (!$placa) {
    echo json_encode(['erro' => 'Placa inválida']);
    exit;
}

$url = "https://wdapi2.com.br/consulta/{$placa}/fa7cf2866d99280b42b277a4dc21e4d0";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'User-Agent: Mozilla/5.0'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    echo json_encode([
        'erro' => 'Erro ao consultar a API externa',
        'curl_error' => $curl_error,
        'http_code' => $http_code
    ]);
    exit;
}

$data = json_decode($response, true);

if (!$data || !isset($data['statusCode']) || $data['statusCode'] != 200 || !isset($data['data'])) {
    echo json_encode([
        'erro' => 'Veículo não encontrado ou resposta inválida',
        'resposta_bruta' => $response
    ]);
    exit;
}

$d = $data['data'];
echo json_encode([
    'placa' => $placa,
    'marca' => $d['MARCA'],
    'modelo' => $d['MODELO'],
    'ano' => $d['ano'],
    'anoModelo' => $d['anoModelo'],
    'chassi' => substr($d['chassi'], 0, 3) . str_repeat('*', 6) . substr($d['chassi'], -3),
    'combustivel' => $d['extra']['combustivel'] ?? 'N/D',
]);
