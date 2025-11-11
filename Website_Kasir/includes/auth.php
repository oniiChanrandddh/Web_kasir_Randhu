<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isKasir() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}
?>
