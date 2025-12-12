<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_FILES['imagen'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibió imagen']);
        exit;
    }

    $archivo = $_FILES['imagen'];
    
    // Validaciones
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Error al subir archivo']);
        exit;
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $permitidos)) {
        echo json_encode(['success' => false, 'error' => 'Formato no permitido']);
        exit;
    }

    // Crear directorio
    if (!file_exists('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Guardar temporalmente
    $rutaTemp = 'uploads/temp_' . session_id() . '.' . $extension;
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaTemp)) {
        $_SESSION['imagen_temp'] = $rutaTemp;
        echo json_encode(['success' => true, 'ruta' => $rutaTemp]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar archivo']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>