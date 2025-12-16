<?php
include 'koneksi.php';

/*
  STRUKTUR DIASUMSIKAN:
  stg_sales_monthly (
      Tahun INT,
      Bulan INT,
      Kategori VARCHAR(..),
      TotalSales DECIMAL/INT
  )
*/

$sql = "
    SELECT 
        Tahun,
        Bulan,
        Kategori AS Category,
        SUM(TotalSales) AS total_sales
    FROM stg_sales_monthly
    GROUP BY Tahun, Bulan, Kategori
    ORDER BY Tahun, Bulan, Kategori
";

$result = mysqli_query($conn, $sql) or die('Query error: ' . mysqli_error($conn));

$periods         = [];   // label waktu: 2004-01, 2004-02, ...
$rawData         = [];   // [Category][periode] = total_sales
$totalByCategory = [];   // [Category] = total

while ($row = mysqli_fetch_assoc($result)) {
    $periode  = $row['Tahun'] . '-' . str_pad($row['Bulan'], 2, '0', STR_PAD_LEFT);
    $category = $row['Category'];
    $sales    = (float)$row['total_sales'];

    if (!in_array($periode, $periods, true)) {
        $periods[] = $periode;
    }

    if (!isset($rawData[$category])) {
        $rawData[$category] = [];
        $totalByCategory[$category] = 0;
    }

    $rawData[$category][$periode] = $sales;
    $totalByCategory[$category]  += $sales;
}

// Ratakan ke array per kategori mengikuti urutan $periods
$dataByCategory = []; // [Category] => [v1, v2, ...]
foreach ($rawData as $cat => $map) {
    $rowValues = [];
    foreach ($periods as $p) {
        $rowValues[] = isset($map[$p]) ? (float)$map[$p] : 0;
    }
    $dataByCategory[$cat] = $rowValues;
}

$totalAll   = array_sum($totalByCategory);
$categories = array_keys($dataByCategory);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard Adventure Works - Sales Monthly</title>

    <!-- SB Admin CSS -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome -->
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

                <h1 class="mt-4">Tren Penjualan Bulanan per Kategori</h1>

                <!-- CARD: FILTER + KPI + LINE CHART -->
                <div class="card mb-4 mt-3">
                    <div class="card-body">

                        <!-- FILTER + KPI TOTAL SALES -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="categoryFilter" class="form-label mb-1">
                                    Filter Kategori
                                </label>
                                <select id="categoryFilter" class="form-select form-select-sm" style="max-width:260px;">
                                    <option value="ALL">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>">
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
                                <div class="card border-left-primary shadow h-100" style="min-width: 240px;">
                                    <div class="card-body py-2 px-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Sales
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSalesDisplay"></div>
                                        <div class="small text-muted" id="totalSalesSubtitle">Semua kategori</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CHART: TREN BULANAN -->
                        <div class="chart-container">
                            <canvas id="salesMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ROW: BAR CHART + TABEL KATEGORI -->
                <div class="row mb-4">
                    <!-- Bar chart total per kategori -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header border-0">
                                <h6 class="mb-0">Total Penjualan per Kategori (Agregat)</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height:360px;">
                                    <canvas id="categoryBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel detail kategori -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header border-0">
                                <h6 class="mb-0">Detail Total Penjualan per Kategori</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:60px;">No</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Total Penjualan</th>
                                                <th class="text-end">Kontribusi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($totalByCategory)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        Tidak ada data kategori.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php
                                                $i = 1;
                                                foreach ($totalByCategory as $cat => $tot):
                                                    $percent = $totalAll > 0 ? ($tot / $totalAll * 100) : 0;
                                                ?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= htmlspecialchars($cat); ?></td>
                                                        <td class="text-end"><?= number_format($tot, 0, ',', '.'); ?></td>
                                                        <td class="text-end"><?= number_format($percent, 2, ',', '.'); ?>%</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- JS Bootstrap + SB Admin -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <script>
        // ===== DATA PHP -> JS =====
        const periods = <?= json_encode($periods); ?>;
        const dataByCat = <?= json_encode($dataByCategory); ?>; // {cat: [..]}
        const totalByCat = <?= json_encode($totalByCategory); ?>; // {cat: total}
        const totalAll = <?= json_encode($totalAll); ?>;
        const categories = Object.keys(dataByCat);

        // Palet warna modern (maksimal 10 kategori)
        const colorPalette = [
            "#3B82F6", // Blue
            "#EF4444", // Red
            "#22C55E", // Green
            "#F59E0B", // Amber
            "#8B5CF6", // Purple
            "#06B6D4", // Cyan
            "#EC4899", // Pink
            "#A855F7", // Violet
            "#14B8A6", // Teal
            "#F87171" // Light red
        ];

        const ctxSM = document.getElementById('salesMonthlyChart').getContext('2d');
        const ctxCatBar = document.getElementById('categoryBarChart').getContext('2d');

        const selCategory = document.getElementById('categoryFilter');
        const totalDisplay = document.getElementById('totalSalesDisplay');
        const totalSub = document.getElementById('totalSalesSubtitle');

        function formatNumberID(v) {
            return Number(v || 0).toLocaleString('id-ID');
        }

        function updateKPIAll() {
            totalDisplay.textContent = formatNumberID(totalAll);
            totalSub.textContent = 'Semua kategori';
        }

        function updateKPICategory(cat) {
            const t = totalByCat[cat] || 0;
            totalDisplay.textContent = formatNumberID(t);
            totalSub.textContent = 'Kategori: ' + cat;
        }

        function buildDatasetsAll() {
            const ds = [];
            categories.forEach((cat, index) => {
                const color = colorPalette[index % colorPalette.length];
                ds.push({
                    label: cat,
                    data: dataByCat[cat],
                    borderColor: color,
                    backgroundColor: color + "33",
                    borderWidth: 2.5,
                    tension: 0.35,
                    pointRadius: 1.8,
                    pointHoverRadius: 4,
                    pointBackgroundColor: color,
                    pointHoverBackgroundColor: "#ffffff",
                });
            });
            return ds;
        }

        function buildDatasetSingle(cat) {
            const index = categories.indexOf(cat);
            const color = colorPalette[index % colorPalette.length];
            return [{
                label: cat,
                data: dataByCat[cat],
                borderColor: color,
                backgroundColor: color + "33",
                borderWidth: 3,
                tension: 0.35,
                pointRadius: 2.5,
                pointHoverRadius: 5,
                pointBackgroundColor: color,
                pointHoverBackgroundColor: "#ffffff",
            }];
        }

        let salesMonthlyChart = null;
        let categoryBarChart = null;

        if (!categories.length || !periods.length) {
            console.warn('Tidak ada data untuk ditampilkan.');
            totalDisplay.textContent = '0';
            totalSub.textContent = 'Tidak ada data';
        } else {
            // LINE CHART: TREN BULANAN
            salesMonthlyChart = new Chart(ctxSM, {
                type: 'line',
                data: {
                    labels: periods,
                    datasets: buildDatasetsAll()
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: "#1e293b",
                                font: {
                                    size: 13
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: "rgba(0,0,0,0.8)",
                            titleFont: {
                                weight: "bold"
                            },
                            titleColor: "#fff",
                            bodyColor: "#fff",
                            padding: 10,
                            callbacks: {
                                label: function(ctx) {
                                    const label = ctx.dataset.label || '';
                                    const val = ctx.parsed.y || 0;
                                    return `${label}: ${formatNumberID(val)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: "#334155",
                                maxRotation: 45,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                color: "#334155"
                            },
                            grid: {
                                borderDash: [4, 4],
                                color: "rgba(148,163,184,0.2)"
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // BAR CHART: TOTAL PER KATEGORI
            const catLabels = categories;
            const catTotals = catLabels.map(c => totalByCat[c] || 0);

            categoryBarChart = new Chart(ctxCatBar, {
                type: 'bar',
                data: {
                    labels: catLabels,
                    datasets: [{
                        label: 'Total Penjualan',
                        data: catTotals,
                        backgroundColor: catLabels.map((_, i) => colorPalette[i % colorPalette.length] + "88"),
                        borderColor: catLabels.map((_, i) => colorPalette[i % colorPalette.length]),
                        borderWidth: 2,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.parsed.y || 0;
                                    return ' ' + formatNumberID(val);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: "#334155"
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: "#334155"
                            },
                            grid: {
                                borderDash: [4, 4],
                                color: "rgba(148,163,184,0.2)"
                            }
                        }
                    }
                }
            });

            // KPI awal
            updateKPIAll();

            // Event filter kategori
            selCategory.addEventListener('change', function() {
                const val = this.value;

                if (val === 'ALL') {
                    salesMonthlyChart.data.datasets = buildDatasetsAll();
                    updateKPIAll();
                } else {
                    salesMonthlyChart.data.datasets = buildDatasetSingle(val);
                    updateKPICategory(val);
                }
                salesMonthlyChart.update();
            });

            // Responsif saat toggle sidebar
            document.getElementById('sidebarToggle').addEventListener('click', () => {
                setTimeout(() => {
                    if (salesMonthlyChart) salesMonthlyChart.resize();
                    if (categoryBarChart) categoryBarChart.resize();
                }, 300);
            });
        }
    </script>
</body>

</html>