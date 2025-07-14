<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan CORS untuk frontend

function getFanpageInfo($pageId, $accessToken) {
    $url = "https://graph.facebook.com/v23.0/$pageId?fields=name,fan_count,created_time&access_token=$accessToken";
    $response = @file_get_contents($url);
    if ($response === false) {
        return ['error' => 'Gagal mengakses API Facebook'];
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Gagal memproses data JSON'];
    }
    
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
$accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';

if (!$pageUrl) {
    echo json_encode(['error' => 'URL halaman tidak diberikan']);
    exit;
}

if (!$accessToken) {
    echo json_encode(['error' => 'Access Token tidak diberikan']);
    exit;
}

// Ekstrak ID atau username dari URL
try {
    $urlParts = parse_url($pageUrl, PHP_URL_PATH);
    $urlParts = explode('/', trim($urlParts, '/'));
    $pageId = end($urlParts);
    if (empty($pageId) || !preg_match('/^[a-zA-Z0-9_]+$/', $pageId)) {
        throw new Exception('ID halaman tidak valid');
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'URL halaman tidak valid: ' . $e->getMessage()]);
    exit;
}

echo json_encode(getFanpageInfo($pageId, $accessToken));
?>
