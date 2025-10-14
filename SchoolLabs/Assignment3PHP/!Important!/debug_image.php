<?php
// debug_image.php
echo "<pre>";

echo "=== SERVER ENVIRONMENT ===\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";

$uploadsDir = realpath(__DIR__ . '/../uploads');
echo "uploadsDir (realpath): " . $uploadsDir . "\n";

echo "\n=== DATABASE CHECK ===\n";
require_once "CRUD.php";
$stmt = $pdo->query("SELECT id, full_Name, profile_image FROM users ORDER BY id DESC LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("No user found.\n");
}

print_r($user);

$imgFromDB = $user['profile_image'];
echo "\nRaw DB path: {$imgFromDB}\n";

$cleanPath = str_replace(['./', '\\'], ['','/'], $imgFromDB);
echo "After clean: {$cleanPath}\n";

if (!str_starts_with($cleanPath, '/')) {
    $cleanPath = '/SchoolLabs/Assignment3PHP/' . ltrim($cleanPath, '/');
}
echo "Final web path: {$cleanPath}\n";

$fullDiskPath = realpath($_SERVER['DOCUMENT_ROOT'] . $cleanPath);
echo "Resolved fullDiskPath: {$fullDiskPath}\n";

if ($fullDiskPath && file_exists($fullDiskPath)) {
    echo "✅ File FOUND on disk.\n";
} else {
    echo "❌ File NOT FOUND on disk.\n";
}

echo "\n=== TEST LINKS ===\n";
echo "<a href='{$cleanPath}' target='_blank'>Open Image in Browser</a>\n";

echo "\n=== PERMISSIONS ===\n";
echo "Uploads folder is readable? " . (is_readable($uploadsDir) ? '✅ YES' : '❌ NO') . "\n";

echo "</pre>";
?>