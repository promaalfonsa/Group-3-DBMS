<?php
require 'db.php';

$order_id = intval($_GET['order_id']);

$stmt = $mysqli->prepare("
SELECT dl.latitude, dl.longitude
FROM deliveries d
JOIN driver_locations dl ON dl.driver_id = d.driver_id
WHERE d.order_id = ?
LIMIT 1
");

$stmt->bind_param('i', $order_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode([
  'lat' => $row['latitude'] ?? null,
  'lng' => $row['longitude'] ?? null
]);
