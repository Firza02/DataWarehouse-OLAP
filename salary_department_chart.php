<?php
include 'koneksi.php';

// QUERY: Ambil data Salary Department
$sql = "SELECT NamaDepartemen, RataRata_GajiTerakhir 
        FROM stg_salary_department
        ORDER BY RataRata_GajiTerakhir DESC";
$result = mysqli_query($conn, $sql);

$labels     = [];
$values     = []; // gaji asli
$tableRows  = [];
$topDept    = null;
$bottomDept = null;
$total      = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['NamaDepartemen'];
    $values[] = (float)$row['RataRata_GajiTerakhir'];

    $tableRows[] = $row;
    $total      += (float)$row['RataRata_GajiTerakhir'];

    if ($topDept === null) {
        $topDept = $row;          // pertama (DESC) = tertinggi
    }
    $bottomDept = $row;           // terakhir di loop = terendah
}

// hitung persentase komposisi
$data = [];
foreach ($values as $v) {
    $data[] = $total > 0 ? round(($v / $total) * 100, 2) : 0;
}

$deptCount = count($tableRows);
$avgSalary = $deptCount > 0 ? $total / $deptCount : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Salary Department Chart</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #layoutSidenav {
            display: flex;
        }

        #layoutSidenav_nav {
            width: 250px;
        }

        #layoutSidenav_content {
            flex: 1;
        }

        .chart-container {
            position: relative;
            height: 420px;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <!-- NAVBAR -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark custom-navbar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-store" style="color:grey"></i>
            </div>
        </a>
        <a class="navbar-brand ps-3 d-flex align-items-center gap-2" href="home.php">
            <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <span>FINAL PROJECT DATA WAREHOUSE &amp; OLAP</span>
        </a>
    </nav>

    <!-- SIDEBAR + CONTENT -->
    <div id="layoutSidenav">

        <!-- SIDEBAR -->
        <?php include 'sidebar.php'; ?>

        <!-- CONTENT -->
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">

                <h1 class="mt-4 mb-3">Rata-Rata Gaji Terakhir per Departemen</h1>

                <!-- KPI CARDS -->
                <div class="row mb-4">
                    <!-- Card 1: Gaji rata-rata tertinggi -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Departemen dengan Gaji Rata-rata Tertinggi</p>
                                <h5 class="mb-1">
                                    <?= $topDept ? htmlspecialchars($topDept['NamaDepartemen']) : '-' ?>
                                </h5>
                                <p class="text-success fw-semibold mb-0">
                                    <?= $topDept ? '$' . number_format($topDept['RataRata_GajiTerakhir'], 2, '.', ',') : '$0.00' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Gaji rata-rata terendah -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Departemen dengan Gaji Rata-rata Terendah</p>
                                <h5 class="mb-1">
                                    <?= $bottomDept ? htmlspecialchars($bottomDept['NamaDepartemen']) : '-' ?>
                                </h5>
                                <p class="text-danger fw-semibold mb-0">
                                    <?= $bottomDept ? '$' . number_format($bottomDept['RataRata_GajiTerakhir'], 2, '.', ',') : '$0.00' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Rata-rata keseluruhan -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Rata-rata Gaji Semua Departemen</p>
                                <h4 class="mb-0 text-primary">
                                    <?= '$' . number_format($avgSalary, 2, '.', ',') ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PIE CHART -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salaryDeptChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABEL DETAIL -->
                <div class="card mb-4">
                    <div class="card-header border-0">
                        <h6 class="mb-0">Detail Rata-rata Gaji Terakhir per Departemen</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th>Departemen</th>
                                        <th class="text-end">Rata-rata Gaji Terakhir (USD)</th>
                                        <th class="text-end">Kontribusi terhadap Total (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($deptCount === 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Tidak ada data departemen.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tableRows as $i => $row):
                                            $val = (float)$row['RataRata_GajiTerakhir'];
                                            $percent = $total > 0 ? ($val / $total * 100) : 0;
                                        ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($row['NamaDepartemen']) ?></td>
                                                <td class="text-end">
                                                    <?= '$' . number_format($val, 2, '.', ',') ?>
                                                </td>
                                                <td class="text-end">
                                                    <?= number_format($percent, 2, '.', ',') ?>%
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <script>
        // DATA dari PHP
        const sdLabels = <?= json_encode($labels); ?>;
        const sdPercent = <?= json_encode($data); ?>; // persen
        const sdOriginal = <?= json_encode($values); ?>; // gaji asli

        const ctx = document.getElementById('salaryDeptChart').getContext('2d');

        let salaryChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: sdLabels,
                datasets: [{
                    label: 'Komposisi Gaji per Departemen',
                    data: sdPercent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let idx = ctx.dataIndex;
                                let label = ctx.label;
                                let percent = sdPercent[idx];
                                let dollar = sdOriginal[idx];

                                let formattedDollar = dollar.toLocaleString("en-US", {
                                    style: "currency",
                                    currency: "USD"
                                });

                                return `${label}: ${percent}% (${formattedDollar})`;
                            }
                        }
                    }
                }
            }
        });

        // Agar chart tetap responsif saat sidebar dibuka/tutup
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => salaryChart.resize(), 300);
        });
    </script>

</body>

</html>