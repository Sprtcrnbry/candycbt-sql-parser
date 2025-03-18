<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = ""; // Sesuaikan dengan nama database Anda

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Mendapatkan daftar mapel untuk dropdown dengan nama_mapel dari tabel mata_pelajaran
$query_mapel = "SELECT m.id_mapel, mp.nama_mapel, m.kode
                FROM mapel m
                INNER JOIN mata_pelajaran mp ON m.nama = mp.kode_mapel
                ORDER BY mp.nama_mapel";
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
$filter_mapel = isset($_GET['id_mapel']) ? $_GET['id_mapel'] : '';
if (empty($filter_mapel) && !empty($daftar_mapel)) {
    $filter_mapel = $daftar_mapel[0]['id_mapel'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Soal dan Jawaban</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 15px;
            line-height: 1.6;
        }
        
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: center;
        }
        
        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            min-width: 200px;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .nomor-col {
            width: 10%;
            text-align: center;
        }
        
        .soal-col {
            width: 50%;
        }
        
        .jawaban-col {
            width: 40%;
        }
        
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-group">
            <form method="get" action="">
                <label>Pilih Mata Pelajaran: </label>
                <select name="id_mapel" onchange="this.form.submit()">
                    <?php
                    // Tampilkan semua mata pelajaran dalam dropdown
                    foreach ($daftar_mapel as $mapel) {
                        $selected = ($filter_mapel == $mapel['id_mapel']) ? 'selected' : '';
                        echo "<option value='{$mapel['id_mapel']}' {$selected}>{$mapel['nama_mapel']} ({$mapel['kode']})</option>";
                    }                    
                    ?>
                </select>
            </form>
        </div>
        
        <?php
        // Query untuk mengambil data dari tabel soal dan mapel dengan filter 
        $query = "SELECT 
                    s.nomor,
                    s.id_soal,
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
            echo '<table>
                    <tr>
                        <th class="nomor-col">No</th>
                        <th class="soal-col">Soal</th>
                        <th class="jawaban-col">Jawaban</th>
                    </tr>';

            while ($row = $hasil->fetch_assoc()) {
                // Menggunakan kolom nomor
                $nomor = $row['nomor'];
                
                // Menentukan huruf pilihan jawaban yang benar
                $huruf_jawaban = $row['jawaban'];
                $kolom_jawaban = 'pil' . $huruf_jawaban;
                
                echo "<tr>
                        <td class='nomor-col'>{$nomor}</td>
                        <td class='soal-col'>{$row['soal']}</td>
                        <td class='jawaban-col'>{$huruf_jawaban}. {$row[$kolom_jawaban]}</td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "<p style='text-align: center;'>Tidak ada data soal yang ditemukan untuk mata pelajaran yang dipilih.</p>";
        }

        // Menutup koneksi database
        $koneksi->close();
        ?>
    </div>
</body>
</html>
