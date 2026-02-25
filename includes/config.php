<?php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'practice_way-point');
define('DB_USER', 'root');
define('DB_PASS', '');
define('WAYPOINT_USER', '69900d0ead28650d6d53b09e');
define('WAYPOINT_PASS', '9Zzos2S4mKNXLgqHC296278g');
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'Waypoint');
define('SITE_URL', 'http://localhost/waypoint/');
?>
