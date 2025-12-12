<?php
session_start();
require_once "conexion.php";

// Configuración
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_DIR', 'uploads/');
define('RESULT_DIR', 'resultados/');
define('JPEG_QUALITY', 90);

// Función para mostrar errores con estilo
function mostrarError($mensaje) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error - Editor de Fotos</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h2 {
                color: #e74c3c;
                margin-bottom: 15px;
            }
            p {
                color: #666;
                margin-bottom: 25px;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: transform 0.2s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <div class='error-icon'>⚠️</div>
            <h2>Error al procesar imagen</h2>
            <p>" . htmlspecialchars($mensaje) . "</p>
            <a href='index.php' class='btn'>Volver al editor</a>
        </div>
    </body>
    </html>";
    exit;
}

// Crear carpetas si no existen con permisos adecuados
try {
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            throw new Exception("No se pudo crear la carpeta de uploads");
        }
    }
    if (!is_dir(RESULT_DIR)) {
        if (!mkdir(RESULT_DIR, 0755, true)) {
            throw new Exception("No se pudo crear la carpeta de resultados");
        }
    }
} catch (Exception $e) {
    mostrarError("Error en la configuración de directorios: " . $e->getMessage());
}

// Validar que se haya enviado el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validar que se haya subido un archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    $errores = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
        UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
    ];
    
    $codigoError = $_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE;
    mostrarError($errores[$codigoError] ?? 'Error desconocido al subir el archivo');
}

// Validar tamaño del archivo
if ($_FILES['imagen']['size'] > MAX_FILE_SIZE) {
    mostrarError("El archivo es demasiado grande. Tamaño máximo: 5MB");
}

// Validar tipo MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, ALLOWED_TYPES)) {
    mostrarError("Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG, GIF y WebP");
}

// Validar que sea una imagen real
$imageInfo = getimagesize($_FILES['imagen']['tmp_name']);
if ($imageInfo === false) {
    mostrarError("El archivo no es una imagen válida");
}

// Generar nombre único y seguro
$extension = match($mimeType) {
    'image/jpeg', 'image/jpg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    default => 'jpg'
};

$nombreArchivo = uniqid('img_', true) . '_' . time() . '.' . $extension;
$rutaSubida = UPLOAD_DIR . $nombreArchivo;

// Mover archivo subido
if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaSubida)) {
    mostrarError("Error al guardar el archivo subido");
}

// Cargar imagen según su tipo
try {
    $img = match($mimeType) {
        'image/jpeg', 'image/jpg' => imagecreatefromjpeg($rutaSubida),
        'image/png' => imagecreatefrompng($rutaSubida),
        'image/gif' => imagecreatefromgif($rutaSubida),
        'image/webp' => imagecreatefromwebp($rutaSubida),
        default => imagecreatefromstring(file_get_contents($rutaSubida))
    };

    if ($img === false) {
        throw new Exception("No se pudo cargar la imagen");
    }
} catch (Exception $e) {
    unlink($rutaSubida); // Eliminar archivo si falla
    mostrarError("Error al procesar la imagen: " . $e->getMessage());
}

// Obtener dimensiones originales
$anchoOriginal = imagesx($img);
$altoOriginal = imagesy($img);

// Validar y aplicar filtro
$filtrosPermitidos = ['ninguno', 'bn', 'sepia', 'invertir', 'desenfoque', 'relieve'];
$filtro = $_POST['filtro'] ?? 'ninguno';

if (!in_array($filtro, $filtrosPermitidos)) {
    $filtro = 'ninguno';
}

// Aplicar filtro seleccionado
switch ($filtro) {
    case "bn":
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        break;

    case "sepia":
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagefilter($img, IMG_FILTER_COLORIZE, 90, 60, 40);
        break;

    case "invertir":
        imagefilter($img, IMG_FILTER_NEGATE);
        break;

    case "desenfoque":
        imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
        break;

    case "relieve":
        imagefilter($img, IMG_FILTER_EMBOSS);
        break;
}

// Redimensionar si se especificó ancho
$nuevoAncho = null;
if (!empty($_POST['ancho'])) {
    $nuevoAncho = filter_var($_POST['ancho'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 50, 'max_range' => 5000]
    ]);

    if ($nuevoAncho !== false && $nuevoAncho > 0) {
        $nuevoAlto = intval(($nuevoAncho * $altoOriginal) / $anchoOriginal);

        $redimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        
        // Preservar transparencia para PNG y GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($redimensionada, false);
            imagesavealpha($redimensionada, true);
        }

        imagecopyresampled(
            $redimensionada, $img, 
            0, 0, 0, 0,
            $nuevoAncho, $nuevoAlto, 
            $anchoOriginal, $altoOriginal
        );

        imagedestroy($img);
        $img = $redimensionada;
    }
}

// Guardar resultado
$nombreResultado = 'result_' . $nombreArchivo;
$rutaResultado = RESULT_DIR . $nombreResultado;

try {
    $guardado = match($extension) {
        'png' => imagepng($img, $rutaResultado, 9),
        'gif' => imagegif($img, $rutaResultado),
        'webp' => imagewebp($img, $rutaResultado, JPEG_QUALITY),
        default => imagejpeg($img, $rutaResultado, JPEG_QUALITY)
    };

    if (!$guardado) {
        throw new Exception("Error al guardar la imagen procesada");
    }
} catch (Exception $e) {
    imagedestroy($img);
    unlink($rutaSubida);
    mostrarError("Error al guardar la imagen: " . $e->getMessage());
}

imagedestroy($img);

// Obtener tamaño del archivo resultante
$tamanoArchivo = filesize($rutaResultado);

// Guardar en base de datos usando prepared statement
try {
    $stmt = $conexion->prepare(
        "INSERT INTO ediciones (imagen_original, imagen_editada, filtro, ancho, fecha) 
         VALUES (?, ?, ?, ?, NOW())"
    );
    
    $stmt->bind_param("sssi", $rutaSubida, $rutaResultado, $filtro, $nuevoAncho);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar en la base de datos");
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error BD: " . $e->getMessage());
    // No mostramos error al usuario ya que la imagen se procesó correctamente
}

// Función para formatear bytes
function formatearBytes($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado - Editor de Fotos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }

        .success-icon {
            text-align: center;
            font-size: 64px;
            margin-bottom: 20px;
        }

        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .image-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .image-box h3 {
            color: #555;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .image-box img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .info-item {
            text-align: center;
        }

        .info-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }

            .comparison {
                grid-template-columns: 1fr;
            }

            h2 {
                font-size: 24px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="success-icon">✅</div>
    <h2>¡Imagen Procesada Exitosamente!</h2>

    <div class="comparison">
        <div class="image-box">
            <h3>📸 Imagen Original</h3>
            <img src="<?= htmlspecialchars($rutaSubida) ?>" alt="Imagen original">
        </div>
        <div class="image-box">
            <h3>✨ Imagen Editada</h3>
            <img src="<?= htmlspecialchars($rutaResultado) ?>" alt="Imagen editada">
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Filtro Aplicado</div>
            <div class="info-value"><?= htmlspecialchars(ucfirst($filtro)) ?></div>
        </div>
        <?php if ($nuevoAncho): ?>
        <div class="info-item">
            <div class="info-label">Nuevo Ancho</div>
            <div class="info-value"><?= htmlspecialchars($nuevoAncho) ?> px</div>
        </div>
        <?php endif; ?>
        <div class="info-item">
            <div class="info-label">Tamaño Archivo</div>
            <div class="info-value"><?= formatearBytes($tamanoArchivo) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Formato</div>
            <div class="info-value"><?= strtoupper($extension) ?></div>
        </div>
    </div>

    <div class="actions">
        <a href="<?= htmlspecialchars($rutaResultado) ?>" download="imagen_editada_<?= time() ?>.<?= $extension ?>" class="btn btn-success">
            <span>⬇️</span>
            <span>Descargar Imagen</span>
        </a>
        <a href="index.php" class="btn btn-primary">
            <span>🖼️</span>
            <span>Editar Otra Imagen</span>
        </a>
        <a href="historial.php" class="btn btn-secondary">
            <span>📜</span>
            <span>Ver Historial</span>
        </a>
    </div>
</div>

</body>
</html>

<?php
// Cerrar conexión
if (isset($conexion)) {
    $conexion->close();
}
?>