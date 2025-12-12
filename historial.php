<?php
session_start();

// Obtener todas las imágenes editadas
$directorio = 'uploads/';
$imagenes = [];

if (is_dir($directorio)) {
    $archivos = scandir($directorio);
    foreach ($archivos as $archivo) {
        if (preg_match('/^editada_/', $archivo)) {
            $ruta = $directorio . $archivo;
            $imagenes[] = [
                'nombre' => $archivo,
                'ruta' => $ruta,
                'fecha' => filemtime($ruta),
                'tamano' => filesize($ruta)
            ];
        }
    }
}

// Ordenar por fecha más reciente
usort($imagenes, function($a, $b) {
    return $b['fecha'] - $a['fecha'];
});

function formatearTamano($bytes) {
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
    <title>Historial - Editor de Fotos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: #fff;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .image-card {
            background: #2a2a2a;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .image-preview {
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-info {
            padding: 20px;
        }

        .image-info h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 14px;
            word-break: break-all;
        }

        .image-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #999;
        }

        .image-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
            flex: 1;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 5px 20px rgba(220, 53, 69, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📜 Historial de Imágenes Editadas</h1>
    <p>Todas tus creaciones en un solo lugar</p>
</div>

<div class="container">
    <div class="top-actions">
        <h2>Total: <?php echo count($imagenes); ?> imagen(es)</h2>
        <a href="index.php" class="btn">🆕 Nueva Edición</a>
    </div>

    <?php if (empty($imagenes)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h2>No hay imágenes editadas aún</h2>
            <p>Comienza editando tu primera imagen</p>
            <br>
            <a href="index.php" class="btn">Empezar Ahora</a>
        </div>
    <?php else: ?>
        <div class="gallery">
            <?php foreach ($imagenes as $img): ?>
                <div class="image-card">
                    <div class="image-preview">
                        <img src="<?php echo htmlspecialchars($img['ruta']); ?>?<?php echo time(); ?>" 
                             alt="<?php echo htmlspecialchars($img['nombre']); ?>">
                    </div>
                    <div class="image-info">
                        <h3><?php echo htmlspecialchars($img['nombre']); ?></h3>
                        <div class="image-meta">
                            <span>📅 <?php echo date('d/m/Y H:i', $img['fecha']); ?></span>
                            <span>💾 <?php echo formatearTamano($img['tamano']); ?></span>
                        </div>
                        <div class="image-actions">
                            <a href="<?php echo htmlspecialchars($img['ruta']); ?>" 
                               download class="btn btn-small">
                                ⬇️ Descargar
                            </a>
                            <button onclick="eliminarImagen('<?php echo htmlspecialchars($img['nombre']); ?>')" 
                                    class="btn btn-small btn-danger">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function eliminarImagen(nombre) {
    if (!confirm('¿Estás seguro de eliminar esta imagen?')) return;
    
    fetch('eliminar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ archivo: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Imagen eliminada');
            location.reload();
        } else {
            alert('Error al eliminar: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error al eliminar la imagen');
        console.error(error);
    });
}
</script>

</body>
</html>