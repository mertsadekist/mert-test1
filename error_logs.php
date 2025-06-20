<?php
// error_logs.php - Error Logs Viewer
session_start();
require_once 'auth.php';
require_once 'includes/ErrorHandler.php';

// Check if user has permission to view error logs
if (!can('manage_users')) {
    header("Location: dashboard.php");
    exit();
}

// Initialize language session if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}

// Translations array
$translations = [
    'ar' => [
        'page_title' => 'سجل الأخطاء',
        'error_logs' => 'سجل الأخطاء',
        'refresh' => 'تحديث',
        'clear_logs' => 'مسح السجل',
        'download_logs' => 'تحميل السجل',
        'no_errors' => 'لا توجد أخطاء مسجلة',
        'timestamp' => 'التوقيت',
        'error_type' => 'نوع الخطأ',
        'message' => 'الرسالة',
        'file' => 'الملف',
        'line' => 'السطر',
        'confirm_clear' => 'هل أنت متأكد من مسح جميع سجلات الأخطاء؟',
        'logs_cleared' => 'تم مسح سجلات الأخطاء بنجاح',
        'error_clearing' => 'حدث خطأ أثناء مسح السجلات',
        'last_updated' => 'آخر تحديث',
        'auto_refresh' => 'تحديث تلقائي',
        'enable' => 'تفعيل',
        'disable' => 'إيقاف'
    ],
    'en' => [
        'page_title' => 'Error Logs',
        'error_logs' => 'Error Logs',
        'refresh' => 'Refresh',
        'clear_logs' => 'Clear Logs',
        'download_logs' => 'Download Logs',
        'no_errors' => 'No errors logged',
        'timestamp' => 'Timestamp',
        'error_type' => 'Error Type',
        'message' => 'Message',
        'file' => 'File',
        'line' => 'Line',
        'confirm_clear' => 'Are you sure you want to clear all error logs?',
        'logs_cleared' => 'Error logs cleared successfully',
        'error_clearing' => 'Error occurred while clearing logs',
        'last_updated' => 'Last Updated',
        'auto_refresh' => 'Auto Refresh',
        'enable' => 'Enable',
        'disable' => 'Disable'
    ]
];

// Current language
$lang = $_SESSION['lang'];
$t = $translations[$lang];
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

// Handle clear logs request
if (isset($_POST['clear_logs'])) {
    $log_file = __DIR__ . '/logs/error.log';
    if (file_exists($log_file)) {
        if (file_put_contents($log_file, '') !== false) {
            $success_message = $t['logs_cleared'];
        } else {
            $error_message = $t['error_clearing'];
        }
    }
}

// Handle download logs request
if (isset($_GET['download'])) {
    $log_file = __DIR__ . '/logs/error.log';
    if (file_exists($log_file)) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="error_logs_' . date('Y-m-d_H-i-s') . '.log"');
        header('Content-Length: ' . filesize($log_file));
        readfile($log_file);
        exit();
    }
}

// Read error logs
function getErrorLogs() {
    $log_file = __DIR__ . '/logs/error.log';
    $logs = [];
    
    if (file_exists($log_file)) {
        $content = file_get_contents($log_file);
        if ($content) {
            $lines = array_reverse(explode("\n", trim($content)));
            foreach ($lines as $line) {
                if (!empty($line)) {
                    // Parse log line format: [timestamp] error_type: message in file:line
                    if (preg_match('/\[(.*?)\]\s*(.*?):\s*(.*?)\s*in\s*(.*?):(\d+)/', $line, $matches)) {
                        $logs[] = [
                            'timestamp' => $matches[1],
                            'type' => $matches[2],
                            'message' => $matches[3],
                            'file' => basename($matches[4]),
                            'line' => $matches[5],
                            'full_line' => $line
                        ];
                    } else {
                        // If doesn't match pattern, show as is
                        $logs[] = [
                            'timestamp' => date('Y-m-d H:i:s'),
                            'type' => 'Unknown',
                            'message' => $line,
                            'file' => '',
                            'line' => '',
                            'full_line' => $line
                        ];
                    }
                }
            }
        }
    }
    
    return array_slice($logs, 0, 100); // Limit to last 100 errors
}

$error_logs = getErrorLogs();
$log_file = __DIR__ . '/logs/error.log';
$last_modified = file_exists($log_file) ? date('Y-m-d H:i:s', filemtime($log_file)) : 'N/A';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .log-entry {
            border-left: 4px solid #dc3545;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 0 5px 5px 0;
        }
        .log-entry.warning {
            border-left-color: #ffc107;
        }
        .log-entry.notice {
            border-left-color: #17a2b8;
        }
        .log-timestamp {
            font-size: 0.85em;
            color: #6c757d;
        }
        .log-type {
            font-weight: bold;
            text-transform: uppercase;
        }
        .log-file {
            font-family: monospace;
            font-size: 0.9em;
            color: #495057;
        }
        .auto-refresh-indicator {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
        }
        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/MainNavbar.php'; ?>
    
    <div class="container-fluid mt-4">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-exclamation-triangle text-warning"></i> <?php echo $t['error_logs']; ?></h2>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> <?php echo $t['refresh']; ?>
                        </button>
                        <button type="button" class="btn btn-outline-success" id="autoRefreshBtn">
                            <i class="fas fa-play"></i> <span id="autoRefreshText"><?php echo $t['enable']; ?></span> <?php echo $t['auto_refresh']; ?>
                        </button>
                        <a href="?download=1" class="btn btn-outline-info">
                            <i class="fas fa-download"></i> <?php echo $t['download_logs']; ?>
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="fas fa-trash"></i> <?php echo $t['clear_logs']; ?>
                        </button>
                    </div>
                </div>
                
                <!-- Info Panel -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-info-circle"></i> <?php echo $t['last_updated']; ?></h6>
                                <p class="card-text"><?php echo $last_modified; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-list-ol"></i> Total Errors</h6>
                                <p class="card-text"><?php echo count($error_logs); ?> errors</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Error Logs Table -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($error_logs)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> <?php echo $t['no_errors']; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><?php echo $t['timestamp']; ?></th>
                                    <th><?php echo $t['error_type']; ?></th>
                                    <th><?php echo $t['message']; ?></th>
                                    <th><?php echo $t['file']; ?></th>
                                    <th><?php echo $t['line']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($error_logs as $log): ?>
                                    <tr>
                                        <td class="log-timestamp"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                        <td>
                                            <span class="badge bg-danger log-type"><?php echo htmlspecialchars($log['type']); ?></span>
                                        </td>
                                        <td class="text-break"><?php echo htmlspecialchars($log['message']); ?></td>
                                        <td class="log-file"><?php echo htmlspecialchars($log['file']); ?></td>
                                        <td><?php echo htmlspecialchars($log['line']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Auto Refresh Indicator -->
    <div class="auto-refresh-indicator" id="autoRefreshIndicator" style="display: none;">
        <div class="alert alert-info">
            <i class="fas fa-sync-alt fa-spin"></i> Auto refreshing in <span id="countdown">30</span>s
        </div>
    </div>
    
    <!-- Clear Logs Modal -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> <?php echo $t['clear_logs']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $t['confirm_clear']; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="clear_logs" class="btn btn-danger">
                            <i class="fas fa-trash"></i> <?php echo $t['clear_logs']; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let autoRefreshInterval;
        let countdownInterval;
        let isAutoRefreshEnabled = false;
        
        document.getElementById('autoRefreshBtn').addEventListener('click', function() {
            if (isAutoRefreshEnabled) {
                disableAutoRefresh();
            } else {
                enableAutoRefresh();
            }
        });
        
        function enableAutoRefresh() {
            isAutoRefreshEnabled = true;
            document.getElementById('autoRefreshText').textContent = '<?php echo $t['disable']; ?>';
            document.getElementById('autoRefreshBtn').innerHTML = '<i class="fas fa-stop"></i> <span id="autoRefreshText"><?php echo $t['disable']; ?></span> <?php echo $t['auto_refresh']; ?>';
            document.getElementById('autoRefreshIndicator').style.display = 'block';
            
            let countdown = 30;
            document.getElementById('countdown').textContent = countdown;
            
            countdownInterval = setInterval(function() {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    location.reload();
                }
            }, 1000);
            
            autoRefreshInterval = setInterval(function() {
                location.reload();
            }, 30000);
        }
        
        function disableAutoRefresh() {
            isAutoRefreshEnabled = false;
            document.getElementById('autoRefreshText').textContent = '<?php echo $t['enable']; ?>';
            document.getElementById('autoRefreshBtn').innerHTML = '<i class="fas fa-play"></i> <span id="autoRefreshText"><?php echo $t['enable']; ?></span> <?php echo $t['auto_refresh']; ?>';
            document.getElementById('autoRefreshIndicator').style.display = 'none';
            
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }
    </script>
</body>
</html>