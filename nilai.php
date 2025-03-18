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

// Mendapatkan filter dari URL
$filter_kelas = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : '';
$filter_siswa = isset($_GET['id_siswa']) ? $_GET['id_siswa'] : '';
$filter_mapel = isset($_GET['id_mapel']) ? $_GET['id_mapel'] : '';

// Mendapatkan daftar kelas untuk dropdown
$query_kelas = "SELECT id_kelas FROM kelas ORDER BY id_kelas";
$hasil_kelas = $koneksi->query($query_kelas);

if (!$hasil_kelas) {
    die("Query error: " . $koneksi->error);
}

// Simpan daftar kelas dalam array
$daftar_kelas = [];
while ($row_kelas = $hasil_kelas->fetch_assoc()) {
    $daftar_kelas[] = $row_kelas;
}

// Mendapatkan daftar siswa berdasarkan kelas yang dipilih
$query_siswa = "SELECT id_siswa, nama FROM siswa";
if (!empty($filter_kelas)) {
    $query_siswa .= " WHERE id_kelas = '$filter_kelas'";
}
$query_siswa .= " ORDER BY nama";

$hasil_siswa = $koneksi->query($query_siswa);

if (!$hasil_siswa) {
    die("Query error: " . $koneksi->error);
}

// Simpan daftar siswa dalam array
$daftar_siswa = [];
while ($row_siswa = $hasil_siswa->fetch_assoc()) {
    $daftar_siswa[] = $row_siswa;
}

// Mendapatkan daftar mapel untuk dropdown
$query_mapel = "SELECT m.id_mapel, mp.nama_mapel, m.kode 
                FROM mapel m
                INNER JOIN mata_pelajaran mp ON m.nama = mp.kode_mapel
                ORDER BY mp.nama_mapel";
$hasil_mapel = $koneksi->query($query_mapel);

if (!$hasil_mapel) {
    die("Query error: " . $koneksi->error);
}

// Simpan daftar mapel dalam array
$daftar_mapel = [];
while ($row_mapel = $hasil_mapel->fetch_assoc()) {
    $daftar_mapel[] = $row_mapel;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nilai Siswa</title>
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
        
        .form-inline {
            display: inline-block;
        }
        
        .selector-group {
            display: inline-block;
            margin: 0 5px;
        }
        
        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            min-width: 150px;
        }
        
        label {
            margin-right: 5px;
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
        
        .id-col {
            width: 10%;
            text-align: center;
        }
        
        .nama-col {
            width: 40%;
        }
        
        .nilai-col {
            width: 15%;
            text-align: center;
        }
        
        @media screen and (max-width: 768px) {
            .selector-group {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
    <script>
        // Fungsi untuk mengirim form saat pilihan berubah
        function updateNilai() {
            document.getElementById('filterForm').submit();
        }
        
        // Fungsi untuk mengupdate filter kelas
        function updateKelas() {
            document.getElementById('filterForm').submit();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="form-group">
            <form id="filterForm" method="get" action="" class="form-inline">
                <div class="selector-group">
                    <label>Kelas:</label>
                    <select name="id_kelas" onchange="updateKelas()">
                        <option value="">Semua Kelas</option>
                        <?php
                        foreach ($daftar_kelas as $kelas) {
                            $selected = ($filter_kelas == $kelas['id_kelas']) ? 'selected' : '';
                            echo "<option value='{$kelas['id_kelas']}' {$selected}>{$kelas['id_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="selector-group">
                    <label>Siswa:</label>
                    <select name="id_siswa" onchange="updateNilai()">
                        <option value="">Semua Siswa</option>
                        <?php
                        foreach ($daftar_siswa as $siswa) {
                            $selected = ($filter_siswa == $siswa['id_siswa']) ? 'selected' : '';
                            echo "<option value='{$siswa['id_siswa']}' {$selected}>{$siswa['nama']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="selector-group">
                    <label>Mapel:</label>
                    <select name="id_mapel" onchange="updateNilai()">
                        <option value="">Semua Mapel</option>
                        <?php
                        foreach ($daftar_mapel as $mapel) {
                            $selected = ($filter_mapel == $mapel['id_mapel']) ? 'selected' : '';
                            echo "<option value='{$mapel['id_mapel']}' {$selected}>{$mapel['nama_mapel']} ({$mapel['kode']})</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
        
        <?php
        // Query untuk mendapatkan data nilai dengan join tabel mapel, mata_pelajaran, siswa, dan kelas
        $query = "SELECT 
                    n.id_nilai,
                    s.id_siswa,
                    s.nama as nama_siswa,
                    m.id_mapel,
                    mp.nama_mapel,
                    m.kode,
                    k.id_kelas,
                    n.total
                  FROM nilai n
                  INNER JOIN mapel m ON n.id_mapel = m.id_mapel
                  INNER JOIN mata_pelajaran mp ON m.nama = mp.kode_mapel
                  INNER JOIN siswa s ON n.id_siswa = s.id_siswa
                  INNER JOIN kelas k ON s.id_kelas = k.id_kelas
                  WHERE 1=1";

        // Tambahkan filter berdasarkan pilihan
        if (!empty($filter_siswa)) {
            $query .= " AND n.id_siswa = '$filter_siswa'";
        }
        
        if (!empty($filter_mapel)) {
            $query .= " AND n.id_mapel = '$filter_mapel'";
        }
        
        if (!empty($filter_kelas)) {
            $query .= " AND k.id_kelas = '$filter_kelas'";
        }

        $query .= " ORDER BY k.id_kelas, s.nama, mp.nama_mapel";

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
                        <th class="id-col">ID</th>
                        <th class="nama-col">Nama</th>
                        <th class="id-col">Kelas</th>
                        <th class="nama-col">Mapel</th>
                        <th class="nilai-col">Nilai</th>
                    </tr>';

            while ($row = $hasil->fetch_assoc()) {
                echo "<tr>
                        <td class='id-col'>{$row['id_siswa']}</td>
                        <td class='nama-col'>{$row['nama_siswa']}</td>
                        <td class='id-col'>{$row['id_kelas']}</td>
                        <td class='nama-col'>{$row['nama_mapel']} ({$row['kode']})</td>
                        <td class='nilai-col'>{$row['total']}</td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "<p style='text-align: center;'>Tidak ada data nilai yang ditemukan untuk kriteria yang dipilih.</p>";
        }

        // Menutup koneksi database
        $koneksi->close();
        ?>
    </div>
</body>
</html>
