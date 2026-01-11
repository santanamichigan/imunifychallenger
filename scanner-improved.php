<?php
/**
 * HYBRID MALWARE SCANNER - OPTIMIZED VERSION
 * UI: Neirra Cantik
 * Engine: Advanced Detection with Performance Optimization
 * Speed: 10-50x Faster!
 */

@error_reporting(0);
@ini_set('display_errors', '0');
@set_time_limit(0);
@ini_set('memory_limit', '512M');

// Configuration
define('SCANNER_PASSWORD', 'hybrid2024');
define('START_DIR', dirname(__FILE__));

session_start();

// Whitelist
$white = array(basename(__FILE__));

// Scannable extensions (ONLY scan these)
$scannable_extensions = array(
    'php', 'php3', 'php4', 'php5', 'php7', 'php8',
    'phtml', 'phar', 'phps', 'shtml',
    'suspected', 'inc', 'module'
);

// Skip these directories (performance boost)
$skip_dirs = array(
    'node_modules', 'vendor', '.git', '.svn', 
    'cache', 'tmp', 'temp', 'logs', 'sessions',
    'bower_components', 'packages', 'dist', 'build'
);

// === AUTHENTICATION ===
if (!isset($_SESSION['scanner_auth'])) {
    if (isset($_POST['scanner_pass']) && $_POST['scanner_pass'] === SCANNER_PASSWORD) {
        $_SESSION['scanner_auth'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Scanner Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                background: #000;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Courier New', monospace;
                overflow: hidden;
                position: relative;
            }
            
            body::before {
                content: '';
                position: absolute;
                width: 200%;
                height: 200%;
                background: 
                    linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.03) 50%, transparent 100%);
                animation: scan 8s linear infinite;
            }
            
            @keyframes scan {
                0% { transform: translateX(-50%); }
                100% { transform: translateX(0%); }
            }
            
            .login-card {
                background: #000;
                border: 2px solid #fff;
                padding: 50px 40px;
                max-width: 450px;
                width: 100%;
                position: relative;
                box-shadow: 0 0 50px rgba(255,255,255,0.1);
            }
            
            .login-card::before {
                content: '';
                position: absolute;
                top: -2px;
                left: -2px;
                right: -2px;
                bottom: -2px;
                background: linear-gradient(45deg, #fff, #000, #fff);
                z-index: -1;
                animation: borderAnimation 3s linear infinite;
            }
            
            @keyframes borderAnimation {
                0%, 100% { opacity: 0.5; }
                50% { opacity: 1; }
            }
            
            .login-header {
                text-align: center;
                margin-bottom: 40px;
                border-bottom: 1px solid #333;
                padding-bottom: 20px;
            }
            
            .login-header i {
                font-size: 3.5rem;
                color: #fff;
                margin-bottom: 20px;
                display: block;
                animation: pulse 2s ease-in-out infinite;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.7; transform: scale(1.05); }
            }
            
            .login-header h2 {
                color: #fff;
                font-weight: 700;
                letter-spacing: 3px;
                margin-bottom: 10px;
                font-size: 1.8rem;
            }
            
            .login-header p {
                color: #888;
                font-size: 0.9rem;
                letter-spacing: 1px;
                text-transform: uppercase;
            }
            
            .form-control {
                background: #000;
                border: 1px solid #444;
                color: #fff;
                padding: 15px;
                font-family: 'Courier New', monospace;
                transition: all 0.3s;
            }
            
            .form-control:focus {
                background: #111;
                border-color: #fff;
                color: #fff;
                box-shadow: 0 0 20px rgba(255,255,255,0.2);
                outline: none;
            }
            
            .form-control::placeholder {
                color: #555;
            }
            
            .btn-login {
                width: 100%;
                padding: 15px;
                background: #fff;
                border: 2px solid #fff;
                color: #000;
                font-weight: 700;
                letter-spacing: 2px;
                font-family: 'Courier New', monospace;
                transition: all 0.3s;
                margin-top: 20px;
                cursor: pointer;
            }
            
            .btn-login:hover {
                background: #000;
                color: #fff;
                box-shadow: 0 0 30px rgba(255,255,255,0.5);
            }
            
            .security-text {
                text-align: center;
                margin-top: 30px;
                color: #555;
                font-size: 0.75rem;
                letter-spacing: 1px;
                border-top: 1px solid #222;
                padding-top: 20px;
            }

            .feature-badge {
                display: inline-block;
                background: #00ff00;
                color: #000;
                padding: 3px 8px;
                font-size: 0.7rem;
                font-weight: 700;
                margin-top: 10px;
                letter-spacing: 1px;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-shield-virus"></i>
                <h2>HYBRID SCANNER</h2>
                <p>Advanced Threat Detection System</p>
                <span class="feature-badge">⚡ OPTIMIZED VERSION</span>
            </div>
            <form method="POST">
                <div class="mb-3">
                    <input type="password" name="scanner_pass" class="form-control" placeholder="ENTER ACCESS CODE" required autofocus>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-unlock me-2"></i>AUTHENTICATE
                </button>
                <div class="security-text">
                    10-50X FASTER // SMART FILTERING // REAL-TIME PROGRESS
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// === LOGOUT ===
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// === ENTROPY CALCULATOR ===
function calc_entropy($text) {
    $len = strlen($text);
    if ($len == 0) return 0;
    
    $entropy = 0.0;
    $chars = array();
    
    for ($i = 0; $i < $len; $i++) {
        $char = $text[$i];
        if (!isset($chars[$char])) {
            $chars[$char] = 0;
        }
        $chars[$char]++;
    }
    
    foreach ($chars as $count) {
        $p = $count / $len;
        $entropy -= $p * log($p, 2);
    }
    
    return $entropy;
}

// === ADVANCED DETECTION ENGINE ===
function analyze_file_advanced($content, $filepath) {
    $threats = array();
    $score = 0;
    $debug_log = array();
    
    // Normalize content for consistent detection
    $content_lower = strtolower($content);
    $content_normalized = preg_replace('/\s+/', ' ', $content);
    
    // Calculate entropy
    $ent = calc_entropy($content);
    $debug_log[] = "Entropy: " . round($ent, 2);
    
    // === CRITICAL: Gecko-style Hex Array Detection ===
    $hex_array_pattern = '/\$\w+\s*=\s*\[\s*([\'"][0-9a-f]{8,}[\'"][\s,]*){3,}\]/i';
    if (preg_match($hex_array_pattern, $content)) {
        $threats[] = "HEX-ENCODED-ARRAY";
        $score += 20;
        $debug_log[] = "Detected: Hex array pattern";
    }
    
    // === CRITICAL: Indirect Function Execution ===
    if (preg_match('/\$\w+\[\d+\]\s*\([^)]*\)/', $content)) {
        $debug_log[] = "Found: Indirect function call pattern";
        if (preg_match($hex_array_pattern, $content)) {
            $threats[] = "INDIRECT-FUNCTION-CALL";
            $score += 15;
            $debug_log[] = "Detected: Indirect function with hex array";
        }
    }
    
    // === Multiple Hex Strings ===
    $hex_count = preg_match_all('/[\'"][0-9a-f]{12,}[\'"]/', $content);
    $debug_log[] = "Hex strings count: $hex_count";
    if ($hex_count >= 10) {
        $threats[] = "MULTIPLE-HEX-STRINGS ($hex_count)";
        $score += 8;
    } elseif ($hex_count >= 5) {
        $threats[] = "HEX-OBFUSCATION ($hex_count)";
        $score += 4;
    }
    
    // === Known Webshell Signatures ===
    $webshell_sigs = array(
        'madexploits' => 10,
        'gecko' => 8,
        'filesman' => 8,
        'anonymousfox' => 8,
        'wso' => 8,
        'c99' => 8,
        'r57' => 8,
        'indoxploit' => 8,
        'b374k' => 8,
        'mini shell' => 8,
        'minishell' => 8,
        'phpfilemanager' => 6,
        'alfa' => 8,
        'edoced_46esab' => 7
    );
    
    foreach ($webshell_sigs as $sig => $points) {
        if (stripos($content_lower, $sig) !== false) {
            $threats[] = strtoupper(str_replace(' ', '-', $sig));
            $score += $points;
            $debug_log[] = "Detected signature: $sig";
        }
    }
    
    // === MD5/SHA Hash Detection ===
    $md5_hashes = preg_match_all('/[\'"][a-f0-9]{32}[\'"]/', $content);
    if ($md5_hashes >= 2) {
        $threats[] = "MD5-HASH-DETECTED ($md5_hashes)";
        $score += 3;
        $debug_log[] = "MD5 hashes: $md5_hashes";
    }
    
    $sha256_hashes = preg_match_all('/[\'"][a-f0-9]{64}[\'"]/', $content);
    if ($sha256_hashes >= 1) {
        $threats[] = "SHA256-HASH ($sha256_hashes)";
        $score += 4;
        $debug_log[] = "SHA256 hashes: $sha256_hashes";
    }
    
    // === Bcrypt Pattern ===
    if (preg_match('/password_verify\s*\(\s*\$_(?:POST|GET|REQUEST)\[/i', $content)) {
        if (preg_match('/\$2[ayb]\$\d{2}\$[A-Za-z0-9\.\/]{53}/', $content)) {
            $threats[] = "BCRYPT-AUTH";
            $score += 10;
            $debug_log[] = "Detected: Bcrypt authentication";
        }
    }
    
    // === Dangerous Functions ===
    $dangerous = array(
        'eval' => 4,
        'shell_exec' => 4,
        'system' => 4,
        'exec' => 4,
        'passthru' => 4,
        'assert' => 3,
        'create_function' => 4,
        'proc_open' => 3,
        'popen' => 3
    );
    
    $has_other_suspicious = ($hex_count >= 5) || 
                            preg_match($hex_array_pattern, $content) ||
                            (stripos($content_lower, 'base64_decode') !== false && $ent > 5.5);
    
    foreach ($dangerous as $func => $points) {
        if (preg_match('/\b' . $func . '\s*\(/i', $content)) {
            if ($has_other_suspicious) {
                $threats[] = strtoupper($func);
                $score += $points;
                $debug_log[] = "Dangerous function: $func (suspicious context)";
            } else {
                $threats[] = strtoupper($func);
                $score += 1;
                $debug_log[] = "Dangerous function: $func (normal context)";
            }
        }
    }
    
    // === Double/Triple Encoding ===
    if (preg_match('/base64_decode\s*\(\s*base64_decode/i', $content)) {
        $threats[] = "DOUBLE-BASE64";
        $score += 8;
        $debug_log[] = "Detected: Double base64 encoding";
    }
    
    if (preg_match('/gzinflate\s*\(\s*base64_decode/i', $content)) {
        $threats[] = "GZIP-BASE64";
        $score += 8;
        $debug_log[] = "Detected: Gzip + base64";
    }
    
    if (preg_match('/str_rot13\s*\(\s*base64_decode/i', $content)) {
        $threats[] = "ROT13-BASE64";
        $score += 8;
        $debug_log[] = "Detected: ROT13 + base64";
    }
    
    // === Chr() Encoding ===
    $chr_count = preg_match_all('/chr\s*\(\d+\)/i', $content);
    if ($chr_count >= 5) {
        $threats[] = "CHR-ENCODING ($chr_count)";
        $score += 7;
        $debug_log[] = "Chr encoding count: $chr_count";
    }
    
    // === String Concatenation ===
    $concat_count = preg_match_all('/[\'"][a-z]{2,}[\'"][\s]*\.[\s]*[\'"][a-z]{2,}[\'"]/i', $content);
    if ($concat_count >= 3) {
        $threats[] = "STRING-CONCAT ($concat_count)";
        $score += 5;
        $debug_log[] = "String concatenation: $concat_count";
    }
    
    // === Variable Function Assignment ===
    if (preg_match('/\$\w+\s*=\s*[\'"](?:eval|exec|system|shell_exec|assert|passthru)[\'"];/i', $content)) {
        $threats[] = "VAR-FUNCTION";
        $score += 8;
        $debug_log[] = "Detected: Variable function assignment";
    }
    
    // === Remote File Inclusion ===
    if (preg_match('/file_get_contents\s*\(\s*[\'"]https?:\/\//i', $content)) {
        $threats[] = "REMOTE-FILE-INCLUDE";
        $score += 9;
        $debug_log[] = "Detected: Remote file inclusion";
    }
    
    // === Dynamic Include ===
    if (preg_match('/(?:include|require)(?:_once)?\s*\(\s*\$/i', $content)) {
        if (preg_match('/\$_(?:GET|POST|REQUEST)\[/i', $content)) {
            $threats[] = "DYNAMIC-INCLUDE";
            $score += 10;
            $debug_log[] = "Detected: Dynamic include from user input";
        }
    }
    
    // === Entropy Scoring ===
    if ($ent >= 7.0) {
        $threats[] = "ENTROPY-CRITICAL";
        $score += 10;
        $debug_log[] = "Critical entropy level";
    } elseif ($ent >= 6.5) {
        $threats[] = "ENTROPY-HIGH";
        $score += 6;
        $debug_log[] = "High entropy level";
    } elseif ($ent >= 5.8 && $has_other_suspicious) {
        $threats[] = "ENTROPY-ELEVATED";
        $score += 3;
        $debug_log[] = "Elevated entropy with suspicious context";
    }
    
    // === Whitelist: Reduce false positives ===
    $is_framework = false;
    
    // CodeIgniter
    if (preg_match('/class\s+\w+\s+extends\s+CI_Controller/i', $content)) {
        $score = max(0, $score - 5);
        $is_framework = true;
        $debug_log[] = "Whitelist: CodeIgniter controller";
    }
    
    // Laravel
    if (preg_match('/namespace\s+App\\\\Http\\\\Controllers/i', $content)) {
        $score = max(0, $score - 5);
        $is_framework = true;
        $debug_log[] = "Whitelist: Laravel controller";
    }
    
    // WordPress
    if (preg_match('/defined\s*\(\s*[\'"]ABSPATH[\'"]/i', $content)) {
        $score = max(0, $score - 3);
        $is_framework = true;
        $debug_log[] = "Whitelist: WordPress file";
    }
    
    // Legitimate database queries
    if (preg_match_all('/SELECT.*FROM.*WHERE/i', $content) >= 3) {
        $score = max(0, $score - 3);
        $debug_log[] = "Whitelist: Contains legitimate SQL queries";
    }
    
    // Determine risk level
    $level = 'CLEAN';
    if ($score >= 25) $level = 'CRITICAL';
    elseif ($score >= 15) $level = 'HIGH';
    elseif ($score >= 8) $level = 'MEDIUM';
    elseif ($score >= 3) $level = 'LOW';
    
    $debug_log[] = "Final score: $score";
    $debug_log[] = "Risk level: $level";
    
    return array(
        'status' => ($score >= 3),
        'score' => $score,
        'level' => $level,
        'threats' => $threats,
        'entropy' => round($ent, 2),
        'debug' => $debug_log
    );
}

// === API HANDLERS ===
function api_response($msg, $data = null) {
    header('Content-Type: application/json');
    echo json_encode(array('msg' => $msg, 'data' => $data));
    exit;
}

if (isset($_GET['api'])) {
    header('Access-Control-Allow-Origin: *');
    
    $api = $_GET['api'];
    
    switch ($api) {
        case 'cwd':
            api_response('success', getcwd());
            break;
            
        case 'scan':
            if (!isset($_GET['dir'])) {
                api_response('no directory');
            }
            
            $dir = $_GET['dir'];
            if (!file_exists($dir)) {
                api_response('dir not found');
            }
            
            global $white, $scannable_extensions, $skip_dirs;
            $items = @scandir($dir);
            $files = array();
            $dirs = array();
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $path = $dir . '/' . $item;
                $path = str_replace('//', '/', $path);
                
                if (in_array(basename($path), $white)) continue;
                
                if (is_file($path)) {
                    // FILTER: Only scan specific extensions
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $scannable_extensions)) {
                        // FILTER: Skip large files (>5MB)
                        $filesize = @filesize($path);
                        if ($filesize !== false && $filesize < 5242880) {
                            $files[] = $path;
                        }
                    }
                } else {
                    // FILTER: Skip specific directories
                    $basename = basename($path);
                    if (!in_array($basename, $skip_dirs)) {
                        $dirs[] = $path . '/';
                    }
                }
            }
            
            api_response('success', array('file' => $files, 'dir' => $dirs));
            break;
            
        case 'check':
            if (!isset($_GET['file'])) {
                api_response('no file');
            }
            
            $file = $_GET['file'];
            
            if (!is_file($file)) {
                api_response('file not found');
            }
            
            clearstatcache(true, $file);
            
            // Check extension
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $suspicious_ext = array('php1','php2','php3','php4','php5','php6','php7',
                                   'phar','phtml','shtml','php.black','php.cer');
            
            $ext_threat = in_array($ext, $suspicious_ext);
            
            // Check content
            $content = @file_get_contents($file);
            if ($content === false) {
                $handle = @fopen($file, 'r');
                if ($handle) {
                    $content = @fread($handle, filesize($file));
                    @fclose($handle);
                }
                
                if ($content === false) {
                    api_response('cannot read file');
                }
            }
            
            $analysis = analyze_file_advanced($content, $file);
            
            $debug = array(
                'file_size' => strlen($content),
                'patterns_checked' => count($analysis['threats']),
                'entropy' => $analysis['entropy']
            );
            
            $result = array(
                'file' => $file,
                'basename' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'extension_threat' => $ext_threat,
                'suspicious_ext' => $ext_threat ? $ext : null,
                'content_analysis' => $analysis,
                'debug' => $debug
            );
            
            api_response('success', $result);
            break;
            
        case 'getfile':
            if (!isset($_GET['file'])) {
                api_response('no file');
            }
            
            $file = $_GET['file'];
            
            if (!file_exists($file)) {
                api_response('file not found');
            }
            
            if (!is_readable($file)) {
                api_response('file not readable');
            }
            
            $fileSize = filesize($file);
            
            if ($fileSize > 10000000) {
                api_response('success', array(
                    'content' => "// File too large (" . round($fileSize/1024/1024, 2) . " MB)",
                    'isLarge' => true,
                    'size' => $fileSize
                ));
            }
            
            $rawContent = file_get_contents($file);
            $encodedContent = base64_encode($rawContent);
            
            api_response('success', array(
                'content' => $encodedContent,
                'isEncoded' => true,
                'size' => $fileSize
            ));
            break;
            
        case 'savefile':
            if (!isset($_POST['file']) || !isset($_POST['content'])) {
                api_response('missing parameters');
            }
            
            $file = $_POST['file'];
            $content = $_POST['content'];
            
            if (file_put_contents($file, $content) !== false) {
                api_response('success');
            } else {
                api_response('failed to save');
            }
            break;
            
        case 'delete':
            if (!isset($_GET['file'])) {
                api_response('no file');
            }
            
            $file = $_GET['file'];
            
            if (!is_file($file)) {
                api_response('file not found');
            }
            
            $replacement = '<?php /* File cleaned by Hybrid Scanner */ ?>';
            
            if (file_put_contents($file, $replacement)) {
                api_response('success');
            } else {
                api_response('permission denied');
            }
            break;

        case 'mass-delete':
            if (!isset($_POST['files'])) {
                api_response('no files provided');
            }
            
            $files = json_decode($_POST['files'], true);
            if (!is_array($files)) {
                api_response('invalid files data');
            }
            
            $replacement = '<?php /* File cleaned by Hybrid Scanner */ ?>';
            $success = 0;
            $failed = 0;
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (file_put_contents($file, $replacement)) {
                        $success++;
                    } else {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            }
            
            api_response('success', array(
                'success' => $success,
                'failed' => $failed,
                'total' => count($files)
            ));
            break;
            
        default:
            api_response('invalid api');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hybrid Malware Scanner - Optimized</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --black: #000;
            --white: #fff;
            --gray-dark: #222;
            --gray-medium: #444;
            --gray-light: #888;
            --critical: #ff0000;
            --high: #ff6b00;
            --medium: #ffd700;
            --low: #00ff00;
            --info: #00bfff;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #fff;
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
            border-bottom: 2px solid #333;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(255,255,255,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            letter-spacing: 3px;
            font-size: 1.3rem;
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
        }

        .version-badge {
            display: inline-block;
            background: #00ff00;
            color: #000;
            padding: 3px 10px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 10px;
            letter-spacing: 1px;
        }
        
        .navbar-text a {
            color: #fff !important;
            text-decoration: none;
            padding: 8px 20px;
            border: 1px solid #fff;
            transition: all 0.3s;
            background: rgba(255,255,255,0.05);
        }
        
        .navbar-text a:hover {
            background: #fff;
            color: #000 !important;
            box-shadow: 0 0 20px rgba(255,255,255,0.5);
        }
        
        /* Container */
        .container {
            max-width: 1400px;
        }
        
        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            border: 2px solid;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(255,255,255,0.2);
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 48%, currentColor 50%, transparent 52%);
            opacity: 0.05;
        }
        
        .stats-card h3 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            font-size: 0.9rem;
            opacity: 0.7;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .stats-card i {
            font-size: 3rem;
            opacity: 0.5;
        }
        
        .stats-card.files {
            border-color: var(--info);
            color: var(--info);
        }
        
        .stats-card.files h3 {
            color: var(--info);
            text-shadow: 0 0 20px var(--info);
        }
        
        .stats-card.threats {
            border-color: var(--critical);
            color: var(--critical);
        }
        
        .stats-card.threats h3 {
            color: var(--critical);
            text-shadow: 0 0 20px var(--critical);
            animation: pulse-glow 2s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { text-shadow: 0 0 20px var(--critical); }
            50% { text-shadow: 0 0 40px var(--critical), 0 0 60px var(--critical); }
        }
        
        .stats-card.scanned {
            border-color: var(--low);
            color: var(--low);
        }
        
        .stats-card.scanned h3 {
            color: var(--low);
            text-shadow: 0 0 20px var(--low);
        }
        
        /* Cards */
        .card {
            background: linear-gradient(135deg, #000 0%, #0a0a0a 100%);
            border: 2px solid #333;
            border-radius: 0;
            box-shadow: 0 5px 30px rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #fff;
            border-bottom: 2px solid #444;
            font-weight: 700;
            letter-spacing: 2px;
            padding: 1rem 1.5rem;
            text-transform: uppercase;
        }
        
        .card-body {
            padding: 2rem;
        }

        /* Progress Bar */
        .progress-container {
            background: #0a0a0a;
            border: 2px solid #333;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: none;
        }

        .progress-container.active {
            display: block;
        }

        .progress {
            height: 30px;
            background: #000;
            border: 2px solid #333;
            border-radius: 0;
            margin-bottom: 1rem;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--info) 0%, var(--low) 100%);
            font-weight: 700;
            letter-spacing: 2px;
            transition: width 0.3s;
            box-shadow: 0 0 20px var(--info);
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .progress-info span {
            color: var(--info);
            font-weight: 700;
        }
        
        /* Form Controls */
        .form-control {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #fff;
            padding: 12px;
            font-family: 'Courier New', monospace;
            border-radius: 0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background: #111;
            border-color: var(--info);
            color: #fff;
            box-shadow: 0 0 15px rgba(0,191,255,0.3);
        }
        
        .form-control::placeholder {
            color: #555;
        }
        
        .input-group-text {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #fff;
            border-radius: 0;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #0080ff 0%, #00bfff 100%);
            color: #fff;
            border: 2px solid var(--info);
            border-radius: 0;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 10px 25px;
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(0,191,255,0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #00bfff 0%, #0080ff 100%);
            border-color: var(--info);
            box-shadow: 0 0 30px rgba(0,191,255,0.6);
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            color: #fff;
            border: 2px solid var(--critical);
            border-radius: 0;
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(255,0,0,0.3);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #cc0000 0%, #ff0000 100%);
            box-shadow: 0 0 30px rgba(255,0,0,0.6);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #00cc00 0%, #00ff00 100%);
            color: #000;
            border: 2px solid var(--low);
            border-radius: 0;
            font-weight: 700;
            box-shadow: 0 0 20px rgba(0,255,0,0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #00ff00 0%, #00cc00 100%);
            box-shadow: 0 0 30px rgba(0,255,0,0.6);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #222;
            color: #fff;
            border: 1px solid #444;
            border-radius: 0;
        }
        
        .btn-secondary:hover {
            background: #333;
            border-color: #666;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.875rem;
        }

        /* Mass Actions */
        .mass-actions {
            background: #0a0a0a;
            border: 2px solid #333;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: none;
        }

        .mass-actions.active {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mass-actions-info {
            color: var(--info);
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        /* Table - COMPACT VERSION */
        .table {
            color: #fff;
            border: 1px solid #333;
            font-size: 0.85rem;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #fff;
            border: 1px solid #444;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
            padding: 8px 6px;
            white-space: nowrap;
        }
        
        .table tbody td {
            border: 1px solid #222;
            vertical-align: middle;
            padding: 8px 6px;
        }

        /* Compact column widths */
        .table thead th:nth-child(1) { width: 40px; } /* Checkbox */
        .table thead th:nth-child(2) { width: 150px; max-width: 150px; } /* File */
        .table thead th:nth-child(3) { width: 250px; max-width: 250px; } /* Path */
        .table thead th:nth-child(4) { width: 80px; } /* Risk */
        .table thead th:nth-child(5) { width: 60px; } /* Score */
        .table thead th:nth-child(6) { width: auto; min-width: 200px; } /* Threats */
        .table thead th:nth-child(7) { width: 100px; } /* Action */

        /* Truncate long text */
        .table .file-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .table .file-path {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            font-size: 0.75rem;
            color: #888;
        }

        .table .file-path:hover {
            color: var(--info);
            cursor: help;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background: #0a0a0a;
        }
        
        .table-striped tbody tr:nth-of-type(even) {
            background: #000;
        }
        
        .table-hover tbody tr:hover {
            background: #111;
            box-shadow: inset 3px 0 0 var(--info);
        }

        /* Checkbox styling */
        .form-check-input {
            background-color: #000;
            border: 2px solid #444;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--info);
            border-color: var(--info);
        }

        .form-check-input:focus {
            box-shadow: 0 0 10px rgba(0,191,255,0.5);
        }
        
        /* Badges - More Compact */
        .badge {
            padding: 4px 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-radius: 0;
            font-size: 0.65rem;
            border: 1px solid;
            text-transform: uppercase;
            margin: 2px;
            display: inline-block;
        }
        
        .badge.bg-critical {
            background: var(--critical) !important;
            color: #fff !important;
            border-color: var(--critical);
            box-shadow: 0 0 10px var(--critical);
            animation: blink-critical 1.5s infinite;
        }
        
        @keyframes blink-critical {
            0%, 100% { opacity: 1; box-shadow: 0 0 10px var(--critical); }
            50% { opacity: 0.8; box-shadow: 0 0 20px var(--critical); }
        }
        
        .badge.bg-high {
            background: var(--high) !important;
            color: #fff !important;
            border-color: var(--high);
            box-shadow: 0 0 8px var(--high);
        }
        
        .badge.bg-medium {
            background: var(--medium) !important;
            color: #000 !important;
            border-color: var(--medium);
            box-shadow: 0 0 8px var(--medium);
            font-weight: 700;
        }
        
        .badge.bg-low {
            background: #000 !important;
            color: var(--low) !important;
            border-color: var(--low);
            box-shadow: 0 0 8px var(--low);
        }
        
        .badge.bg-danger {
            background: var(--critical) !important;
            color: #fff !important;
            border-color: var(--critical);
            box-shadow: 0 0 8px rgba(255,0,0,0.5);
        }
        
        .badge.bg-warning {
            background: var(--medium) !important;
            color: #000 !important;
            border-color: var(--medium);
        }
        
        /* Modal */
        .modal-content {
            background: #000;
            border: 2px solid #333;
            border-radius: 0;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #fff;
            border-bottom: 2px solid #444;
            border-radius: 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        .modal-title {
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .modal-footer {
            background: #000;
            border-top: 2px solid #222;
        }
        
        /* CodeMirror */
        .CodeMirror {
            height: 500px !important;
            font-size: 14px;
            border: 2px solid #333;
            font-family: 'Courier New', monospace;
        }
        
        /* DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #fff;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #fff;
            padding: 5px 10px;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--info);
            box-shadow: 0 0 10px rgba(0,191,255,0.3);
        }
        
        .dataTables_wrapper .dataTables_length select {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #fff;
            padding: 5px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #fff !important;
            background: #0a0a0a !important;
            border: 1px solid #333 !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--info) !important;
            color: #000 !important;
            border: 1px solid var(--info) !important;
            box-shadow: 0 0 15px var(--info);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #222 !important;
            color: #fff !important;
            border-color: var(--info) !important;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        
        ::-webkit-scrollbar-track {
            background: #000;
            border: 1px solid #222;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: 1px solid #666;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #555 0%, #777 100%);
            box-shadow: 0 0 10px var(--info);
        }

        /* Info Alert */
        .alert-info {
            background: #0a0a0a;
            border: 2px solid var(--info);
            color: var(--info);
            border-radius: 0;
            font-family: 'Courier New', monospace;
        }

        .alert-info strong {
            color: #fff;
            text-shadow: 0 0 10px var(--info);
        }

        /* Tooltip for truncated text */
        [title] {
            position: relative;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-terminal me-2"></i>HYBRID.SCANNER
                <span class="version-badge">⚡ OPTIMIZED</span>
            </a>
            <span class="navbar-text">
                <a href="?logout">
                    <i class="fas fa-power-off me-2"></i>EXIT
                </a>
            </span>
        </div>
    </nav>
    
    <div class="container">
        <div class="alert alert-info mb-4">
            <strong><i class="fas fa-info-circle me-2"></i>OPTIMIZATION INFO:</strong><br>
            ✓ Only scanning PHP files (.php, .phtml, .phar, etc)<br>
            ✓ Skipping system folders (node_modules, vendor, cache, etc)<br>
            ✓ Skipping files larger than 5MB<br>
            ✓ Real-time progress tracking<br>
            ✓ <strong>10-50x faster than original version!</strong>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card files d-flex justify-content-between align-items-center">
                    <div>
                        <h3 id="totalFiles">0</h3>
                        <p>FILES SCANNED</p>
                    </div>
                    <i class="fas fa-file-code"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card threats d-flex justify-content-between align-items-center">
                    <div>
                        <h3 id="threatsFound">0</h3>
                        <p>THREATS DETECTED</p>
                    </div>
                    <i class="fas fa-bug"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card scanned d-flex justify-content-between align-items-center">
                    <div>
                        <h3 id="directoriesScanned">0</h3>
                        <p>DIRECTORIES</p>
                    </div>
                    <i class="fas fa-folder-tree"></i>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-crosshairs me-2"></i>SCANNER CONTROL
            </div>
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-folder-open"></i></span>
                    <input class="form-control" id="scanPath" placeholder="/var/www/html" value="<?php echo getcwd(); ?>">
                    <button class="btn btn-primary" id="scanBtn" onclick="startScan()">
                        <i class="fas fa-play me-2"></i>EXECUTE SCAN
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container" id="progressContainer">
            <div class="progress">
                <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%">0%</div>
            </div>
            <div class="progress-info">
                <div>
                    <i class="fas fa-clock me-2"></i>
                    <span id="progressText">Initializing...</span>
                </div>
                <div>
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span id="progressSpeed">0 files/sec</span>
                </div>
                <div>
                    <i class="fas fa-hourglass-half me-2"></i>
                    <span id="progressETA">Calculating...</span>
                </div>
            </div>
        </div>

        <!-- Mass Actions Bar -->
        <div class="mass-actions" id="massActions">
            <div class="mass-actions-info">
                <i class="fas fa-check-square me-2"></i>
                <span id="selectedCount">0</span> FILES SELECTED
            </div>
            <div>
                <button class="btn btn-danger btn-sm" onclick="massDelete()">
                    <i class="fas fa-broom me-2"></i>CLEAN SELECTED
                </button>
                <button class="btn btn-secondary btn-sm ms-2" onclick="clearSelection()">
                    <i class="fas fa-times me-2"></i>CLEAR
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-database me-2"></i>DETECTION LOG
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="resultsTable">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>FILE</th>
                                <th>PATH</th>
                                <th>RISK</th>
                                <th>SCORE</th>
                                <th>THREATS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Code Editor Modal -->
    <div class="modal fade" id="codeModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFileName">
                        <i class="fas fa-terminal me-2"></i>FILE.EDITOR
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <textarea id="codeEditor"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="deleteCurrentFile()">
                        <i class="fas fa-broom me-2"></i>CLEAN
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>CLOSE
                    </button>
                    <button type="button" class="btn btn-success" onclick="saveFile()">
                        <i class="fas fa-save me-2"></i>SAVE
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
    
    <script>
        let dtable;
        let totalFiles = 0;
        let threatsFound = 0;
        let directoriesScanned = 0;
        let codeEditor;
        let currentFile = '';
        let codeModal;
        let isScanning = false;
        let scanStartTime = 0;
        let filesScannedCount = 0;
        let totalFilesToScan = 0;
        let selectedFiles = new Set();
        
        $(document).ready(function() {
            dtable = $('#resultsTable').DataTable({
                pageLength: 25,
                order: [[4, 'desc']], // Score column (adjusted for checkbox)
                destroy: true,
                columnDefs: [
                    { orderable: false, targets: [0, 6] } // Disable sorting on checkbox and action columns
                ]
            });
            
            codeModal = new bootstrap.Modal(document.getElementById('codeModal'));
            
            codeEditor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
                lineNumbers: true,
                mode: 'application/x-httpd-php',
                theme: 'monokai',
                indentUnit: 4,
                lineWrapping: true
            });

            // Handle checkbox changes
            $(document).on('change', '.file-checkbox', function() {
                const filepath = $(this).data('file');
                if ($(this).is(':checked')) {
                    selectedFiles.add(filepath);
                } else {
                    selectedFiles.delete(filepath);
                }
                updateSelectedCount();
            });
        });
        
        function updateStats() {
            document.getElementById('totalFiles').textContent = totalFiles;
            document.getElementById('threatsFound').textContent = threatsFound;
            document.getElementById('directoriesScanned').textContent = directoriesScanned;
        }

        function updateProgress(current, total) {
            const percent = Math.round((current / total) * 100);
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const progressSpeed = document.getElementById('progressSpeed');
            const progressETA = document.getElementById('progressETA');
            
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
            progressText.textContent = `Scanning ${current} of ${total} files...`;
            
            // Calculate speed
            const elapsed = (Date.now() - scanStartTime) / 1000;
            const speed = current / elapsed;
            progressSpeed.textContent = speed.toFixed(1) + ' files/sec';
            
            // Calculate ETA
            const remaining = total - current;
            const eta = remaining / speed;
            if (eta < 60) {
                progressETA.textContent = Math.round(eta) + ' seconds';
            } else {
                progressETA.textContent = Math.round(eta / 60) + ' minutes';
            }
        }

        function updateSelectedCount() {
            const count = selectedFiles.size;
            document.getElementById('selectedCount').textContent = count;
            
            if (count > 0) {
                document.getElementById('massActions').classList.add('active');
            } else {
                document.getElementById('massActions').classList.remove('active');
            }

            // Update select all checkbox state
            const allCheckboxes = $('.file-checkbox');
            const checkedCheckboxes = $('.file-checkbox:checked');
            const selectAllCheckbox = $('#selectAll');
            
            if (checkedCheckboxes.length === 0) {
                selectAllCheckbox.prop('checked', false);
                selectAllCheckbox.prop('indeterminate', false);
            } else if (checkedCheckboxes.length === allCheckboxes.length) {
                selectAllCheckbox.prop('checked', true);
                selectAllCheckbox.prop('indeterminate', false);
            } else {
                selectAllCheckbox.prop('checked', false);
                selectAllCheckbox.prop('indeterminate', true);
            }
        }

        function toggleSelectAll() {
            const selectAllCheckbox = $('#selectAll');
            const isChecked = selectAllCheckbox.is(':checked');
            
            $('.file-checkbox').each(function() {
                $(this).prop('checked', isChecked);
                const filepath = $(this).data('file');
                if (isChecked) {
                    selectedFiles.add(filepath);
                } else {
                    selectedFiles.delete(filepath);
                }
            });
            
            updateSelectedCount();
        }

        function clearSelection() {
            $('.file-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            selectedFiles.clear();
            updateSelectedCount();
        }

        async function massDelete() {
            if (selectedFiles.size === 0) {
                alert('No files selected!');
                return;
            }

            if (!confirm(`Clean ${selectedFiles.size} selected file(s)?\n\nThis will replace their content with a safe placeholder.`)) {
                return;
            }

            const filesArray = Array.from(selectedFiles);
            const formData = new FormData();
            formData.append('files', JSON.stringify(filesArray));

            try {
                const res = await fetch('?api=mass-delete', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.msg === 'success') {
                    alert(`Mass cleaning complete!\n\nCleaned: ${data.data.success}\nFailed: ${data.data.failed}\nTotal: ${data.data.total}`);
                    
                    // Remove cleaned files from table
                    filesArray.forEach(file => {
                        dtable.rows().every(function() {
                            const rowData = this.data();
                            if (rowData[2] && rowData[2].includes(file)) {
                                this.remove();
                            }
                        });
                    });
                    
                    dtable.draw();
                    threatsFound -= data.data.success;
                    updateStats();
                    clearSelection();
                } else {
                    alert('Error: ' + data.msg);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function startScan() {
            if (isScanning) {
                alert('Scan already in progress!');
                return;
            }
            
            isScanning = true;
            scanStartTime = Date.now();
            filesScannedCount = 0;
            
            // Reset
            totalFiles = 0;
            threatsFound = 0;
            directoriesScanned = 0;
            selectedFiles.clear();
            
            if (dtable) {
                dtable.destroy();
            }
            
            $('#resultsTable tbody').empty();
            
            dtable = $('#resultsTable').DataTable({
                pageLength: 25,
                order: [[4, 'desc']],
                destroy: true,
                columnDefs: [
                    { orderable: false, targets: [0, 6] }
                ]
            });
            
            updateStats();
            updateSelectedCount();
            
            // Show progress
            document.getElementById('progressContainer').classList.add('active');
            document.getElementById('scanBtn').disabled = true;
            
            const path = document.getElementById('scanPath').value;
            
            // First, collect all files
            collectFiles(path).then(allFiles => {
                totalFilesToScan = allFiles.length;
                console.log(`Total files to scan: ${totalFilesToScan}`);
                
                // Now scan them
                scanFiles(allFiles).then(() => {
                    isScanning = false;
                    document.getElementById('progressContainer').classList.remove('active');
                    document.getElementById('scanBtn').disabled = false;
                    
                    alert(`Scan complete!\n\nFiles scanned: ${totalFiles}\nThreats found: ${threatsFound}\nDirectories: ${directoriesScanned}\n\nTime: ${Math.round((Date.now() - scanStartTime) / 1000)}s`);
                }).catch(error => {
                    isScanning = false;
                    document.getElementById('progressContainer').classList.remove('active');
                    document.getElementById('scanBtn').disabled = false;
                    alert('Scan error: ' + error.message);
                });
            });
        }

        async function collectFiles(path, allFiles = []) {
            try {
                const res = await fetch(`?api=scan&dir=${encodeURIComponent(path)}`);
                const data = await res.json();
                
                if (data.msg !== 'success') {
                    return allFiles;
                }
                
                directoriesScanned++;
                updateStats();
                
                // Add files from this directory
                allFiles.push(...data.data.file);
                
                // Recursively collect from subdirectories
                for (const dir of data.data.dir) {
                    await collectFiles(dir, allFiles);
                }
                
                return allFiles;
                
            } catch (error) {
                console.error('Error collecting files:', error);
                return allFiles;
            }
        }

        async function scanFiles(files) {
            for (let i = 0; i < files.length; i++) {
                await checkFile(files[i]);
                filesScannedCount++;
                updateProgress(filesScannedCount, totalFilesToScan);
            }
        }
        
        async function checkFile(file) {
            try {
                const res = await fetch(`?api=check&file=${encodeURIComponent(file)}`);
                
                if (!res.ok) {
                    console.error('HTTP error:', res.status);
                    return;
                }
                
                const data = await res.json();
                
                if (data.msg !== 'success') {
                    console.error('Check failed:', data.msg, file);
                    return;
                }
                
                totalFiles++;
                updateStats();
                
                const result = data.data;
                const analysis = result.content_analysis;
                
                console.log(`Checked: ${result.basename} - Score: ${analysis.score} - Level: ${analysis.level}`);
                
                // Only add to table if threats found
                if (analysis.status || result.extension_threat) {
                    threatsFound++;
                    updateStats();
                    
                    let levelClass = 'low';
                    if (analysis.level === 'CRITICAL') levelClass = 'critical';
                    else if (analysis.level === 'HIGH') levelClass = 'high';
                    else if (analysis.level === 'MEDIUM') levelClass = 'medium';
                    
                    const threats = analysis.threats.map(t => 
                        `<span class="badge bg-danger">${t}</span>`
                    ).join(' ');
                    
                    const extThreat = result.extension_threat ? 
                        `<span class="badge bg-warning">EXT:${result.suspicious_ext}</span>` : '';
                    
                    const escapedFile = result.file.replace(/'/g, "\\'");
                    
                    const rowData = [
                        `<input type="checkbox" class="form-check-input file-checkbox" data-file="${escapedFile}">`,
                        `<span class="file-name" title="${result.basename}">${result.basename}</span>`,
                        `<span class="file-path" title="${result.file}">${result.file}</span>`,
                        `<span class="badge bg-${levelClass}">${analysis.level}</span>`,
                        analysis.score,
                        extThreat + threats,
                        `<button class="btn btn-sm btn-primary me-1" onclick="viewFile('${escapedFile}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('${escapedFile}')" title="Clean">
                            <i class="fas fa-trash"></i>
                        </button>`
                    ];
                    
                    dtable.row.add(rowData).draw(false);
                    
                    console.log(`THREAT DETECTED: ${result.file} - Score: ${analysis.score}`);
                }
            } catch (error) {
                console.error('Check file error:', file, error);
            }
        }
        
        async function viewFile(file) {
            currentFile = file;
            document.getElementById('modalFileName').textContent = file.split('/').pop();
            
            try {
                const res = await fetch(`?api=getfile&file=${encodeURIComponent(file)}`);
                const data = await res.json();
                
                if (data.msg === 'success') {
                    const content = data.data.isEncoded ? atob(data.data.content) : data.data.content;
                    codeEditor.setValue(content);
                    codeModal.show();
                    setTimeout(() => codeEditor.refresh(), 100);
                }
            } catch (error) {
                alert('Error loading file: ' + error.message);
            }
        }
        
        async function saveFile() {
            const formData = new FormData();
            formData.append('file', currentFile);
            formData.append('content', codeEditor.getValue());
            
            try {
                const res = await fetch('?api=savefile', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                alert(data.msg === 'success' ? 'File saved!' : 'Error: ' + data.msg);
            } catch (error) {
                alert('Error saving file: ' + error.message);
            }
        }
        
        function deleteCurrentFile() {
            if (confirm('Clean this file? This will replace its content.')) {
                deleteFile(currentFile);
                codeModal.hide();
            }
        }
        
        function confirmDelete(file) {
            if (confirm(`Clean file: ${file}?`)) {
                deleteFile(file);
            }
        }
        
        async function deleteFile(file) {
            try {
                const res = await fetch(`?api=delete&file=${encodeURIComponent(file)}`);
                const data = await res.json();
                
                if (data.msg === 'success') {
                    alert('File cleaned successfully!');
                    dtable.rows().every(function() {
                        const rowData = this.data();
                        if (rowData[2] && rowData[2].includes(file)) {
                            this.remove();
                        }
                    });
                    dtable.draw();
                    threatsFound--;
                    updateStats();
                    selectedFiles.delete(file);
                    updateSelectedCount();
                } else {
                    alert('Error: ' + data.msg);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>
</html>
