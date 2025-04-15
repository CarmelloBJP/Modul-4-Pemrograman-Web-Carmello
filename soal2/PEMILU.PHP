<?php
// Start output buffering
ob_start();

// --- KONFIGURASI DATABASE ---
$host = "localhost";
$username = "root";
$password = "";
$database = "pemilu";

// Membuat koneksi database
$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// --- FUNGSI-FUNGSI ---
function tampilkan_calon($koneksi) {
    $query_calon = "SELECT * FROM calon ORDER BY nomor_urut ASC";
    $result_calon = mysqli_query($koneksi, $query_calon);
    
    if (mysqli_num_rows($result_calon) > 0) {
        while ($row_calon = mysqli_fetch_assoc($result_calon)) {
            echo '<div class="calon-item">';
            echo '<input type="radio" id="calon_' . $row_calon['id_calon'] . '" name="id_calon" value="' . $row_calon['id_calon'] . '" required>';
            echo '<label for="calon_' . $row_calon['id_calon'] . '">';
            echo 'Nomor Urut ' . $row_calon['nomor_urut'] . ' - ' . $row_calon['nama_calon'];
            echo '</label>';
            echo '</div>';
        }
    } else {
        echo "<p>Tidak ada data calon.</p>";
    }
}

function proses_login($koneksi, $nik) {
    // Validasi NIK 16 digit
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        $_SESSION['error'] = "NIK harus terdiri dari 16 digit angka";
        header("Location: pemilu.php");
        exit();
    }

    $query_pemilih = "SELECT * FROM pemilih WHERE nik = '$nik'";
    $result_pemilih = mysqli_query($koneksi, $query_pemilih);

    if (mysqli_num_rows($result_pemilih) > 0) {
        $row_pemilih = mysqli_fetch_assoc($result_pemilih);
        if ($row_pemilih["sudah_memilih"]) {
            $_SESSION['error'] = 'NIK ini sudah digunakan untuk memilih.';
            header("Location: pemilu.php");
            exit();
        } else {
            $_SESSION['nik_pemilih'] = $nik;
            $_SESSION['id_pemilih'] = $row_pemilih['id_pemilih'];
            header("Location: pemilu.php?page=pemilihan");
            exit();
        }
    } else {
        $query_tambah_pemilih = "INSERT INTO pemilih (nik) VALUES ('$nik')";
        if (mysqli_query($koneksi, $query_tambah_pemilih)) {
            $_SESSION['nik_pemilih'] = $nik;
            $_SESSION['id_pemilih'] = mysqli_insert_id($koneksi);
            header("Location: pemilu.php?page=pemilihan");
            exit();
        } else {
            $_SESSION['error'] = "Error menambahkan pemilih: " . mysqli_error($koneksi);
            header("Location: pemilu.php");
            exit();
        }
    }
}

function proses_pemilihan($koneksi, $id_calon_terpilih, $id_pemilih) {
    mysqli_begin_transaction($koneksi);
    
    try {
        // Update jumlah suara calon
        $query_update_suara = "UPDATE calon SET jumlah_suara = jumlah_suara + 1 WHERE id_calon = '$id_calon_terpilih'";
        if (!mysqli_query($koneksi, $query_update_suara)) {
            throw new Exception("Error memperbarui jumlah suara: " . mysqli_error($koneksi));
        }
        
        // Update status pemilih
        $query_update_pemilih = "UPDATE pemilih SET sudah_memilih = TRUE, waktu_memilih = NOW() WHERE id_pemilih = '$id_pemilih'";
        if (!mysqli_query($koneksi, $query_update_pemilih)) {
            throw new Exception("Error memperbarui status pemilih: " . mysqli_error($koneksi));
        }
        
        mysqli_commit($koneksi);
        $_SESSION['sudah_memilih'] = true;
        $_SESSION['success'] = 'Terima kasih telah berpartisipasi dalam PEMILU!';
        header("Location: pemilu.php?page=hasil");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['error'] = $e->getMessage();
        header("Location: pemilu.php?page=pemilihan");
        exit();
    }
}

function tampilkan_hasil($koneksi) {
    $query_hasil = "SELECT c.nama_calon, c.nomor_urut, c.jumlah_suara, 
                   (SELECT COUNT(*) FROM pemilih WHERE sudah_memilih = TRUE) AS total_pemilih,
                   (SELECT COUNT(*) FROM pemilih) AS total_terdaftar
                   FROM calon c ORDER BY c.jumlah_suara DESC";
    
    $result_hasil = mysqli_query($koneksi, $query_hasil);
    
    if (mysqli_num_rows($result_hasil) > 0) {
        $row_total = mysqli_fetch_assoc($result_hasil);
        mysqli_data_seek($result_hasil, 0);
        
        echo "<div class='statistik'>";
        echo "<p>Total pemilih terdaftar: " . $row_total['total_terdaftar'] . "</p>";
        echo "<p>Total yang sudah memilih: " . $row_total['total_pemilih'] . "</p>";
        echo "<p>Persentase partisipasi: " . round(($row_total['total_pemilih'] / $row_total['total_terdaftar']) * 100, 2) . "%</p>";
        echo "</div>";
        
        echo "<table>";
        echo "<thead><tr><th>No. Urut</th><th>Nama Calon</th><th>Jumlah Suara</th><th>Persentase</th></tr></thead>";
        echo "<tbody>";
        
        while ($row_hasil = mysqli_fetch_assoc($result_hasil)) {
            $persentase = $row_total['total_pemilih'] > 0 
                ? round(($row_hasil['jumlah_suara'] / $row_total['total_pemilih']) * 100, 2) 
                : 0;
            
            echo "<tr>";
            echo "<td>" . $row_hasil['nomor_urut'] . "</td>";
            echo "<td>" . $row_hasil['nama_calon'] . "</td>";
            echo "<td>" . $row_hasil['jumlah_suara'] . "</td>";
            echo "<td>" . $persentase . "%</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>Belum ada suara masuk.</p>";
    }
}

// --- LOGIKA UTAMA ---
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: pemilu.php");
    exit();
}

// Handle session timeout (30 menit)
$timeout = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: pemilu.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Tentukan halaman yang akan ditampilkan
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Handle pesan error/success
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);

// Proses form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_login'])) {
        $nik = trim($_POST["nik"]);
        proses_login($koneksi, $nik);
    } elseif (isset($_POST['submit_pilihan'])) {
        if (isset($_POST["id_calon"]) && isset($_SESSION['id_pemilih'])) {
            $id_calon_terpilih = $_POST["id_calon"];
            $id_pemilih = $_SESSION['id_pemilih'];
            proses_pemilihan($koneksi, $id_calon_terpilih, $id_pemilih);
        } else {
            $_SESSION['error'] = "Pilihan calon tidak valid";
            header("Location: pemilu.php?page=pemilihan");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilu K-Pop vs Anime</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-error {
            background-color: #e74c3c;
            color: white;
        }
        .alert-success {
            background-color: #27ae60;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .calon-item {
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .calon-item:hover {
            background-color: #f9f9f9;
            border-color: #3498db;
        }
        button, .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover, .btn:hover {
            background-color: #2980b9;
        }
        .btn-logout {
            background-color: #e74c3c;
        }
        .btn-logout:hover {
            background-color: #c0392b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        .statistik {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php
        switch ($page) {
            case 'login':
                echo '<h1>Pemilu K-Pop vs Anime</h1>';
                echo '<form method="post">';
                echo '<div class="form-group">';
                echo '<label for="nik">Masukkan NIK Anda (16 digit):</label>';
                echo '<input type="text" id="nik" name="nik" pattern="[0-9]{16}" title="NIK harus 16 digit angka" required>';
                echo '</div>';
                echo '<button type="submit" name="submit_login">Lanjutkan</button>';
                echo '</form>';
                break;

            case 'pemilihan':
                if (!isset($_SESSION['nik_pemilih'])) {
                    header("Location: pemilu.php");
                    exit();
                }
                
                echo '<h1>Pilih Calon Favorit Anda</h1>';
                echo '<p>Anda login sebagai NIK: ' . htmlspecialchars($_SESSION['nik_pemilih']) . '</p>';
                echo '<form method="post">';
                tampilkan_calon($koneksi);
                echo '<button type="submit" name="submit_pilihan">Submit Pilihan</button>';
                echo '</form>';
                echo '<a href="pemilu.php?logout=true" class="btn btn-logout">Logout</a>';
                break;

            case 'hasil':
                echo '<h1>Hasil Pemungutan Suara</h1>';
                
                if (isset($_SESSION['sudah_memilih'])) {
                    echo '<div class="alert alert-success">Terima kasih telah berpartisipasi dalam pemilihan!</div>';
                }
                
                tampilkan_hasil($koneksi);
                echo '<a href="pemilu.php?logout=true" class="btn btn-logout">Logout</a>';
                break;

            default:
                echo '<h2>Halaman tidak ditemukan</h2>';
                echo '<p><a href="pemilu.php" class="btn">Kembali ke halaman utama</a></p>';
                break;
        }
        ?>
        
        <footer>
            <p>Sistem Pemilu K-Pop vs Anime &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>
</html>
<?php
mysqli_close($koneksi);
ob_end_flush();
?>