<?php
function loadEnv($path) {
    if (!file_exists($path)) {
        http_response_code(500);
        die('Configuration Error: The .env file was not found at ' . $path);
    }
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $env_data = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        $parsed = parse_ini_string($line, false, INI_SCANNER_RAW);
        if ($parsed) {
            $env_data = array_merge($env_data, $parsed);
        }
    }
    foreach ($env_data as $key => $value) {
        $value = trim($value, "\"'");
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
loadEnv(__DIR__ . '/.env');
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';
    $ADMIN_USER = $_ENV['EDU_TRACK_USER'] ?? '';
    $ADMIN_PASS = $_ENV['EDU_TRACK_PASSWORD'] ?? '';
    if ($input_username === $ADMIN_USER && $input_password === $ADMIN_PASS) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $ADMIN_USER;
        header("Location: dashboard.php"); 
        exit;
    } else {
        header("Location: login.html?error=invalid_credentials");
        exit;
    }
} else {
    header("Location: login.html");
    exit;
}
?>