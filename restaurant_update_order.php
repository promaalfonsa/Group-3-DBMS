<?php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    if ($action === 'accept') {
        $u = $mysqli->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
        $u->bind_param('i',$order_id); $u->execute();
    } else {
        $u = $mysqli->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $u->bind_param('i',$order_id); $u->execute();
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
