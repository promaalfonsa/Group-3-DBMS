<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in() || $_SESSION['role'] !== 'driver') exit;

$delivery_id = intval($_POST['delivery_id']);

// get driver id
$stmt = $mysqli->prepare("SELECT driver_id FROM deliveries WHERE id=?");
$stmt->bind_param('i',$delivery_id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();

$driver_id = $d['driver_id'];

// mark delivered
$mysqli->query("
UPDATE deliveries SET status='delivered' WHERE id=$delivery_id
");

// check if driver has any active deliveries
$check = $mysqli->prepare("
SELECT id FROM deliveries
WHERE driver_id=? AND status!='delivered'
LIMIT 1
");
$check->bind_param('i',$driver_id);
$check->execute();

if (!$check->get_result()->fetch_assoc()) {
    // no active orders → remove GPS
    $mysqli->query("
    DELETE FROM driver_locations WHERE driver_id=$driver_id
    ");
}

header('Location: driver_dashboard.php');
