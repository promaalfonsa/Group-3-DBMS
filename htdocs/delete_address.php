<?php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$id = intval($_GET['id'] ?? 0);
$uid = current_user_id();
$stmt = $mysqli->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii',$id,$uid); $stmt->execute();
header('Location: addresses.php'); exit;
