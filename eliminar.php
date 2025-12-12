<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['archivo'])) {
        echo json_encode(['success' => false, 'error' => 'No se especificó archivo']);
        exit;
    }
    
    $archivo = basename($data['archivo']); // Seguridad: solo nombre de archivo
    $ruta = 'uploads/' . $archivo;
    
    // Validar que sea una imagen editada
    if (!preg_match('/^editada_/', $archivo)) {
        echo json_encode(['success' => false, 'error' => 'Archivo no válido']);
        exit;
    }
    
    if (!file_exists($ruta)) {
        echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
        exit;
    }
    
    if (unlink($ruta)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo eliminar']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>