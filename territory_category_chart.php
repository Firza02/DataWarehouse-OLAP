<?php
require 'koneksi.php';

// =============== LEVEL 1: TERRITORY =================
$sqlTerritory = "
    SELECT Territory,
           SUM(TotalQty) AS total_qty
    FROM stg_territory_category
    GROUP BY Territory
    ORDER BY Territory;
";
$resTerritory = mysqli_query($conn, $sqlTerritory);

$territoryLabels = [];
$territoryValues = [];

$topTerritoryName = null;
$topTerritoryQty  = 0;

while ($row = mysqli_fetch_assoc($resTerritory)) {
    $territoryLabels[] = $row['Territory'];
    $qty               = (int)$row['total_qty'];
    $territoryValues[] = $qty;

    if ($qty > $topTerritoryQty) {
        $topTerritoryQty  = $qty;
        $topTerritoryName = $row['Territory'];
    }
}

$totalAllTerritory = array_sum($territoryValues);

// mapping Territory => qty (buat tabel & JS)
$territorySummary = [];
foreach ($territoryLabels as $i => $t) {
    $territorySummary[$t] = $territoryValues[$i];
}

// =============== LEVEL 2: CATEGORY PER TERRITORY =================
$sqlCategory = "
    SELECT Territory,
           Category,
           SUM(TotalQty) AS total_qty
    FROM stg_territory_category
    GROUP BY Territory, Category
    ORDER BY Territory, Category;
";
$resCategory = mysqli_query($conn, $sqlCategory);

$categoryDataByTerritory = []; // ['Australia' => ['labels'=>[], 'data'=>[]], ...]

while ($row = mysqli_fetch_assoc($resCategory)) {
    $territory = $row['Territory'];
    $category  = $row['Category'];
    $qty       = (int)$row['total_qty'];

    if (!isset($categoryDataByTerritory[$territory])) {
        $categoryDataByTerritory[$territory] = [
            'labels' => [],
            'data'   => []
        ];
    }
    $categoryDataByTerritory[$territory]['labels'][] = $category;
    $categoryDataByTerritory[$territory]['data'][]   = $qty;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Territory vs Category</title>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles-table.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
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
            height: 400px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
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

    <div id="layoutSidenav">
        <?php include 'sidebar.php'; ?>

        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">
                <h1 class="mt-4">Distribusi Penjualan per Territory &amp; Kategori</h1>

                <!-- KPI CARDS -->
                <div class="row mb-3">
                    <!-- KPI global -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Total Qty Semua Territory</span>
                                    <i class="fas fa-globe-asia text-secondary"></i>
                                </div>
                                <h4 class="mb-0 fw-semibold text-primary" id="kpiGlobalTotal">
                                    <?= number_format($totalAllTerritory, 0, ',', '.') ?>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- KPI territory teratas / terpilih -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small" id="kpiSecondaryTitle">
                                        Territory dengan Total Qty Tertinggi
                                    </span>
                                    <i class="fas fa-map-marker-alt text-secondary"></i>
                                </div>
                                <h5 class="mb-1" id="kpiSelectedName">
                                    <?= htmlspecialchars($topTerritoryName ?: '-') ?>
                                </h5>
                                <p class="text-primary fw-semibold mb-0" id="kpiSelectedValue">
                                    <?= number_format($topTerritoryQty, 0, ',', '.') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHART + DRILLDOWN -->
                <div class="card mb-4 mt-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0" id="chartSubtitle">
                                Distribusi total qty per Territory
                            </h6>
                            <button id="btnBack" class="btn btn-sm btn-outline-secondary d-none">
                                Kembali ke Territory
                            </button>
                        </div>

                        <div class="chart-container">
                            <canvas id="territoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABEL DINAMIS -->
                <div class="card mb-4">
                    <div class="card-header border-0">
                        <h6 class="mb-0" id="tableTitle">Ringkasan Total Qty per Territory</h6>
                        <small class="text-muted" id="tableSubtitle">
                            Menunjukkan kontribusi setiap territory terhadap total qty penjualan global.
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                            <table class="table table-striped table-sm mb-0 align-middle" id="tableSummary">
                                <thead class="table-light">
                                    <tr id="tableHeaderRow">
                                        <th style="width:60px;">No</th>
                                        <th>Territory</th>
                                        <th class="text-end">Total Qty</th>
                                        <th class="text-end">Kontribusi terhadap Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"><!-- diisi via JS --></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script src="js/datatables-simple-demo.js"></script>

    <script>
        // ====== DATA PHP -> JS ======
        const territoryLabels = <?= json_encode($territoryLabels) ?>;
        const territoryValues = <?= json_encode($territoryValues) ?>;
        const territorySummary = <?= json_encode($territorySummary) ?>;
        const totalAllTerritory = <?= json_encode($totalAllTerritory) ?>;
        const categoryDataByTerritory = <?= json_encode($categoryDataByTerritory) ?>;

        const ctx = document.getElementById('territoryChart').getContext('2d');
        const btnBack = document.getElementById('btnBack');
        const chartSubtitle = document.getElementById('chartSubtitle');

        let currentLevel = 'territory';
        let currentTerritory = null; // untuk highlight saat kembali

        const kpiSecondaryTitle = document.getElementById('kpiSecondaryTitle');
        const kpiSelectedName = document.getElementById('kpiSelectedName');
        const kpiSelectedValue = document.getElementById('kpiSelectedValue');

        const tableTitle = document.getElementById('tableTitle');
        const tableSubtitle = document.getElementById('tableSubtitle');
        const tableHeaderRow = document.getElementById('tableHeaderRow');
        const tableBody = document.getElementById('tableBody');

        const categoryPalette = [
            "#3B82F6", "#EF4444", "#22C55E", "#F59E0B", "#8B5CF6",
            "#06B6D4", "#EC4899", "#A855F7", "#14B8A6", "#F97316"
        ];

        function formatNumberID(v) {
            return Number(v || 0).toLocaleString("id-ID");
        }

        // ========== TABEL INTERAKTIF ==========
        function renderTableTerritory() {
            tableTitle.textContent = "Ringkasan Total Qty per Territory";
            tableSubtitle.textContent =
                "Menunjukkan kontribusi setiap territory terhadap total qty penjualan global.";

            tableHeaderRow.innerHTML = `
            <th style="width:60px;">No</th>
            <th>Territory</th>
            <th class="text-end">Total Qty</th>
            <th class="text-end">Kontribusi terhadap Total</th>
        `;

            tableBody.innerHTML = "";

            let i = 1;
            for (const t of Object.keys(territorySummary)) {
                const qty = territorySummary[t] || 0;
                const percent = totalAllTerritory > 0 ? (qty / totalAllTerritory * 100) : 0;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${i++}</td>
                <td>${t}</td>
                <td class="text-end">${formatNumberID(qty)}</td>
                <td class="text-end">${percent.toFixed(2).replace('.', ',')}%</td>
            `;
                tableBody.appendChild(tr);
            }
        }

        function renderTableCategory(territoryName) {
            const cfg = categoryDataByTerritory[territoryName];
            if (!cfg) {
                tableBody.innerHTML = `
                <tr><td colspan="4" class="text-center text-muted py-3">
                Tidak ada data kategori untuk territory ini.
                </td></tr>`;
                return;
            }

            tableTitle.textContent = "Detail Qty per Category - " + territoryName;
            tableSubtitle.textContent =
                "Menunjukkan kontribusi setiap kategori di territory " + territoryName + ".";

            tableHeaderRow.innerHTML = `
            <th style="width:60px;">No</th>
            <th>Category</th>
            <th class="text-end">Total Qty</th>
            <th class="text-end">Kontribusi dalam Territory</th>
        `;

            tableBody.innerHTML = "";

            const totalTerritory = cfg.data.reduce((a, b) => a + Number(b || 0), 0);

            cfg.labels.forEach((cat, idx) => {
                const qty = cfg.data[idx] || 0;
                const percent = totalTerritory > 0 ? (qty / totalTerritory * 100) : 0;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${cat}</td>
                <td class="text-end">${formatNumberID(qty)}</td>
                <td class="text-end">${percent.toFixed(2).replace('.', ',')}%</td>
            `;
                tableBody.appendChild(tr);
            });
        }

        // ========== KPI INTERAKTIF ==========
        function setKPIForGlobal() {
            kpiSecondaryTitle.textContent = "Territory dengan Total Qty Tertinggi";
            kpiSelectedName.textContent = "<?= htmlspecialchars($topTerritoryName ?: '-') ?>";
            kpiSelectedValue.textContent = "<?= number_format($topTerritoryQty, 0, ',', '.') ?>";
        }

        function setKPIForTerritory(territoryName) {
            const total = territorySummary[territoryName] || 0;
            kpiSecondaryTitle.textContent = "Territory yang Dipilih";
            kpiSelectedName.textContent = territoryName;
            kpiSelectedValue.textContent = formatNumberID(total);
        }

        // ========== CHART & DRILLDOWN ==========
        function renderTerritory(chart, selectedName = null) {
            chart.data.labels = territoryLabels;
            chart.data.datasets[0].data = territoryValues;

            const baseColor = "rgba(59,130,246,0.6)";
            const baseBorder = "rgba(37,99,235,1)";

            chart.data.datasets[0].backgroundColor = territoryLabels.map(t =>
                t === selectedName ? "rgba(37,99,235,0.85)" : baseColor
            );

            chart.data.datasets[0].borderColor = baseBorder;
            chart.data.datasets[0].borderWidth = territoryLabels.map(t =>
                t === selectedName ? 3 : 2
            );
            chart.data.datasets[0].borderRadius = 8;
            chart.data.datasets[0].maxBarThickness = 46;

            chart.options.plugins.title.text = 'Total Qty per Territory';
            chartSubtitle.textContent = "Distribusi total qty per Territory";

            currentLevel = 'territory';
            btnBack.classList.add('d-none');

            setKPIForGlobal();
            renderTableTerritory();

            chart.update();
        }

        function renderCategory(chart, territoryName) {
            const cfg = categoryDataByTerritory[territoryName];
            if (!cfg) return;

            chart.data.labels = cfg.labels;
            chart.data.datasets[0].data = cfg.data;

            const colors = cfg.labels.map((_, idx) => categoryPalette[idx % categoryPalette.length]);
            chart.data.datasets[0].backgroundColor = colors.map(c => c + "B3");
            chart.data.datasets[0].borderColor = colors;
            chart.data.datasets[0].borderWidth = 2;
            chart.data.datasets[0].borderRadius = 8;
            chart.data.datasets[0].maxBarThickness = 46;

            chart.options.plugins.title.text = 'Total Qty per Category - ' + territoryName;
            chartSubtitle.textContent = "Rincian kategori untuk Territory: " + territoryName;

            currentLevel = 'category';
            currentTerritory = territoryName;
            btnBack.classList.remove('d-none');

            setKPIForTerritory(territoryName);
            renderTableCategory(territoryName);

            chart.update();
        }

        const territoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: territoryLabels,
                datasets: [{
                    label: 'Total Qty',
                    data: territoryValues
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Qty per Territory'
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "rgba(0,0,0,0.8)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                        padding: 10,
                        callbacks: {
                            label: function(ctx) {
                                const lbl = ctx.label || '';
                                const val = ctx.parsed.y || 0;
                                return ' ' + lbl + ': ' + formatNumberID(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: "#1e293b",
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: "#475569",
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            borderDash: [4, 4],
                            color: "rgba(148,163,184,0.3)"
                        }
                    }
                },
                onClick: (evt, elements) => {
                    if (!elements.length || currentLevel !== 'territory') return;
                    const idx = elements[0].index;
                    const territoryName = territoryLabels[idx];
                    renderCategory(territoryChart, territoryName);
                }
            }
        });

        // init awal
        renderTerritory(territoryChart);

        // tombol back -> balik ke view territory + highlight territory terakhir
        btnBack.addEventListener('click', () => {
            renderTerritory(territoryChart, currentTerritory);
        });

        // responsif sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => territoryChart.resize(), 300);
        });
    </script>

</body>

</html>