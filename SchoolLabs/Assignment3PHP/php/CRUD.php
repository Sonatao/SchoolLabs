<?php 

include 'config.php';

// Form Submission. Checking to make sure everything went over smoothly. Started losing my mind, at some point, and decided to make it all
// line up nicely, easier to deal with, also just more visually pleasing.

if (isset($_GET['action']) && $_GET['action'] === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
    // Form Submission. Checking to make sure everything went over smoothly.
    $full_name = $_POST["full_Name"] ?? null;
    $email     = $_POST["email"] ?? null;
    $bio       = $_POST["bio"] ?? null;
    $phone     = $_POST["phone"] ?? null;
    $password  = $_POST["password"] ?? null;


    // Kills it if something required isnt there, backup incase html breaks.
    if (!$full_name || !$email || !$password) {
        die("Form is invalid");
    }

// Checks if an image was actually uploaded
if (!isset($_FILES["profile_image"]["name"])) {
    die("No Image Uploaded");
}
$file = $_FILES["profile_image"];
$imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

// Basic security next, svgs, xss, prepared stm etc, had to go back into the class code to remember how to do this one, admittedly
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

// Ill be honest, this feels a little dangerous, what if I add a string of these letters to my file in order to bypass,
// I dont actually know if it would read it if I put a random jpeg extension as the end, but left it as just 'jpeg' and not .jpeg
// Since this is just looking for allowed_types inside of the filetype which pulls the path info, which shows the file type?
if (!in_array($imageFileType, $allowed_types)) {
    die('Only Jpeg, jpg, png, or GIF are files allowed as profile images.');
}

// Dont want to get zip bombed to oblivion by a heavenscale screenshot
$max_file_size = 2 * 1024 * 1024;
if ($file["size"] > $max_file_size) {
    die("The file is too large, please use a file less than 2mb");
}

// This verifies that the uploaded file actually is an image and not some random file disguised as one
if (getimagesize($file["tmp_name"]) === false) {
    die("File is not an image.");
}

// Sets up the file with a temporary home as we continue forward, with full permissions in order to continue using it as we wish.
$target_directory = "../uploads/";
if (!file_exists($target_directory)) {
    mkdir($target_directory, 0777, true);
}

// I vaguely understood this, as adding a new uniq caller to the image from before, in order to identify it. I'm confident that's correct
// But I'll be honest, Im not extremely certain, but it works. W3Schools working overtime here. 
$new_filename = uniqid('img_', true) . '.' . $imageFileType;
$target_file  = $target_directory . $new_filename;

// If it isnt moved properly, or something breaks somewhere here, it'll display a death echo.
if (!move_uploaded_file($file['tmp_name'], $target_file)) {
    die('Failed to upload Image.');
}

$sql = "INSERT INTO users (full_Name, email, bio, phone, password, profile_image)
        VALUES (:full_name, :email, :bio, :phone, :password, :profile_image)";

$stmt = $pdo->prepare($sql);
// Added a hash here, I can't remember honestly if we learned it so I put it in just in case, since I didn't want to go back and check
// through everything to see if we needed it or not 
try {
    $stmt->execute([
        ':full_name'     => $full_name,
        ':email'         => $email,
        ':bio'           => $bio,
        ':phone'         => $phone,
        ':password'      => password_hash($password, PASSWORD_DEFAULT),
        ':profile_image' => $target_file
    ]);

    echo "Profile created successfully!";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    
}
exit;
}
// This little bracket right above this comment, caused so much extra work. Hateful little thing.



/*
    Read Capability Below
    The Idea here is to build something that fetches 1 profile based off the uniqid provided by the table setup, using the 
    primary key, that or the name, we'll see which one it ends up with.
*/

// This just makes sure the name == action, and the action is 'read', so the html can communicate to the backend,
// if this doesnt work, it should just kill itself, if I did it right. 

if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['name'])) {

    $name = trim($_GET['name']);

    // Cant be empty. Also sorry for all the comments, they're for me too, as I debug ! 
    if (empty($name)) {
        // Wasn't outputting errors, because I forgot to turn it into an html readable response.
        echo json_encode(['Error: ' => "Please enter a name"]);
        exit;
    }

    try {
        // This section will just be error handling to find for what we need using stmt's for security, 'Like' case-insensitive
        $stmt = $pdo->prepare("
            SELECT id, full_Name, email, bio, profile_image, created_at
            FROM users
            WHERE full_Name LIKE :name
            ORDER BY created_at DESC
        ");

        // This works, but, I really am not too sure.
        $stmt->execute([':name' => "%$name%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($users ?: ['error' => 'No User Found']);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

    // Hour 2 Check-in: 
    // 
    // I was thinking about partial search functions here in case you search like "Double O" but not the "Seven", you'd still get
    // Double O Seven appearing instead of it just being screwed because you forgot a letter or something like that
    // But, I wasn't sure about how it all worked and didn't want what works breaking here,
    // The general syntax was %full_Name% or fetchAll(PDO::FETCH_ASSOC) and it says its for an associative array,
    //  But I just genuinely could not wrap my head around the system, and if it broke, I couldn't fix it, so I left it out.
    // You'll notice I did use it, which is why I thought to maybe use it for partial strings but, didn't work. Not sure why!


    // I want to do what you can do on instragram with all the accounts listed there and then clicked onto a templated profile page, would be cool I think.

    if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        $stmt = $pdo->query("SELECT id, full_Name, email, bio, profile_image, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($users ?: []);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

?>
