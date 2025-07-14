<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan CORS untuk frontend

function getFanpageInfo($pageId, $accessToken) {
    $url = "https://graph.facebook.com/v20.0/$pageId?fields=name,fan_count,created_time&access_token=$accessToken";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['error' => 'Error: ' . $data['error']['message']];
    }

    return [
        'nama' => $data['name'] ?? 'Tidak ditemukan',
        'jumlah_follower' => $data['fan_count'] ?? 'Tidak ditemukan',
        'tahun_pembuatan' => substr($data['created_time'] ?? 'Tidak ditemukan', 0, 4)
    ];
}

$pageUrl = isset($_GET['url']) ? $_GET['url'] : '';
if (!$pageUrl) {
    echo json_encode(['error' => 'URL halaman tidak diberikan']);
    exit;
}

// Ekstrak ID atau username dari URL
try {
    $urlParts = parse_url($pageUrl, PHP_URL_PATH);
    $urlParts = explode('/', trim($urlParts, '/'));
    $pageId = end($urlParts);
} catch (Exception $e) {
    echo json_encode(['error' => 'URL halaman tidak valid']);
    exit;
}

$accessToken = getenv('ACCESS_TOKEN') ?: 'TOKEN_AKSES_ANDA'; // Ganti dengan token Anda
echo json_encode(getFanpageInfo($pageId, $accessToken));
?>