<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the logged-in user's email
if (!isset($_SESSION['user_email'])) {
    $user_id = $_SESSION['user_id'];
    $statement = $pdo->prepare("SELECT email FROM admin WHERE user_id = ?");
    $statement->execute([$user_id]);
    $email = $statement->fetchColumn();
    $_SESSION['user_email'] = $email;
} else {
    $email = $_SESSION['user_email'];
}

// Fetch all clearance periods for the dropdown
$periods_stmt = $pdo->prepare("SELECT Cpid, school_year, semester, clearancetype FROM clearance_period");
$periods_stmt->execute();
$periods = $periods_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle selected clearance period
$selected_cpid = $_GET['Cpid'] ?? $periods[0]['Cpid']; // Default to the first record if no selection

// Fetch clearance data for the selected period
$statement = $pdo->prepare("
    SELECT SUM(c.status = 'Complete') AS complete_count,
           SUM(c.status = 'Pending') AS pending_count
    FROM clearance_period cp
    LEFT JOIN clearance c ON cp.Cpid = c.Cpid
    WHERE cp.Cpid = ?
    GROUP BY cp.Cpid
");
$statement->execute([$selected_cpid]);
$clearance_data = $statement->fetch(PDO::FETCH_ASSOC);

// Fetch details of the selected clearance period
$selected_period_stmt = $pdo->prepare("
    SELECT school_year, semester, clearancetype 
    FROM clearance_period WHERE Cpid = ?
");
$selected_period_stmt->execute([$selected_cpid]);
$selected_period = $selected_period_stmt->fetch(PDO::FETCH_ASSOC);

// Calculate percentages
$total = ($clearance_data['complete_count'] ?? 0) + ($clearance_data['pending_count'] ?? 0);
$complete_percentage = $total > 0 ? round(($clearance_data['complete_count'] / $total) * 100, 2) : 0;
$pending_percentage = $total > 0 ? round(($clearance_data['pending_count'] / $total) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link rel="stylesheet" href="Admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Ensure the sidebar stretches vertically */
        .sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            width: 250px;
            background-color: #800000;
        }

        .logo {
            text-align: center;
            padding: 10px;
            color: #fff;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
        }

        a.button, p {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            text-align: center;
        }

        a.button:hover {
            background-color: #34495e;
        }

        /* Stick "Log Out" and "Logged in as" to the bottom */
        .sidebar-bottom {
            margin-top: auto;
            text-align: center;
        }

        .sidebar-bottom a.button {
            margin-bottom: 0;
        }

        .sidebar-bottom p {
            margin-top: 0;
        }
        
    </style>
</head>
<body>
<div class="sidebar">
    <!-- Add the logo -->
    <img src="/images/perpetualsmallicon.png" alt="Perpetual Logo" class="logo-image">
    <ul>
        <li>
            <a href="Admin_dashboard.php" class="button">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="Admin_Create_Account.php" class="button">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </li>
        <li>
            <a href="Create_Clearanceform.php" class="button">
                <i class="fas fa-file-alt"></i> Create ClearanceForm
            </a>
        </li>
        <li>
            <a href="#" class="button" onclick="openQRModal()">
                <i class="fas fa-qrcode"></i> Scan QR
            </a>
        </li>
        <li>
            <a href="Graph.php" class="button">
                <i class="fas fa-chart-bar"></i> Graph
            </a>
        </li>
        
    </ul>
    

    <!-- Sidebar bottom: Log Out and Logged in as -->
    <div class="sidebar-bottom">
            <a href="../logout.php" class="button">
            <i class="fas fa-sign-out-alt"></i> Log Out
    </a>
            <p>Logged in as: <?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>
</div>
       
    <div class="main-content">
        <h2>Clearance Status Graph</h2>

        <!-- Dropdown Form -->
        <form method="GET" action="Graph.php">
            <label for="Cpid">Select Clearance Period:</label>
            <select name="Cpid" id="Cpid" onchange="this.form.submit()">
                <?php foreach ($periods as $period): ?>
                    <option value="<?php echo $period['Cpid']; ?>" 
                        <?php echo ($selected_cpid == $period['Cpid']) ? 'selected' : ''; ?>>
                        <?php echo $period['school_year'] . ' - ' . $period['semester'] . ' (' . $period['clearancetype'] . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <canvas id="clearanceGraph"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('clearanceGraph').getContext('2d');

        // Data from PHP
        const clearanceData = <?php echo json_encode($clearance_data); ?>;
        const total = <?php echo $total; ?>;
        const completePercentage = <?php echo $complete_percentage; ?>;
        const pendingPercentage = <?php echo $pending_percentage; ?>;
        const selectedPeriod = "<?php echo $selected_period['school_year'] . ' - ' . $selected_period['semester'] . ' (' . $selected_period['clearancetype'] . ')'; ?>";

        // Create the chart
        const clearanceGraph = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Complete', 'Pending'],
                datasets: [{
                    label: selectedPeriod,
                    data: [clearanceData.complete_count || 0, clearanceData.pending_count || 0],
                    backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Clearance Status for Selected Period'
                    },
                    datalabels: {
                        display: true,
                        color: 'white',
                        anchor: 'center',
                        align: 'center',
                        formatter: (value, context) => {
                            const percentage = context.dataIndex === 0 ? completePercentage : pendingPercentage;
                            return `${value}\n(${percentage}%)`; // Show count and percentage
                        },
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            plugins: [ChartDataLabels] // Enable Data Labels Plugin
        });
    </script>
</body>
</html>
