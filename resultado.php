<?php
session_start();

$imagenGuardada = isset($_GET['img']) ? $_GET['img'] : '';

if (empty($imagenGuardada) || !file_exists($imagenGuardada)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imagen Guardada - Editor de Fotos</title>
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
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .success-box {
            background: #2a2a2a;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 800px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .image-result {
            margin: 30px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .image-result img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
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

        .btn-secondary {
            background: #333;
        }

        .btn-secondary:hover {
            background: #444;
            box-shadow: 0 5px 20px rgba(255, 255, 255, 0.1);
        }

        .info-text {
            color: #999;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>✅ ¡Imagen Guardada Exitosamente!</h1>
</div>

<div class="container">
    <div class="success-box">
        <div class="success-icon">🎉</div>
        <h2>Tu imagen ha sido procesada y guardada</h2>
        
        <div class="image-result">
            <img src="<?php echo htmlspecialchars($imagenGuardada); ?>?<?php echo time(); ?>" alt="Imagen editada">
        </div>

        <div class="actions">
            <a href="<?php echo htmlspecialchars($imagenGuardada); ?>" download class="btn">
                💾 Descargar Imagen
            </a>
            <a href="index.php?nuevo=1" class="btn btn-secondary">
                🆕 Editar Nueva Imagen
            </a>
            <a href="historial.php" class="btn btn-secondary">
                📜 Ver Historial
            </a>
        </div>

        <p class="info-text">
            Ruta: <?php echo htmlspecialchars($imagenGuardada); ?>
        </p>
    </div>
</div>

</body>
</html>