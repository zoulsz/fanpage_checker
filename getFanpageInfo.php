<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan CORS untuk frontend

require_once 'C:/wamp64/www/facebook_scraper/simple_html_dom.php'; // Path absolut

function getFanpageInfo($pageUrl, $cookie) {
    $result = [
        'link' => $pageUrl,
        'nama_halaman' => 'Tidak ditemukan',
        'tahun' => 'Tidak ditemukan',
        'jumlah_pengikut' => 'Tidak ditemukan'
    ];

    // Inisialisasi cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Kembalikan sebagai string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Batas waktu 30 detik
    curl_setopt($ch, CURLOPT_USERAGENT, 'Dalvik/2.1.0 (Linux; U; Android 13; SM-G960F Build/TP1A.220624.014) FacebookForAndroid/421.0.0.32.73'); // User-agent Facebook
    curl_setopt($ch, CURLOPT_COOKIE, $cookie); // Gunakan cookie dari form
    curl_setopt($ch, CURLOPT_REFERER, 'https://web.facebook.com'); // Tambahkan referer
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL untuk pengujian lokal
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Nonaktifkan verifikasi host SSL

    // Eksekusi cURL
    $html = curl_exec($ch);
    $error = curl_error($ch);
    if ($error || $html === false) {
        curl_close($ch);
        return ['error' => 'Gagal mengakses halaman: ' . $error];
    }
    sleep(3); // Penundaan untuk menghindari deteksi
    file_put_contents('debug.html', $html); // Simpan untuk debug

    curl_close($ch);

    // Parse HTML dengan simple_html_dom
    $dom = new simple_html_dom();
    $dom->load($html);

    // Ekstrak nama halaman
    $title = $dom->find('title', 0);
    if ($title) {
        $result['nama_halaman'] = trim($title->plaintext);
    }

    // Ekstrak tahun
    $metaDescription = $dom->find('meta[name=description]', 0);
    if ($metaDescription && preg_match('/(\d{4})/', $metaDescription->content, $matches)) {
        $result['tahun'] = $matches[1];
    } else {
        $yearElements = $dom->find('span, div');
        foreach ($yearElements as $element) {
            $text = trim($element->plaintext);
            if (preg_match('/(\d{4})/', $text, $matches)) {
                $result['tahun'] = $matches[1];
                break;
            }
        }
    }

    // Ekstrak jumlah pengikut
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
$cookie = isset($_GET['cookie']) ? $_GET['cookie'] : '';
if (!$pageUrl) {
    echo json_encode(['error' => 'URL halaman tidak diberikan']);
    exit;
}
if (!$cookie) {
    echo json_encode(['error' => 'Cookie tidak diberikan']);
    exit;
}

echo json_encode(getFanpageInfo($pageUrl, $cookie));
?>