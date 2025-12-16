<?php
include 'koneksi.php';

$sql = "SELECT DepartmentName, Rata2Cuti_Hari 
        FROM stg_vacation_department
        ORDER BY Rata2Cuti_Hari DESC";
$result = mysqli_query($conn, $sql);

$labels    = [];
$data      = [];
$tableRows = [];
$topDept   = null;
$bottomDept = null;
$totalAll  = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['DepartmentName'];
    $data[]   = (float)$row['Rata2Cuti_Hari'];

    $tableRows[] = $row;
    $totalAll   += (float)$row['Rata2Cuti_Hari'];

    if ($topDept === null) {
        $topDept = $row;           // pertama (DESC) = tertinggi
    }
    $bottomDept = $row;            // terakhir di loop = terendah
}

$deptCount = count($tableRows);
$avgAll    = $deptCount > 0 ? $totalAll / $deptCount : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Vacation Department Chart</title>

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

                <h1 class="mt-4 mb-3">Rata-rata Cuti per Departemen (Hari)</h1>

                <!-- KPI CARDS -->
                <div class="row mb-4">
                    <!-- Card 1: Departemen dengan cuti tertinggi -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Departemen dengan Rata-rata Cuti Tertinggi</p>
                                <h5 class="mb-1">
                                    <?= $topDept ? htmlspecialchars($topDept['DepartmentName']) : '-' ?>
                                </h5>
                                <p class="text-success fw-semibold mb-0">
                                    <?= $topDept ? number_format($topDept['Rata2Cuti_Hari'], 2, ',', '.') : '0,00' ?> hari
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Departemen dengan cuti terendah -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Departemen dengan Rata-rata Cuti Terendah</p>
                                <h5 class="mb-1">
                                    <?= $bottomDept ? htmlspecialchars($bottomDept['DepartmentName']) : '-' ?>
                                </h5>
                                <p class="text-danger fw-semibold mb-0">
                                    <?= $bottomDept ? number_format($bottomDept['Rata2Cuti_Hari'], 2, ',', '.') : '0,00' ?> hari
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Rata-rata cuti keseluruhan -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Rata-rata Cuti Semua Departemen</p>
                                <h4 class="mb-0 text-primary">
                                    <?= number_format($avgAll, 2, ',', '.') ?> hari
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHART -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="vacationDeptChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABEL DETAIL -->
                <div class="card mb-4">
                    <div class="card-header border-0">
                        <h6 class="mb-0">Detail Rata-rata Cuti per Departemen</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th>Departemen</th>
                                        <th class="text-end">Rata-rata Cuti (Hari)</th>
                                        <th class="text-end">Posisi vs Rata-rata</th>
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
                                            $val = (float)$row['Rata2Cuti_Hari'];
                                            if (abs($val - $avgAll) < 0.01) {
                                                $posisi = "Sama dengan rata-rata";
                                            } elseif ($val > $avgAll) {
                                                $posisi = "Di atas rata-rata";
                                            } else {
                                                $posisi = "Di bawah rata-rata";
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($row['DepartmentName']) ?></td>
                                                <td class="text-end">
                                                    <?= number_format($val, 2, ',', '.') ?> hari
                                                </td>
                                                <td class="text-end">
                                                    <?= $posisi ?>
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

    <!-- Bootstrap + SB Admin Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <script>
        // DATA dari PHP
        const vdLabels = <?= json_encode($labels); ?>;
        const vdData = <?= json_encode($data); ?>;

        // Warna modern (Tailwind Blue 500)
        const barColor = "rgba(59, 130, 246, 0.65)"; // Soft Blue
        const barBorderColor = "rgba(37, 99, 235, 1)"; // Strong Blue

        const ctxVD = document.getElementById('vacationDeptChart').getContext('2d');

        let vacationChart = new Chart(ctxVD, {
            type: 'bar',
            data: {
                labels: vdLabels,
                datasets: [{
                    label: 'Rata-rata Cuti (Hari)',
                    data: vdData,
                    backgroundColor: barColor,
                    borderColor: barBorderColor,
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: "rgba(59, 130, 246, 0.85)",
                    hoverBorderColor: barBorderColor,
                    maxBarThickness: 38
                }]
            },
            options: {
                indexAxis: 'y', // horizontal bar
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
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
                                const val = ctx.parsed.x ?? 0;
                                return " " + val.toLocaleString("id-ID") + " hari";
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: "#334155",
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            borderDash: [4, 4],
                            color: "rgba(148,163,184,0.25)",
                        }
                    },
                    y: {
                        ticks: {
                            color: "#1e293b",
                            font: {
                                size: 13,
                                weight: "600"
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Agar chart responsif saat toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => vacationChart.resize(), 300);
        });
    </script>

</body>

</html>