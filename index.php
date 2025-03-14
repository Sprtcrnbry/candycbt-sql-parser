<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "ass2023"; // Sesuaikan dengan nama database Anda

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Mendapatkan daftar mapel untuk dropdown
$query_mapel = "SELECT id_mapel, nama FROM mapel ORDER BY nama";
$hasil_mapel = $koneksi->query($query_mapel);

if (!$hasil_mapel) {
    die("Query error: " . $koneksi->error);
}

// Simpan daftar mapel dalam array untuk penggunaan berikutnya
$daftar_mapel = [];
while ($row_mapel = $hasil_mapel->fetch_assoc()) {
    $daftar_mapel[] = $row_mapel;
}

// Jika tidak ada mapel yang dipilih, gunakan mapel pertama sebagai default
$filter_mapel = isset($_POST['id_mapel']) ? $_POST['id_mapel'] : '';
if (empty($filter_mapel) && !empty($daftar_mapel)) {
    $filter_mapel = $daftar_mapel[0]['id_mapel'];
}

// Form untuk memilih mapel
echo "<h2>Data Soal dan Jawaban Benar</h2>";
echo "<form method='post' action=''>";
echo "<label>Pilih Mata Pelajaran: </label>";
echo "<select name='id_mapel' onchange='this.form.submit()'>";

// Tampilkan semua mata pelajaran dalam dropdown
foreach ($daftar_mapel as $mapel) {
    $selected = ($filter_mapel == $mapel['id_mapel']) ? 'selected' : '';
    echo "<option value='{$mapel['id_mapel']}' {$selected}>{$mapel['nama']}</option>";
}

echo "</select>";
echo "</form>";
echo "<br>";

// Query untuk mengambil data dari tabel soal dan mapel dengan filter 
$query = "SELECT 
            s.nomor,
            s.id_soal,
            m.nama as nama_mapel, 
            s.soal, 
            s.pilA, 
            s.pilB, 
            s.pilC, 
            s.pilD, 
            s.pilE, 
            s.jawaban 
          FROM soal s
          INNER JOIN mapel m ON s.id_mapel = m.id_mapel";

// Tambahkan filter mapel jika ada mapel yang dipilih
if (!empty($filter_mapel)) {
    $query .= " WHERE s.id_mapel = '$filter_mapel'";
}

$query .= " ORDER BY s.nomor";

$hasil = $koneksi->query($query);

// Memeriksa apakah query berhasil dieksekusi
if (!$hasil) {
    die("Query error: " . $koneksi->error);
}

// Cek apakah ada data yang ditemukan
if ($hasil->num_rows > 0) {
    // Menampilkan hasil dalam bentuk tabel
    echo "<table border='1' cellpadding='5' cellspacing='0'>
            <tr>
                <th>Nomor</th>
                <th>Mata Pelajaran</th>
                <th>Soal</th>
                <th>Jawaban Benar</th>
            </tr>";

    while ($row = $hasil->fetch_assoc()) {
        // Menggunakan kolom nomor
        $nomor = $row['nomor'];
        
        // Menentukan huruf pilihan jawaban yang benar
        $huruf_jawaban = $row['jawaban'];
        $kolom_jawaban = 'pil' . $huruf_jawaban;
        
        echo "<tr>
                <td>{$nomor}</td>
                <td>{$row['nama_mapel']}</td>
                <td>{$row['soal']}</td>
                <td>{$huruf_jawaban}. {$row[$kolom_jawaban]}</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p>Tidak ada data soal yang ditemukan untuk mata pelajaran yang dipilih.</p>";
}

// Menutup koneksi database
$koneksi->close();
?>
