<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// php/debug2.php - diagnostic for uploads issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Server PHP diagnostic</h2>";

// 1) Basic server info
echo "<p><strong>__DIR__:</strong> " . htmlspecialchars(__DIR__) . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";

// 2) Show uploads folder candidate on disk
$uploads_disk = realpath(__DIR__ . "/../uploads");
echo "<p><strong>uploads realpath (server-side):</strong> " . ($uploads_disk ? "<code>" . htmlspecialchars($uploads_disk) . "</code>" : "<span style='color:red'>NOT FOUND</span>") . "</p>";

// 3) List files in uploads (if readable)
if ($uploads_disk && is_dir($uploads_disk)) {
    echo "<h3>uploads folder listing</h3><ul>";
    $files = scandir($uploads_disk);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $file_path = $uploads_disk . DIRECTORY_SEPARATOR . $f;
        echo "<li><code>" . htmlspecialchars($f) . "</code> — exists: " . (file_exists($file_path) ? "<strong style='color:green'>yes</strong>" : "<span style='color:red'>no</span>");
        echo " — size: " . (is_file($file_path) ? filesize($file_path) . " bytes" : "—");
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Uploads directory is missing or not readable by PHP.</p>";
}

// 4) Pull one DB row so we know exactly what DB stores
require_once __DIR__ . '/CRUD.php'; // adjust only if path differs
try {
    $stmt = $pdo->query("SELECT id, full_Name, profile_image FROM users ORDER BY created_at DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>DB row (latest)</h3><pre>" . htmlspecialchars(json_encode($row, JSON_PRETTY_PRINT)) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'><strong>DB error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    $row = null;
}

// 5) Server-side file check for the DB value
if ($row && !empty($row['profile_image'])) {
    $dbVal = $row['profile_image'];
    // normalize value (strip leading ./ or ../ and keep basename)
    $basename = basename($dbVal);
    $candidate_disk = __DIR__ . "/../uploads/" . $basename;
    echo "<p><strong>DB profile_image:</strong> <code>" . htmlspecialchars($dbVal) . "</code></p>";
    echo "<p><strong>basename:</strong> <code>" . htmlspecialchars($basename) . "</code></p>";
    echo "<p><strong>candidate disk path:</strong> <code>" . htmlspecialchars($candidate_disk) . "</code></p>";
    echo "<p>file_exists: " . (file_exists($candidate_disk) ? "<strong style='color:green'>YES</strong>" : "<strong style='color:red'>NO</strong>") . "</p>";

    // Show the browser URL we expect to work:
    $absoluteUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['REQUEST_URI']);
    // request URI e.g. /SchoolLabs/Assignment3PHP/php/debug2.php => dirname -> /SchoolLabs/Assignment3PHP/php
    // but we want uploads at project root, so replace /php with /uploads
    $expectedUrl = preg_replace('#/php(/.*)?$#', '/uploads/' . $basename, dirname($_SERVER['REQUEST_URI']));
    if ($expectedUrl === dirname($_SERVER['REQUEST_URI'])) {
        // fallback to absolute project path guess
        $expectedUrl = '/SchoolLabs/Assignment3PHP/uploads/' . $basename;
    }
    $browserLink = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $expectedUrl;
    echo "<p><strong>Browser test URL (open in new tab):</strong> <a href='" . htmlspecialchars($browserLink) . "' target='_blank'>" . htmlspecialchars($browserLink) . "</a></p>";
    echo "<h3>Preview attempts (these may be broken)</h3>";
    foreach ([$dbVal, "../uploads/" . $basename, "/SchoolLabs/Assignment3PHP/uploads/" . $basename, $browserLink] as $src) {
        echo "<div style='margin:10px 0; padding:8px; border:1px solid #ddd;'>";
        echo "<p><code>" . htmlspecialchars($src) . "</code></p>";
        echo "<img src='" . htmlspecialchars($src) . "' style='max-width:200px;display:block;border:1px solid #ccc;padding:4px' alt='preview'>";
        echo "</div>";
    }
} else {
    echo "<p>No profile_image found in DB row to test.</p>";
}

echo "<hr>";
echo "<p>After opening this page, copy the full output and paste it here (or tell me which lines show NOT FOUND / NO / 404).</p>";
?>