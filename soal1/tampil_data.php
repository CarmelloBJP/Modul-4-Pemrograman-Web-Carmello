<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Survei Kepuasan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Data Survei Kepuasan Fasilitas Hotel</h1>
        <div class="link-container">
            <a href="index.php" class="button">Kembali ke Formulir</a>
            <a href="grafik.php" class="button">Lihat Grafik</a>
        </div>
        <?php
        require_once "koneksi.php";
        $sql = "SELECT id, nama, kepuasan, kritik_saran, waktu_submit FROM survey_kepuasan ORDER BY waktu_submit DESC";
        $result = $conn->query($sql);

        if ($result) {
            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>Nama</th>";
                echo "<th>Kepuasan</th>";
                echo "<th>Kritik dan Saran</th>";
                echo "<th>Waktu Submit</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nama"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["kepuasan"]) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($row["kritik_saran"])) . "</td>"; // Tambahkan nl2br
                    echo "<td>" . htmlspecialchars($row["waktu_submit"]) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p class='info'>Tidak ada data survei.</p>";
            }
            $result->free(); // Bebaskan hasil query
        } else {
            echo "<p class='error'>Terjadi kesalahan saat mengambil data: " . $conn->error . "</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>