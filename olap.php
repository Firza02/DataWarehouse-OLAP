<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard SHAFIQ - OLAP</title>

    <!-- SB Admin CSS (konsisten dengan halaman lain) -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome (ikon sidebar dan navbar) -->
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

    <!-- Highcharts (kalau dibutuhkan) -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

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

        iframe {
            width: 100%;
            height: 650px;
            border: none;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <!-- NAVBAR -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-danger">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <i class="fas fa-store" style="color:grey"></i>
        </a>
        <a class="navbar-brand ps-3" href="home.php">FINAL PROJECT DATA WAREHOUSE & OLAP</a>

        <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- SIDEBAR + CONTENT -->
    <div id="layoutSidenav">

        <!-- SIDEBAR -->
        <?php include 'sidebar.php'; ?>

        <!-- CONTENT -->
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">

                <h1 class="mt-4">OLAP Mondrian</h1>

                <p class="mb-4">Berikut merupakan tampilan OLAP yang terintegrasi dengan Mondrian.</p>

                <div class="card mb-4">
                    <iframe name="mondrian"
                        src="http://localhost:8080/mondrian/testpage.jsp?query=sales_schema"
                        style="width:100%; height:650px; border:none;"></iframe>
                </div>
        </div>

        </main>
    </div>

    </div>

    <!-- Bootstrap + SB Admin Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <!-- Sidebar Toggle Resize Fix -->
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => {
                // iframe tidak butuh resize, tapi layout tetap diperbarui
            }, 300);
        });
    </script>

</body>

</html>