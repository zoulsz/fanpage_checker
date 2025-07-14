<?php
require_once 'simple_html_dom.php';

// Uji dengan URL sederhana
$html = file_get_contents('https://www.example.com');
$dom = new simple_html_dom();
$dom->load($html);

$title = $dom->find('title', 0);
if ($title) {
    echo "Judul halaman: " . $title->plaintext;
} else {
    echo "Tidak ada judul yang ditemukan.";
}
?>