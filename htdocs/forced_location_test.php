<?php
require 'db.php';

$stmt = $mysqli->prepare("
INSERT INTO driver_locations (driver_id, order_id, latitude, longitude)
VALUES (1, 16, 23.8103, 90.4125)
ON DUPLICATE KEY UPDATE
latitude=VALUES(latitude),
longitude=VALUES(longitude),
updated_at=NOW()
");

if ($stmt->execute()) {
  echo "INSERT OK";
} else {
  echo $stmt->error;
}
