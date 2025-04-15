<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survei Kepuasan Fasilitas Hotel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Survei Kepuasan Fasilitas Hotel</h1>
        <p class="info">Berikan penilaian Anda terhadap fasilitas hotel kami.</p>
        <form action="proses_survei.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama Anda:</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label>Bagaimana tingkat kepuasan Anda terhadap fasilitas hotel?</label><br>
                <input type="radio" id="sangat_baik" name="kepuasan" value="Sangat Baik" required>
                <label for="sangat_baik">Sangat Baik</label><br>
                <input type="radio" id="baik" name="kepuasan" value="Baik" required>
                <label for="baik">Baik</label><br>
                <input type="radio" id="cukup" name="kepuasan" value="Cukup" required>
                <label for="cukup">Cukup</label><br>
                <input type="radio" id="kurang_baik" name="kepuasan" value="Kurang Baik" required>
                <label for="kurang_baik">Kurang Baik</label><br>
                <input type="radio" id="sangat_kurang_baik" name="kepuasan" value="Sangat Kurang Baik" required>
                <label for="sangat_kurang_baik">Sangat Kurang Baik</label>
            </div>
            <div class="form-group">
                <label for="kritik_saran">Kritik dan Saran:</label>
                <textarea id="kritik_saran" name="kritik_saran" rows="5"></textarea>
            </div>
            <button type="submit">Kirim Survei</button>
        </form>
        <div class="link-container">
            <a href="tampil_data.php">Lihat Data</a>
            <a href="grafik.php">Lihat Grafik</a>
        </div>
    </div>
</body>
</html>