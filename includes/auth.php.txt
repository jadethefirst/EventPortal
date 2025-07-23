<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'admin';
}

function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        header("Location: /login.php");
        exit();
    }
}
