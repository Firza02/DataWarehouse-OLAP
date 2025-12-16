<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to safely handle database errors
function handleDBError($conn, $query = '') {
    $error = mysqli_error($conn);
    error_log("Database Error: " . $error . " in query: " . $query);
    return "Database Error: " . $error;
}

try {
    require('koneksi.php');

    // Check connection
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Query to get total sales per year
    $sql = "SELECT t.Tahun, SUM(fs.SalesAmount) as TotalSales
            FROM factsales fs
            JOIN time t ON fs.timeid = t.timeid
            WHERE t.Tahun BETWEEN 2011 AND 2014
            GROUP BY t.Tahun
            ORDER BY t.Tahun;";

    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception(handleDBError($conn, $sql));
    }

    // Format data for Highcharts
    $chartData = array();
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            // Validate data
            if (!isset($row['Tahun']) || !isset($row['TotalSales'])) {
                error_log("Invalid row data: " . print_r($row, true));
                continue;
            }
            
            $year = filter_var($row['Tahun'], FILTER_VALIDATE_INT);
            $sales = filter_var($row['TotalSales'], FILTER_VALIDATE_FLOAT);
            
            if ($year === false || $sales === false) {
                error_log("Data validation failed for row: " . print_r($row, true));
                continue;
            }
            
            $chartData[] = array($year, $sales);
        }
    }

    // Validate final data
    if (empty($chartData)) {
        throw new Exception("No valid data found for the chart");
    }

    // Convert to JSON, check for errors
    $jsonData = json_encode($chartData);
    if ($jsonData === false) {
        throw new Exception("JSON encoding failed: " . json_last_error_msg());
    }

} catch (Exception $e) {
    error_log("Error in chart generation: " . $e->getMessage());
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Dashboard</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            height: 100vh;
            overflow: hidden; /* Menghilangkan scroll pada body */
        }

        .layout {
            display: flex;
            height: 100%;
        }

        .sidebar {
            width: 250px; /* Lebar sidebar */
            background-color: #f4f4f4;
            border-right: 1px solid #ccc;
            padding: 20px;
        }

        .content {
            flex: 1; /* Konten utama mengambil sisa ruang */
            padding: 20px;
            overflow: hidden; /* Menghindari scroll di area konten */
        }

        #container {
            width: 100%;
            height: 100%; /* Chart mengisi area konten */
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <?php include('sidebar.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <h3>Error Loading Chart</h3>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            <div id="container"></div>
            <center>
    <p class="highcharts-description">
        Berikut merupakan grafik untuk menampilkan Total Penjualan selama rentang 2011-2014
	</center>
        </div>
    </div>
    <?php if (!isset($error)): ?>
    <script>
    try {
        const chartData = <?php echo $jsonData; ?>;
        
        // Validate client-side data
        if (!Array.isArray(chartData) || chartData.length === 0) {
            throw new Error('Invalid or empty chart data');
        }

        Highcharts.chart('container', {
            chart: {
                type: 'line',
                events: {
                    load: function() {
                        if (this.series[0].data.length === 0) {
                            this.renderer.text('No data to display', 140, 140)
                                .css({
                                    color: '#666',
                                    fontSize: '16px'
                                })
                                .add();
                        }
                    }
                }
            },
            title: {
                text: 'Total Sales Amount by Year (2011-2014)',
                align: 'center'
            },
            xAxis: {
                title: {
                    text: 'Year'
                },
                categories: [2011, 2012, 2013, 2014]
            },
            yAxis: {
                title: {
                    text: 'Total Sales Amount'
                },
                labels: {
                    formatter: function() {
                        return '$' + this.value.toLocaleString();
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>Year: </b>' + this.x + '<br>' +
                           '<b>Total Sales: </b>$' + this.y.toLocaleString();
                }
            },
            series: [{
                name: 'Sales Amount',
                data: chartData,
                color: '#4e73df'
            }],
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });
    } catch (e) {
        console.error('Error creating chart:', e);
        document.getElementById('container').innerHTML = 
            '<div class="error-message">Error creating chart: ' + e.message + '</div>';
    }
    </script>
    <?php endif; ?>

    <!-- Debug information for development -->
    <?php if (defined('DEBUG') && DEBUG): ?>
    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa;">
        <h4>Debug Information:</h4>
        <pre><?php 
            echo "Raw Data:\n";
            print_r($chartData);
            echo "\n\nJSON Data:\n";
            echo $jsonData;
        ?></pre>
    </div>
    <?php endif; ?>
</body>
</html>