<?php
session_start();

// Limpiar sesión antigua si existe
if (isset($_GET['nuevo'])) {
    unset($_SESSION['imagen_temp']);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Fotos Avanzado PHP</title>
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
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .container {
            display: flex;
            height: calc(100vh - 80px);
        }

        /* Sección de carga */
        .upload-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .upload-box {
            background: #2a2a2a;
            padding: 60px;
            border-radius: 20px;
            text-align: center;
            border: 3px dashed #667eea;
            max-width: 500px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-box:hover {
            border-color: #764ba2;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        input[type="file"] {
            display: none;
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
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        /* Editor */
        .editor-section {
            display: none;
            flex: 1;
        }

        .editor-section.active {
            display: flex;
        }

        .image-panel {
            flex: 2;
            padding: 20px;
            overflow: auto;
            background: #252525;
        }

        .image-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .image-box {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 12px;
        }

        .image-box h3 {
            margin-bottom: 10px;
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
        }

        .image-box img {
            width: 100%;
            border-radius: 8px;
            display: block;
        }

        .controls-panel {
            flex: 1;
            background: #2a2a2a;
            padding: 20px;
            overflow-y: auto;
            border-left: 2px solid #333;
        }

        .control-group {
            margin-bottom: 25px;
            background: #1a1a1a;
            padding: 20px;
            border-radius: 12px;
        }

        .control-group h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-item {
            margin-bottom: 15px;
        }

        .control-item label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #999;
        }

        .control-item input[type="range"] {
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #333;
            outline: none;
            -webkit-appearance: none;
        }

        .control-item input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
        }

        .control-item input[type="range"]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            border: none;
        }

        .value-display {
            color: #667eea;
            font-weight: 600;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .filter-btn {
            padding: 10px;
            background: #333;
            border: 2px solid transparent;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 12px;
        }

        .filter-btn:hover {
            border-color: #667eea;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .action-buttons {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #2a2a2a;
            padding: 15px 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
            z-index: 100;
        }

        .btn-reset {
            background: #dc3545;
        }

        .btn-reset:hover {
            background: #c82333;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .spinner {
            border: 3px solid #333;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
                height: auto;
            }

            .image-comparison {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                position: relative;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>🎨 Editor de Fotos Avanzado PHP</h1>
    <p>Ajusta brillo, contraste, saturación y aplica filtros profesionales</p>
</div>

<div class="container">
    <!-- Sección de carga -->
    <div class="upload-section" id="uploadSection">
        <div class="upload-box" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon">📸</div>
            <h2>Sube tu imagen</h2>
            <p>Haz clic o arrastra una imagen aquí</p>
            <p style="font-size: 12px; color: #666;">JPG, PNG, GIF, WebP - Max 5MB</p>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" id="fileInput" name="imagen" accept="image/*" onchange="cargarImagen()">
            </form>
        </div>
    </div>

    <!-- Sección del editor -->
    <div class="editor-section" id="editorSection">
        <div class="image-panel">
            <div class="image-comparison">
                <div class="image-box">
                    <h3>📷 Imagen Original</h3>
                    <img id="imagenOriginal" src="" alt="Original">
                </div>
                <div class="image-box">
                    <h3>✨ Vista Previa</h3>
                    <div id="loadingPreview" class="loading" style="display: none;">
                        <div class="spinner"></div>
                        <p>Procesando...</p>
                    </div>
                    <img id="imagenPreview" src="" alt="Preview">
                </div>
            </div>
        </div>

        <div class="controls-panel">
            <!-- Ajustes básicos -->
            <div class="control-group">
                <h3>⚙️ Ajustes Básicos</h3>
                
                <div class="control-item">
                    <label>
                        <span>Brillo</span>
                        <span class="value-display" id="brilloValue">0</span>
                    </label>
                    <input type="range" id="brillo" min="-100" max="100" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Contraste</span>
                        <span class="value-display" id="contrasteValue">0</span>
                    </label>
                    <input type="range" id="contraste" min="-100" max="100" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Saturación</span>
                        <span class="value-display" id="saturacionValue">0</span>
                    </label>
                    <input type="range" id="saturacion" min="-100" max="100" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Exposición</span>
                        <span class="value-display" id="exposicionValue">0</span>
                    </label>
                    <input type="range" id="exposicion" min="-50" max="50" value="0" oninput="actualizarPreview()">
                </div>
            </div>

            <!-- Tono/Color -->
            <div class="control-group">
                <h3>🎨 Color</h3>
                
                <div class="control-item">
                    <label>
                        <span>Tono (Hue)</span>
                        <span class="value-display" id="hueValue">0</span>
                    </label>
                    <input type="range" id="hue" min="-180" max="180" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Temperatura</span>
                        <span class="value-display" id="temperaturaValue">0</span>
                    </label>
                    <input type="range" id="temperatura" min="-100" max="100" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Tinte</span>
                        <span class="value-display" id="tinteValue">0</span>
                    </label>
                    <input type="range" id="tinte" min="-100" max="100" value="0" oninput="actualizarPreview()">
                </div>
            </div>

            <!-- Detalle -->
            <div class="control-group">
                <h3>🔍 Detalle</h3>
                
                <div class="control-item">
                    <label>
                        <span>Nitidez</span>
                        <span class="value-display" id="nitidezValue">0</span>
                    </label>
                    <input type="range" id="nitidez" min="0" max="100" value="0" oninput="actualizarPreview()">
                </div>

                <div class="control-item">
                    <label>
                        <span>Desenfoque</span>
                        <span class="value-display" id="desenfoqueValue">0</span>
                    </label>
                    <input type="range" id="desenfoque" min="0" max="20" value="0" oninput="actualizarPreview()">
                </div>
            </div>

            <!-- Filtros -->
            <div class="control-group">
                <h3>🎭 Filtros Creativos</h3>
                <div class="filter-grid">
                    <button class="filter-btn active" onclick="seleccionarFiltro('ninguno')">Ninguno</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('bn')">B&N</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('sepia')">Sepia</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('vintage')">Vintage</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('invertir')">Invertir</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('relieve')">Relieve</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('detectarBordes')">Bordes</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('pixelar')">Pixelar</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('posterizar')">Poster</button>
                    <button class="filter-btn" onclick="seleccionarFiltro('acuarela')">Acuarela</button>
                </div>
            </div>

            <!-- Redimensionar -->
            <div class="control-group">
                <h3>📐 Redimensionar</h3>
                <div class="control-item">
                    <label>
                        <span>Ancho (px)</span>
                    </label>
                    <input type="number" id="ancho" placeholder="Dejar vacío para mantener" 
                           style="width: 100%; padding: 10px; background: #333; border: 1px solid #444; color: #fff; border-radius: 6px;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="action-buttons" id="actionButtons" style="display: none;">
    <button class="btn btn-reset" onclick="resetearAjustes()">🔄 Resetear</button>
    <button class="btn" onclick="guardarImagen()">💾 Guardar Imagen</button>
    <button class="btn" onclick="location.href='historial.php'">📜 Ver Historial</button>
</div>

<script>
let ajustesActuales = {
    brillo: 0,
    contraste: 0,
    saturacion: 0,
    exposicion: 0,
    hue: 0,
    temperatura: 0,
    tinte: 0,
    nitidez: 0,
    desenfoque: 0,
    filtro: 'ninguno',
    ancho: ''
};

let timeoutPreview = null;

function cargarImagen() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (!file) return;
    
    // Validar tamaño
    if (file.size > 5 * 1024 * 1024) {
        alert('El archivo es demasiado grande. Máximo 5MB');
        return;
    }
    
    // Validar tipo
    if (!file.type.match('image.*')) {
        alert('Por favor selecciona una imagen válida');
        return;
    }
    
    const formData = new FormData();
    formData.append('imagen', file);
    
    fetch('subir_temp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('imagenOriginal').src = data.ruta + '?' + new Date().getTime();
            document.getElementById('imagenPreview').src = data.ruta + '?' + new Date().getTime();
            
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('editorSection').classList.add('active');
            document.getElementById('actionButtons').style.display = 'flex';
        } else {
            alert('Error al cargar imagen: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error al cargar la imagen');
        console.error(error);
    });
}

function actualizarPreview() {
    // Actualizar valores visuales
    document.getElementById('brilloValue').textContent = document.getElementById('brillo').value;
    document.getElementById('contrasteValue').textContent = document.getElementById('contraste').value;
    document.getElementById('saturacionValue').textContent = document.getElementById('saturacion').value;
    document.getElementById('exposicionValue').textContent = document.getElementById('exposicion').value;
    document.getElementById('hueValue').textContent = document.getElementById('hue').value;
    document.getElementById('temperaturaValue').textContent = document.getElementById('temperatura').value;
    document.getElementById('tinteValue').textContent = document.getElementById('tinte').value;
    document.getElementById('nitidezValue').textContent = document.getElementById('nitidez').value;
    document.getElementById('desenfoqueValue').textContent = document.getElementById('desenfoque').value;
    
    // Recopilar ajustes
    ajustesActuales = {
        brillo: document.getElementById('brillo').value,
        contraste: document.getElementById('contraste').value,
        saturacion: document.getElementById('saturacion').value,
        exposicion: document.getElementById('exposicion').value,
        hue: document.getElementById('hue').value,
        temperatura: document.getElementById('temperatura').value,
        tinte: document.getElementById('tinte').value,
        nitidez: document.getElementById('nitidez').value,
        desenfoque: document.getElementById('desenfoque').value,
        filtro: ajustesActuales.filtro,
        ancho: document.getElementById('ancho').value
    };
    
    // Debounce para no saturar el servidor
    clearTimeout(timeoutPreview);
    timeoutPreview = setTimeout(() => {
        aplicarPreview();
    }, 300);
}

function aplicarPreview() {
    const loading = document.getElementById('loadingPreview');
    const preview = document.getElementById('imagenPreview');
    
    loading.style.display = 'block';
    preview.style.opacity = '0.3';
    
    fetch('preview.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(ajustesActuales)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preview.src = data.ruta + '?' + new Date().getTime();
            preview.style.opacity = '1';
        } else {
            alert('Error en preview: ' + data.error);
        }
        loading.style.display = 'none';
    })
    .catch(error => {
        console.error('Error:', error);
        loading.style.display = 'none';
        preview.style.opacity = '1';
    });
}

function seleccionarFiltro(filtro) {
    // Actualizar UI
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    ajustesActuales.filtro = filtro;
    aplicarPreview();
}

function resetearAjustes() {
    document.getElementById('brillo').value = 0;
    document.getElementById('contraste').value = 0;
    document.getElementById('saturacion').value = 0;
    document.getElementById('exposicion').value = 0;
    document.getElementById('hue').value = 0;
    document.getElementById('temperatura').value = 0;
    document.getElementById('tinte').value = 0;
    document.getElementById('nitidez').value = 0;
    document.getElementById('desenfoque').value = 0;
    document.getElementById('ancho').value = '';
    
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.filter-btn').classList.add('active');
    
    ajustesActuales.filtro = 'ninguno';
    actualizarPreview();
}

function guardarImagen() {
    if (!confirm('¿Guardar la imagen con estos ajustes?')) return;
    
    fetch('guardar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(ajustesActuales)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Imagen guardada exitosamente!');
            window.location.href = 'resultado.php?img=' + encodeURIComponent(data.ruta);
        } else {
            alert('Error al guardar: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error al guardar la imagen');
        console.error(error);
    });
}
</script>

</body>
</html>