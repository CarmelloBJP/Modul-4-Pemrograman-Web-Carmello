<?php
require_once "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (empty($_POST["nama"])) {
        die("<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'><h2>Error</h2><p>Nama harus diisi.</p><p><a href='index.php'>Kembali ke Formulir</a></p></div>");
    }
    $nama = htmlspecialchars(trim($_POST["nama"]));

    if (!isset($_POST["kepuasan"]) || !in_array($_POST["kepuasan"], ['Sangat Baik', 'Baik', 'Cukup', 'Kurang Baik', 'Sangat Kurang Baik'])) {
        die("<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'><h2>Error</h2><p>Tingkat kepuasan harus dipilih.</p><p><a href='index.php'>Kembali ke Formulir</a></p></div>");
    }
    $kepuasan = $_POST["kepuasan"];
    $kritik_saran = htmlspecialchars(trim($_POST["kritik_saran"]));

    $sql = "INSERT INTO survey_kepuasan (nama, kepuasan, kritik_saran) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $nama, $kepuasan, $kritik_saran);

        if ($stmt->execute()) {
            echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
            echo "<h2>Terima kasih atas partisipasi Anda!</h2>";
            echo "<p>Survei Anda telah berhasil dikirim.</p>";
            echo "<p><a href='index.php'>Kembali ke Formulir</a></p>";
            echo "</div>";
        } else {
            echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
            echo "<h2>Terjadi Kesalahan</h2>";
            echo "<p>Maaf, terjadi kesalahan saat menyimpan data survei: " . $stmt->error . "</p>";
            echo "<p><a href='index.php'>Kembali ke Formulir</a></p>";
            echo "</div>";
        }

        $stmt->close();
    } else {
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h2>Terjadi Kesalahan</h2>";
        echo "<p>Maaf, terjadi kesalahan saat mempersiapkan pernyataan SQL: " . $conn->error . "</p>";
        echo "<p><a href='index.php'>Kembali ke Formulir</a></p>";
        echo "</div>";
    }

    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>