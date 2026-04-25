<?php
header('Content-Type: application/json');
session_start();

$response = ['logged_in' => false, 'username' => ''];

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $response['logged_in'] = true;
    $response['username'] = $_SESSION['username'];
}

echo json_encode($response);
?>