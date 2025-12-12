<?php
// Deshabilitar cualquier salida de errores
error_reporting(0);
ini_set('display_errors', 0);

// Iniciar sesión
session_start();

// Configurar header JSON ANTES de cualquier salida
header('Content-Type: application/json; charset=utf-8');

// Función para enviar respuesta JSON
function enviarRespuesta($success, $data = [], $error = '') {
    echo json_encode([
        'success' => $success,
        'error' => $error,
        'ruta' => $data['ruta'] ?? '',
        'mensaje' => $data['mensaje'] ?? ''
    ]);
    exit;
}

try {
    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarRespuesta(false, [], 'Método no permitido');
    }

    // Leer datos JSON
    $input = file_get_contents('php://input');
    $ajustes = json_decode($input, true);

    if (!$ajustes) {
        enviarRespuesta(false, [], 'Datos inválidos');
    }

    // Verificar que exista imagen temporal
    if (!isset($_SESSION['imagen_temp']) || !file_exists($_SESSION['imagen_temp'])) {
        enviarRespuesta(false, [], 'No hay imagen cargada');
    }

    $rutaOriginal = $_SESSION['imagen_temp'];

    // Crear imagen desde el archivo
    $extension = strtolower(pathinfo($rutaOriginal, PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $imagen = @imagecreatefromjpeg($rutaOriginal);
            break;
        case 'png':
            $imagen = @imagecreatefrompng($rutaOriginal);
            break;
        case 'gif':
            $imagen = @imagecreatefromgif($rutaOriginal);
            break;
        case 'webp':
            $imagen = @imagecreatefromwebp($rutaOriginal);
            break;
        default:
            enviarRespuesta(false, [], 'Formato no soportado');
    }

    if (!$imagen) {
        enviarRespuesta(false, [], 'Error al procesar imagen');
    }

    // Obtener dimensiones
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);

    // Redimensionar si se especificó
    if (!empty($ajustes['ancho']) && $ajustes['ancho'] > 0) {
        $nuevoAncho = (int)$ajustes['ancho'];
        $nuevoAlto = (int)($alto * ($nuevoAncho / $ancho));
        
        $imagenRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        
        // Preservar transparencia
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($imagenRedimensionada, false);
            imagesavealpha($imagenRedimensionada, true);
        }
        
        imagecopyresampled($imagenRedimensionada, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        imagedestroy($imagen);
        $imagen = $imagenRedimensionada;
        $ancho = $nuevoAncho;
        $alto = $nuevoAlto;
    }

    // Aplicar ajustes básicos
    $brillo = (int)($ajustes['brillo'] ?? 0);
    $contraste = (int)($ajustes['contraste'] ?? 0);
    $saturacion = (int)($ajustes['saturacion'] ?? 0);
    $exposicion = (int)($ajustes['exposicion'] ?? 0);

    // Brillo + Exposición
    $brilloTotal = $brillo + ($exposicion * 1.5);
    if ($brilloTotal != 0) {
        imagefilter($imagen, IMG_FILTER_BRIGHTNESS, (int)$brilloTotal);
    }

    // Contraste
    if ($contraste != 0) {
        imagefilter($imagen, IMG_FILTER_CONTRAST, -$contraste);
    }

    // Saturación (simulada con colorize)
    if ($saturacion != 0) {
        $factor = $saturacion / 100;
        if ($saturacion > 0) {
            imagefilter($imagen, IMG_FILTER_COLORIZE, 0, 0, 0, 0);
        } else {
            imagefilter($imagen, IMG_FILTER_GRAYSCALE);
            imagefilter($imagen, IMG_FILTER_COLORIZE, 0, 0, 0, (int)(127 * abs($factor)));
        }
    }

    // Temperatura (Cálido/Frío)
    $temperatura = (int)($ajustes['temperatura'] ?? 0);
    if ($temperatura != 0) {
        $rojo = $temperatura > 0 ? $temperatura / 2 : 0;
        $azul = $temperatura < 0 ? abs($temperatura) / 2 : 0;
        imagefilter($imagen, IMG_FILTER_COLORIZE, (int)$rojo, 0, (int)$azul);
    }

    // Tinte
    $tinte = (int)($ajustes['tinte'] ?? 0);
    if ($tinte != 0) {
        $verde = $tinte > 0 ? $tinte / 2 : 0;
        $magenta = $tinte < 0 ? abs($tinte) / 2 : 0;
        imagefilter($imagen, IMG_FILTER_COLORIZE, (int)$magenta, (int)$verde, 0);
    }

    // Desenfoque
    $desenfoque = (int)($ajustes['desenfoque'] ?? 0);
    if ($desenfoque > 0) {
        for ($i = 0; $i < $desenfoque; $i++) {
            imagefilter($imagen, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }

    // Nitidez
    $nitidez = (int)($ajustes['nitidez'] ?? 0);
    if ($nitidez > 0) {
        $matrix = array(
            array(-1, -1, -1),
            array(-1, 16 + ($nitidez / 10), -1),
            array(-1, -1, -1)
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        imageconvolution($imagen, $matrix, $divisor, 0);
    }

    // Aplicar filtros creativos
    $filtro = $ajustes['filtro'] ?? 'ninguno';
    
    switch ($filtro) {
        case 'bn':
            imagefilter($imagen, IMG_FILTER_GRAYSCALE);
            break;
            
        case 'sepia':
            imagefilter($imagen, IMG_FILTER_GRAYSCALE);
            imagefilter($imagen, IMG_FILTER_COLORIZE, 90, 60, 30);
            break;
            
        case 'vintage':
            imagefilter($imagen, IMG_FILTER_BRIGHTNESS, -20);
            imagefilter($imagen, IMG_FILTER_CONTRAST, -15);
            imagefilter($imagen, IMG_FILTER_COLORIZE, 60, 30, -10);
            break;
            
        case 'invertir':
            imagefilter($imagen, IMG_FILTER_NEGATE);
            break;
            
        case 'relieve':
            imagefilter($imagen, IMG_FILTER_EMBOSS);
            break;
            
        case 'detectarBordes':
            imagefilter($imagen, IMG_FILTER_EDGEDETECT);
            break;
            
        case 'pixelar':
            imagefilter($imagen, IMG_FILTER_PIXELATE, 10, true);
            break;
            
        case 'posterizar':
            imagefilter($imagen, IMG_FILTER_MEAN_REMOVAL);
            break;
            
        case 'acuarela':
            imagefilter($imagen, IMG_FILTER_GAUSSIAN_BLUR);
            imagefilter($imagen, IMG_FILTER_SMOOTH, 5);
            imagefilter($imagen, IMG_FILTER_COLORIZE, 10, 10, 20);
            break;
    }

    // Guardar preview temporal
    $rutaPreview = 'uploads/temp_preview.jpg';
    
    // Crear directorio si no existe
    if (!file_exists('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Guardar con calidad alta
    imagejpeg($imagen, $rutaPreview, 90);
    imagedestroy($imagen);

    // Respuesta exitosa
    enviarRespuesta(true, ['ruta' => $rutaPreview], '');

} catch (Exception $e) {
    enviarRespuesta(false, [], 'Error interno: ' . $e->getMessage());
}
?>