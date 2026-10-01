<?php
require_once __DIR__ . '/../config/conexion.php';

function esta_logueado() {
    return isset($_SESSION['usuario_id']);
}

function es_admin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function requerir_login() {
    if (!esta_logueado()) {
        redirect('/kaia_sv/login.php');
    }
}

function requerir_admin() {
    if (!es_admin()) {
        redirect('/kaia_sv/login.php');
    }
}