<?php
require_once "CRUD.php"; // includes $pdo connection

header("Content-Type: text/html; charset=UTF-8");

try {
    $stmt = $pdo->query("SELECT full_Name, bio, profile_image FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Profiles</title>
    <link rel="stylesheet" href="../profiles.css">
    <!-- Optionally include local profile CSS if you want the alternative layout -->
    <!-- <link rel="stylesheet" href="../profiles.css"> -->
</head>
<body>
    <main class="profiles-page container">
        <h1>All Profiles</h1>

        <?php if (empty($users)): ?>
            <p>No profiles found.</p>
        <?php else: ?>
            <section class="profiles-grid">
                <?php foreach ($users as $user):
                    $dbVal = $user['profile_image'] ?? '';
                    $basename = $dbVal ? basename($dbVal) : '';

                    // Used this to build a way to get into the uploads folder from the Webroot directly turning it into a variable that I can use, instead of concatinating
                    // a path within the folder. 
                    $uploadsUrl = '';
                    if ($basename !== '') {
                        $requestDir = dirname($_SERVER['REQUEST_URI'] ?? '');
                        $expected = preg_replace('#/php(/.*)?$#', '/uploads/' . $basename, $requestDir);
                        if ($expected === $requestDir || $expected === false) {
                            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
                            $expected = preg_replace('#/php(/.*)?$#', '/uploads/' . $basename, $scriptDir);
                        }
                        if ($expected === $requestDir || $expected === false) {
                            $uploadsUrl = '../uploads/' . $basename; // relative fallback
                        } else {
                            $uploadsUrl = $expected;
                        }
                    }
                    ?>

                    <article class="profile-card" onclick="location.href='#'" aria-label="Profile of <?php echo htmlspecialchars($user['full_Name']); ?>">
                        <?php if ($basename !== ''): ?>
                            <div class="profile-photo">
                                <img src="<?php echo htmlspecialchars($uploadsUrl); ?>" alt="<?php echo htmlspecialchars($user['full_Name']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="profile-photo"><div class="no-photo">No photo</div></div>
                        <?php endif; ?>

                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['full_Name']); ?></h2>
                            <p><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'No bio available'; ?></p>
                        </div>
                    </article>

                <?php endforeach; ?>
            </section>
        <?php endif; ?>

    </main>
    <script src="../jScript/backToFrontConn.js" defer></script>
</body>
</html>