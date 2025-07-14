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

$accessToken = getenv('ACCESS_TOKEN') ?: 'EAAY5C5ZBneYUBPOMGXVOFfTQZBro5yMu9WVE0qPdolVJ7KpPmfILD1rOPPZAxORzIoZAM3JQ7TB4A1sFDeM96cxloQZBm3nwJ6hXF53NODP4rLca8ZB87pPua2ZCOMWak9igmTajYeQYCZAo9MgMa9vUJe3IXwZBaJx49KX2mcxZBVo3lDx8Wt9Tx5sZAr0epk2S7gZAsWinifPo0Qfq0jHA7lGZCqTWg2QnvQFB9kmaggNf4JEQasDgfZA7D4gaYs8nnZAeJD0FWqeOSIaA1ZB7W3snfZAHQRe7BamzNnU2UgAZDZD'; // Ganti dengan token Anda
echo json_encode(getFanpageInfo($pageId, $accessToken));
?>
