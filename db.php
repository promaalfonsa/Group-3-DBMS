<?php
$DB_HOST = 'sql208.infinityfree.com';
$DB_USER = 'if0_41442926';
$DB_PASS = 'zi3mBY4HlumF';
$DB_NAME = 'if0_41442926_khadok';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("DB Connect failed: " . $mysqli->connect_error);
}
session_start();
?>