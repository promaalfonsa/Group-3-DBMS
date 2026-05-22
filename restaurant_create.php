<?php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'restaurant') { echo 'Access denied'; exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = current_user_id();
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $stmt = $mysqli->prepare("INSERT INTO restaurants (user_id,name,address,city,phone) VALUES (?,?,?,?,?)");
    $stmt->bind_param('issss', $uid, $name, $address, $city, $phone);
    if ($stmt->execute()) header('Location: restaurant_dashboard.php');
    else echo 'Error: ' . $stmt->error;
}
