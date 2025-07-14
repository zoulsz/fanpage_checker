<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan CORS untuk frontend

require_once 'simple_html_dom.php'; // Pastikan file ini ada

function getFanpageInfo($pageUrl) {
    $result = [
        'link' => $pageUrl,
        'nama_halaman' => 'Tidak ditemukan',
        'tahun' => 'Tidak ditemukan',
        'jumlah_pengikut' => 'Tidak ditemukan'
    ];

    // Gunakan user-agent dari aplikasi Facebook
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Dalvik/2.1.0 (Linux; U; Android 13; SM-G960F Build/TP1A.220624.014) FacebookForAndroid/421.0.0.32.73"
            // Ganti dengan user-agent Anda sendiri jika berbeda
        ]
    ]);
    $html = @file_get_contents($pageUrl, false, $context);
    if ($html === false) {
        return ['error' => 'Gagal mengakses halaman. Periksa koneksi internet atau blokir oleh Facebook.'];
    }
    sleep(2); // Penundaan untuk menghindari deteksi
    file_put_contents('debug.html', $html); // Simpan untuk debug

    $dom = new simple_html_dom();
    $dom->load($html);

    $title = $dom->find('title', 0);
    if ($title) {
        $result['nama_halaman'] = trim($title->plaintext);
    }

    $metaDescription = $dom->find('meta[name=description]', 0);
    if ($metaDescription && preg_match('/(\d{4})/', $metaDescription->content, $matches)) {
        $result['tahun'] = $matches[1];
    }

    $followerElements = $dom->find('span, div');
    foreach ($followerElements as $element) {
        $text = trim($element->plaintext);
        if (strpos($text, 'pengikut') !== false || strpos($text, 'followers') !== false || strpos($text, 'suka') !== false) {
            $number = preg_replace('/[^0-9]/', '', $text);
            if ($number) {
                $result['jumlah_pengikut'] = $number;
                break;
            }
        }
    }

    return $result;
}

$pageUrl = isset($_GET['url']) ? $_GET['url'] : '';
if (!$pageUrl) {
    echo json_encode(['error' => 'URL halaman tidak diberikan']);
    exit;
}

echo json_encode(getFanpageInfo($pageUrl));
?>