<?php
// update_driver_location.php
require 'db.php';
require 'functions.php';

header('Content-Type: application/json');
if (!is_logged_in() || $_SESSION['role'] !== 'driver') {
    echo json_encode(['ok'=>false,'error'=>'not_authenticated']);
    exit;
}

$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;
$driver_id = current_user_id();

if ($lat === null || $lng === null) {
    echo json_encode(['ok'=>false,'error'=>'no_coords']);
    exit;
}

// update deliveries rows assigned to this driver that are not delivered
$stmt = $mysqli->prepare("UPDATE deliveries SET latitude = ?, longitude = ? WHERE driver_id = ? AND status != 'delivered'");
$stmt->bind_param('ddi', $lat, $lng, $driver_id);
$stmt->execute();

// Optionally respond with OK
echo json_encode(['ok'=>true]);
