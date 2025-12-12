<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "editor_fotos_db";

$conexion = new mysqli($host, $usuario, $clave, $bd);

if ($conexion->connect_error) {
    die("Error en conexión: " . $conexion->connect_error);
}
?>
