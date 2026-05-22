<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in() || $_SESSION['role'] !== 'driver') {
    exit('Unauthorized');
}

$driver_id = current_user_id();
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

if (!$lat || !$lng) exit('Invalid data');

$stmt = $mysqli->prepare("
INSERT INTO driver_locations (driver_id, latitude, longitude)
VALUES (?,?,?)
ON DUPLICATE KEY UPDATE
latitude=VALUES(latitude),
longitude=VALUES(longitude),
updated_at=NOW()
");

$stmt->bind_param('idd', $driver_id, $lat, $lng);
$stmt->execute();

echo "OK";
