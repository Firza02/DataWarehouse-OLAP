<!-- sidebar.php -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <!-- ====================== -->
                <!--   MENU STAGING CHART    -->
                <!-- ====================== -->
                <div class="sb-sidenav-menu-heading">STAGING CHART</div>

                <a class="nav-link <?= ($currentPage == 'purchasing_vendor_chart.php') ? 'active' : '' ?>"
                    href="purchasing_vendor_chart.php">
                    <div class="sb-nav-link-icon icon-circle"><i class="fas fa-truck"></i></div>
                    Purchasing Vendor
                </a>

                <a class="nav-link <?= ($currentPage == 'sales_monthly_chart.php') ? 'active' : '' ?>"
                    href="sales_monthly_chart.php">
                    <div class="sb-nav-link-icon icon-circle"><i class="fas fa-chart-line"></i></div>
                    Sales Monthly
                </a>

                <a class="nav-link <?= ($currentPage == 'vacation_department_chart.php') ? 'active' : '' ?>"
                    href="vacation_department_chart.php">
                    <div class="sb-nav-link-icon icon-circle"><i class="fas fa-plane"></i></div>
                    Vacation per Department
                </a>

                <a class="nav-link <?= ($currentPage == 'salary_department_chart.php') ? 'active' : '' ?>"
                    href="salary_department_chart.php">
                    <div class="sb-nav-link-icon icon-circle"><i class="fas fa-wallet"></i></div>
                    Salary per Department
                </a>

                <a class="nav-link <?= ($currentPage == 'territory_category_chart.php') ? 'active' : '' ?>"
                    href="territory_category_chart.php">
                    <div class="sb-nav-link-icon icon-circle"><i class="fas fa-globe"></i></div>
                    Territory vs Category
                </a>

                <!-- ========== -->
                <!--    OLAP    -->
                <!-- ========== -->
                <div class="sb-sidenav-menu-heading">OLAP</div>
                <a class="nav-link" href="olap.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                    Mondrian
                </a>

                <!-- ============= -->
                <!--    ACCOUNT    -->
                <!-- ============= -->
                <div class="sb-sidenav-menu-heading">ACCOUNT</div>
                <a class="nav-link" href="logout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                    Logout
                </a>

            </div>
        </div>

           
    </nav>
</div>