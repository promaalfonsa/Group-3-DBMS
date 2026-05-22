<?php
require 'db.php';
require 'functions.php';
if (!is_admin() && $_SESSION['role'] !== 'restaurant') { header('Location: login.php'); exit; }
$id = intval($_GET['id'] ?? 0);
$rid = intval($_GET['rid'] ?? 0);
if ($id>0) { $stmt = $mysqli->prepare('DELETE FROM menu_items WHERE id = ?'); $stmt->bind_param('i',$id); $stmt->execute(); }
if ($rid) header('Location: restaurant_menu_list.php?rid='.$rid); else header('Location: admin_dashboard.php');
exit;
