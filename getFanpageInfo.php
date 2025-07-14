<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan CORS untuk frontend

require_once 'simple_html_dom.php'; // Pastikan file ini ada di folder yang sama

function getFanpageInfo($pageUrl) {
    // Inisialisasi array hasil
    $result = [
        'link' => $pageUrl,
        'tahun' => 'Tidak ditemukan',
        'nama_halaman' => 'Tidak ditemukan',
        'jumlah_pengikut' => 'Tidak ditemukan'
    ];

    // Ambil konten halaman dengan user-agent untuk menghindari blokir
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
        ]
    ]);
    $html = @file_get_contents($pageUrl, false, $context);
    if ($html === false) {
        return ['error' => 'Gagal mengakses halaman Facebook'];
    }

    // Buat objek HTML DOM
    $dom = new simple_html_dom();
    $dom->load($html);

    // Ekstrak nama halaman (dari judul atau elemen header)
    $title = $dom->find('title', 0);
    if ($title) {
        $result['nama_halaman'] = trim($title->plaintext);
    } else {
        $header = $dom->find('h1', 0); // Coba dari elemen header
        if ($header) {
            $result['nama_halaman'] = trim($header->plaintext);
        }
    }

    // Ekstrak tahun (berdasarkan pola seperti "2018" di nama atau deskripsi)
    $metaDescription = $dom->find('meta[name=description]', 0);
    if ($metaDescription && preg_match('/(\d{4})/', $metaDescription->content, $matches)) {
        $result['tahun'] = $matches[1];
    } else {
        // Coba dari teks di halaman
        $yearElements = $dom->find('span, div');
        foreach ($yearElements as $element) {
            $text = trim($element->plaintext);
            if (preg_match('/(\d{4})/', $text, $matches)) {
                $result['tahun'] = $matches[1];
                break;
            }
        }
    }

    // Ekstrak jumlah pengikut (berdasarkan pola seperti "105 pengikut")
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
