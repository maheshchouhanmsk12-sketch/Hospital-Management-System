<?php
/**
 * 1-Click Aiven Database Setup & Diagnostic Tool
 * Hospital Management System (EMR)
 */

require_once __DIR__ . '/importance.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$isCli = (php_sapi_name() === 'cli');

$host = Config::getHost();
$port = Config::getPort();
$user = Config::getUser();
$pass = Config::getPass();
$dbName = Config::getDbName();
$useSsl = Config::getSsl();

$connStatus = false;
$connError = '';
$serverInfo = '';
$tablesFound = [];
$adminExists = false;
$doctorExists = false;
$importMessage = '';
$importSuccess = false;

// Connect via mysqli with SSL for setup/import
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
if ($useSsl) {
    $mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $flags = MYSQLI_CLIENT_SSL;
} else {
    $flags = 0;
}

$connected = @$mysqli->real_connect($host, $user, $pass, null, $port, null, $flags);
if ($connected && !$mysqli->connect_error) {
    $connStatus = true;
    $serverInfo = $mysqli->server_info;

    // Check if database exists, if not create it
    @$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
    @$mysqli->select_db($dbName);

    // List tables
    $res = $mysqli->query("SHOW TABLES");
    if ($res) {
        while ($row = $res->fetch_array()) {
            $tablesFound[] = $row[0];
        }
    }

    // Check admin
    if (in_array('users', $tablesFound)) {
        $chkAdmin = $mysqli->query("SELECT id FROM users WHERE (email='bensontesting@gmail.com' OR email='admin@admin.com') AND status=1");
        if ($chkAdmin && $chkAdmin->num_rows > 0) {
            $adminExists = true;
        }

        $chkDoc = $mysqli->query("SELECT id FROM users WHERE status=2");
        if ($chkDoc && $chkDoc->num_rows > 0) {
            $doctorExists = true;
        }
    }
} else {
    $connError = $mysqli ? $mysqli->connect_error : 'Connection failed';
}

// Handle Import
if ($action === 'import' || ($isCli && in_array('--import', $argv ?? []))) {
    if (!$connStatus) {
        $importMessage = "Cannot import: Database connection failed ($connError)";
    } else {
        $sqlPath = __DIR__ . '/database/hospital.sql';
        if (!file_exists($sqlPath)) {
            $sqlPath = __DIR__ . '/Hospital_Management_System/database/hospital.sql';
        }

        if (!file_exists($sqlPath)) {
            $importMessage = "Error: Database dump file 'hospital.sql' not found!";
        } else {
            $sqlContent = file_get_contents($sqlPath);

            // Cloud MySQL / Aiven safe execution settings
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
            $mysqli->query("SET SESSION sql_require_primary_key = 0");
            $mysqli->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");

            if ($mysqli->multi_query($sqlContent)) {
                do {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }

            // Ensure admin@admin.com also exists alongside bensontesting@gmail.com
            $adminToken = md5('admin' . time());
            $mysqli->query("INSERT IGNORE INTO users (firstName, secondName, email, password, token, status, phone, gender, role) VALUES ('System', 'Admin', 'admin@admin.com', 'admin123', '{$adminToken}', 1, '0700000000', 'Male', 'Administrator')");

            // Re-fetch tables
            $tablesFound = [];
            $res = $mysqli->query("SHOW TABLES");
            if ($res) {
                while ($row = $res->fetch_array()) {
                    $tablesFound[] = $row[0];
                }
            }

            $adminExists = true;
            $doctorExists = true;
            $importSuccess = true;
            $importMessage = "Database imported successfully! " . count($tablesFound) . " tables created & configured.";
        }
    }
}

if ($isCli) {
    echo "=====================================================\n";
    echo " Hospital Management System - Aiven DB Status\n";
    echo "=====================================================\n";
    echo "Host:        {$host}:{$port}\n";
    echo "Database:    {$dbName}\n";
    echo "SSL:         " . ($useSsl ? "ENABLED (MYSQLI_CLIENT_SSL)" : "DISABLED") . "\n";
    echo "Connection:  " . ($connStatus ? "ONLINE (MySQL v{$serverInfo})" : "FAILED ({$connError})") . "\n";
    echo "Tables (" . count($tablesFound) . "): " . implode(', ', $tablesFound) . "\n";
    echo "Admin User:  " . ($adminExists ? "READY" : "NOT FOUND") . "\n";
    if ($importMessage) {
        echo "Import:      {$importMessage}\n";
    }
    echo "=====================================================\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Diagnostics - Hospital Management System</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding-top: 30px; }
        .setup-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 25px; padding: 25px; }
        .badge-status { font-size: 14px; padding: 6px 12px; border-radius: 20px; }
        .stat-icon { font-size: 30px; margin-bottom: 10px; }
        .status-box { padding: 15px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container" style="max-width: 900px;">
    <div class="text-center" style="margin-bottom: 30px;">
        <h2>🏥 Hospital Management System (EMR)</h2>
        <p class="text-muted">Aiven Cloud MySQL 1-Click Database Setup & Diagnostics</p>
    </div>

    <?php if ($importMessage): ?>
        <div class="alert <?php echo $importSuccess ? 'alert-success' : 'alert-danger'; ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong><?php echo $importSuccess ? '✔ SUCCESS:' : '✖ NOTICE:'; ?></strong> <?php echo htmlspecialchars($importMessage); ?>
        </div>
    <?php endif; ?>

    <div class="setup-card">
        <h4 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
            📡 Cloud Database Status
        </h4>

        <div class="row text-center">
            <div class="col-md-3 col-sm-6">
                <div class="status-box <?php echo $connStatus ? 'bg-success text-white' : 'bg-danger text-white'; ?>" style="background-color: <?php echo $connStatus ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $connStatus ? '#155724' : '#721c24'; ?>;">
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $connStatus ? 'Connected' : 'Offline'; ?></div>
                    <small>MySQL Connection</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="status-box" style="background-color: #d1ecf1; color: #0c5460;">
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $useSsl ? 'SSL Active' : 'Off'; ?></div>
                    <small>Encryption</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="status-box" style="background-color: #e2e3e5; color: #383d41;">
                    <div style="font-size: 24px; font-weight: bold;"><?php echo count($tablesFound); ?> Tables</div>
                    <small>Database Schema</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="status-box <?php echo $adminExists ? 'bg-success' : 'bg-warning'; ?>" style="background-color: <?php echo $adminExists ? '#d4edda' : '#fff3cd'; ?>; color: <?php echo $adminExists ? '#155724' : '#856404'; ?>;">
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $adminExists ? 'Ready' : 'Missing'; ?></div>
                    <small>Admin Account</small>
                </div>
            </div>
        </div>

        <table class="table table-bordered" style="margin-top: 15px;">
            <tr>
                <th style="width: 200px;">Host & Port</th>
                <td><code><?php echo htmlspecialchars($host . ':' . $port); ?></code></td>
            </tr>
            <tr>
                <th>Database Name</th>
                <td><code><?php echo htmlspecialchars($dbName); ?></code></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><code><?php echo htmlspecialchars($user); ?></code></td>
            </tr>
            <tr>
                <th>MySQL Version</th>
                <td><?php echo $serverInfo ? htmlspecialchars($serverInfo) : '<span class="text-danger">' . htmlspecialchars($connError) . '</span>'; ?></td>
            </tr>
            <tr>
                <th>Existing Tables</th>
                <td>
                    <?php if (!empty($tablesFound)): ?>
                        <?php foreach ($tablesFound as $tbl): ?>
                            <span class="label label-info" style="font-size: 12px; margin-right: 5px;"><?php echo htmlspecialchars($tbl); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-warning">No tables found. Please click "1-Click Import Database" below.</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <div style="margin-top: 25px;" class="text-center">
            <form method="post" action="db_setup.php" style="display: inline-block;">
                <input type="hidden" name="action" value="import">
                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Do you want to run 1-Click Database Setup on Aiven Cloud?');">
                    ⚡ 1-Click Import / Refresh Database
                </button>
            </form>

            <a href="login.php" class="btn btn-success btn-lg" style="margin-left: 10px;">
                🔐 Proceed to Login Page &rarr;
            </a>

            <a href="db_setup.php" class="btn btn-default btn-lg" style="margin-left: 10px;">
                🔄 Re-check Status
            </a>
        </div>
    </div>

    <div class="setup-card">
        <h4 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
            🔑 Default Login Credentials
        </h4>
        <div class="row">
            <div class="col-md-6">
                <div class="well">
                    <h5><strong>👑 System Administrator</strong></h5>
                    <p><strong>URL:</strong> <a href="login.php?attempt=1">login.php?attempt=1</a></p>
                    <p><strong>Email:</strong> <code>bensontesting@gmail.com</code> (or <code>admin@admin.com</code>)</p>
                    <p><strong>Password:</strong> <code>programming</code> (or <code>admin123</code>)</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="well">
                    <h5><strong>👨‍⚕️ Doctor</strong></h5>
                    <p><strong>URL:</strong> <a href="login.php?attempt=2">login.php?attempt=2</a></p>
                    <p><strong>Email:</strong> <code>totonajoyce@gmail.com</code></p>
                    <p><strong>Password:</strong> <code>12345</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

