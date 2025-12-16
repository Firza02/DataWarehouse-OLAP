<?php
include 'koneksi.php';

// Ambil data dari staging purchasing vendor, urut dari terbesar
$sql = "SELECT NamaVendor, TotalNilaiPembelian 
        FROM stg_purchasing_vendor
        ORDER BY TotalNilaiPembelian DESC";
$result = mysqli_query($conn, $sql);

$labels     = [];
$data       = [];
$tableRows  = [];
$topVendor  = null;
$totalAll   = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['NamaVendor'];
    $data[]   = (float)$row['TotalNilaiPembelian'];

    $tableRows[] = $row;

    $totalAll += (float)$row['TotalNilaiPembelian'];

    if ($topVendor === null) {
        $topVendor = $row; // baris pertama = nilai tertinggi
    }
}

$vendorCount = count($tableRows);
$topShare    = ($totalAll > 0 && $topVendor)
    ? ($topVendor['TotalNilaiPembelian'] / $totalAll * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Purchasing Vendor Chart</title>

    <!-- SB Admin CSS -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome (ikon sidebar) -->
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
            height: 430px;
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

                <h1 class="mt-4 mb-3">Total Nilai Pembelian per Vendor</h1>

                <!-- ROW: KPI CARDS -->
                <div class="row mb-4">
                    <!-- Card 1: Vendor Tertinggi -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Vendor dengan Pembelian Tertinggi</p>
                                <h5 class="mb-1">
                                    <?= $topVendor ? htmlspecialchars($topVendor['NamaVendor']) : '-' ?>
                                </h5>
                                <p class="text-success fw-semibold mb-0">
                                    Rp <?= $topVendor ? number_format($topVendor['TotalNilaiPembelian'], 0, ',', '.') : '0' ?>
                                </p>
                                <small class="text-muted">
                                    <?= $totalAll > 0 ? number_format($topShare, 2, ',', '.') : '0,00' ?>% dari total
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Total Pembelian -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Total Nilai Pembelian</p>
                                <h4 class="mb-0 text-primary">
                                    Rp <?= number_format($totalAll, 0, ',', '.') ?>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Jumlah Vendor -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Jumlah Vendor</p>
                                <h4 class="mb-0">
                                    <?= $vendorCount ?> vendor
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD: BAR CHART -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="purchasingVendorChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- CARD: TABEL DETAIL -->
                <div class="card mb-4">
                    <div class="card-header border-0">
                        <h6 class="mb-0">Detail Total Nilai Pembelian per Vendor</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th>Nama Vendor</th>
                                        <th class="text-end">Total Nilai Pembelian</th>
                                        <th class="text-end">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($vendorCount === 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Tidak ada data vendor.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tableRows as $i => $row):
                                            $percent = $totalAll > 0
                                                ? ($row['TotalNilaiPembelian'] / $totalAll * 100)
                                                : 0;
                                        ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($row['NamaVendor']) ?></td>
                                                <td class="text-end">
                                                    Rp <?= number_format($row['TotalNilaiPembelian'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-end">
                                                    <?= number_format($percent, 2, ',', '.') ?>%
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

    <!-- Bootstrap + Script Toggle SB Admin -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <!-- Chart.js -->
    <script>
        // DATA dari PHP
        const pvLabels = <?= json_encode($labels); ?>;
        const pvData = <?= json_encode($data); ?>;

        const ctxPV = document.getElementById('purchasingVendorChart').getContext('2d');

        let purchasingChart = new Chart(ctxPV, {
            type: 'bar',
            data: {
                labels: pvLabels,
                datasets: [{
                    label: 'Total Nilai Pembelian',
                    data: pvData,
                    backgroundColor: 'rgba(59, 130, 246, 0.35)',
                    borderColor: 'rgba(37, 99, 235, 0.9)',
                    borderWidth: 2,
                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.60)',
                    hoverBorderColor: 'rgba(37, 99, 235, 1)',
                    borderRadius: 6,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const label = ctx.dataset.label || '';
                                const value = ctx.parsed.y || 0;
                                const formatted = value.toLocaleString('id-ID');
                                return `${label}: ${formatted}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Responsif saat toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => purchasingChart.resize(), 300);
        });
    </script>

</body>

</html>