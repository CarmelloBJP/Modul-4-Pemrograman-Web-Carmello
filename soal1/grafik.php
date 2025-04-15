<?php
require_once "koneksi.php";

$sql = "SELECT kepuasan, COUNT(*) AS jumlah FROM survey_kepuasan GROUP BY kepuasan ORDER BY FIELD(kepuasan, 'Sangat Baik', 'Baik', 'Cukup', 'Kurang Baik', 'Sangat Kurang Baik')";
$result = $conn->query($sql);

$labels = [];
$data = [];
$backgroundColors = [
    'rgba(54, 162, 235, 0.8)',
    'rgba(75, 192, 192, 0.8)',
    'rgba(255, 206, 86, 0.8)',
    'rgba(255, 99, 132, 0.8)',
    'rgba(153, 102, 255, 0.8)'
];
$borderColors = [
    'rgba(54, 162, 235, 1)',
    'rgba(75, 192, 192, 1)',
    'rgba(255, 206, 86, 1)',
    'rgba(255, 99, 132, 1)',
    'rgba(153, 102, 255, 1)'
];

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row["kepuasan"];
            $data[] = (int) $row["jumlah"]; // Pastikan jumlah adalah integer
        }
        $result->free();
    }
} else {
    echo "<div class='container'><p class='error'>Terjadi kesalahan saat mengambil data grafik: " . $conn->error . "</p><div class='link-container'><a href='index.php' class='button'>Kembali ke Formulir</a><a href='tampil_data.php' class='button'>Lihat Data</a></div></div></body></html>";
    $conn->close();
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Kepuasan</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Grafik Tingkat Kepuasan Fasilitas Hotel</h1>
        <div class="link-container">
            <a href="index.php" class="button">Kembali ke Formulir</a>
            <a href="tampil_data.php" class="button">Lihat Data</a>
        </div>
        <div class="chart-container">
            <canvas id="barChart" width="400" height="300"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('barChart').getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Jumlah Responden',
                        data: <?php echo json_encode($data); ?>,
                        backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                        borderColor: <?php echo json_encode($borderColors); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Responden'
                            },
                            ticks: {
                                stepSize: 1, // Tampilkan angka bulat pada sumbu Y
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Responden per Tingkat Kepuasan'
                        }
                    }
                }
            });
        </script>
    </div>
</body>
</html>