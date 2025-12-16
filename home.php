<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$namaUser = htmlspecialchars($_SESSION['full_name'] ?: $_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard UASDWO</title>

    <!-- CSS utama yang sama dengan halaman lain -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        /* SECTION HERO */
        .home-hero {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .home-hero-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .home-hero-subtitle {
            font-size: .95rem;
            color: #6c757d;
        }

        /* CARD OVERVIEW */
        .overview-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .overview-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 35px rgba(15, 23, 42, .16);
        }

        .overview-card .card-body {
            padding: 1.25rem 1.3rem;
        }

        .overview-card-title {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }

        .overview-card-text {
            font-size: .9rem;
            color: #4b5563;
        }

        .overview-card .overview-icon {
            font-size: 2.3rem;
        }

        .overview-link {
            text-decoration: none;
            color: inherit;
        }

        .overview-link:hover {
            text-decoration: none;
        }

        /* FOOTER */
        footer.sticky-footer {
            border-top: 1px solid rgba(148, 163, 184, .25);
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <!-- NAVBAR (sama seperti halaman lain) -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <i class="fas fa-store" style="color:grey"></i>
        </a>
        <a class="navbar-brand ps-3" href="home.php">UASDWO</a>

        <!-- Tombol Toggle Sidebar -->
        <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- SIDEBAR + CONTENT -->
    <div id="layoutSidenav">

        <!-- SIDEBAR -->
        <?php include "sidebar.php"; ?>

        <!-- CONTENT -->
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">

                <!-- HERO -->
                <section class="home-hero">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="home-hero-title">
                                Welcome, <?= $namaUser; ?>!
                            </div>
                            <div class="home-hero-subtitle">
                                Executive Overview – Dashboard UASDWO
                            </div>
                        </div>
                    </div>
                </section>

                <!-- OVERVIEW CARDS -->
                <section class="mb-4">
                    <div class="row g-3">

                        <!-- Purchasing Overview -->
                        <div class="col-xl-4 col-md-6">
                            <a href="purchasing_vendor_chart.php" class="overview-link">
                                <div class="card overview-card border-left-primary">
                                    <div class="card-body">
                                        <div class="row g-0 align-items-center">
                                            <div class="col">
                                                <div class="overview-card-title text-primary">
                                                    PURCHASING OVERVIEW
                                                </div>
                                                <div class="overview-card-text">
                                                    Vendor dengan nilai pembelian terbesar dan distribusi nilai per vendor.
                                                </div>
                                            </div>
                                            <div class="col-auto ps-3">
                                                <i class="fas fa-truck overview-icon text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Sales Trend & Territory -->
                        <div class="col-xl-4 col-md-6">
                            <a href="sales_monthly_chart.php" class="overview-link">
                                <div class="card overview-card border-left-success">
                                    <div class="card-body">
                                        <div class="row g-0 align-items-center">
                                            <div class="col">
                                                <div class="overview-card-title text-success">
                                                    SALES TREND & TERRITORY
                                                </div>
                                                <div class="overview-card-text">
                                                    Tren penjualan bulanan serta pola per wilayah dan kategori produk.
                                                </div>
                                            </div>
                                            <div class="col-auto ps-3">
                                                <i class="fas fa-chart-line overview-icon text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- HR Analytics -->
                        <div class="col-xl-4 col-md-6">
                            <a href="vacation_department_chart.php" class="overview-link">
                                <div class="card overview-card border-left-info">
                                    <div class="card-body">
                                        <div class="row g-0 align-items-center">
                                            <div class="col">
                                                <div class="overview-card-title text-info">
                                                    HR ANALYTICS
                                                </div>
                                                <div class="overview-card-text">
                                                    Rata-rata cuti dan gaji terakhir per departemen.
                                                </div>
                                            </div>
                                            <div class="col-auto ps-3">
                                                <i class="fas fa-user-tie overview-icon text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Salary per Department -->
                        <div class="col-xl-4 col-md-6">
                            <a href="salary_department_chart.php" class="overview-link">
                                <div class="card overview-card border-left-warning">
                                    <div class="card-body">
                                        <div class="row g-0 align-items-center">
                                            <div class="col">
                                                <div class="overview-card-title text-warning">
                                                    SALARY PER DEPARTMENT
                                                </div>
                                                <div class="overview-card-text">
                                                    Distribusi rata-rata gaji terakhir untuk setiap departemen.
                                                </div>
                                            </div>
                                            <div class="col-auto ps-3">
                                                <i class="fas fa-wallet overview-icon text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Territory vs Category (Drilldown) -->
                        <div class="col-xl-4 col-md-6">
                            <a href="territory_category_chart.php" class="overview-link">
                                <div class="card overview-card border-left-secondary">
                                    <div class="card-body">
                                        <div class="row g-0 align-items-center">
                                            <div class="col">
                                                <div class="overview-card-title text-secondary">
                                                    TERRITORY VS CATEGORY
                                                </div>
                                                <div class="overview-card-text">
                                                    Perbandingan total penjualan per territory dengan drilldown kategori produk.
                                                </div>
                                            </div>
                                            <div class="col-auto ps-3">
                                                <i class="fas fa-globe overview-icon text-secondary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </section>


            </main>

            <!-- FOOTER -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Dashboard UASDWO</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <script>
        // tidak wajib, tapi kalau mau ada behaviour tambahan bisa di sini
    </script>
</body>

</html>