<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "conexion.php";

echo "Conexión DB: OK<br>";
echo "Sesión activa: " . (isset($_SESSION['imagen_temp']) ? "SÍ" : "NO") . "<br>";

if (isset($_SESSION['imagen_temp'])) {
    echo "Ruta imagen temp: " . $_SESSION['imagen_temp'] . "<br>";
    echo "Archivo existe: " . (file_exists($_SESSION['imagen_temp']) ? "SÍ" : "NO") . "<br>";
}

// Verificar carpetas
echo "<br>Carpeta temp/: " . (is_dir('temp') ? "SÍ" : "NO") . "<br>";
echo "Carpeta uploads/: " . (is_dir('uploads') ? "SÍ" : "NO") . "<br>";
echo "Carpeta resultados/: " . (is_dir('resultados') ? "SÍ" : "NO") . "<br>";

// Verificar preview
$rutaPreview = 'temp/preview_' . session_id() . '.jpg';
echo "<br>Preview esperado: " . $rutaPreview . "<br>";
echo "Preview existe: " . (file_exists($rutaPreview) ? "SÍ" : "NO") . "<br>";

// Probar consulta SQL
$stmt = $conexion->prepare(
    "INSERT INTO ediciones (
        imagen_original, imagen_editada, filtro, ancho, 
        brillo, contraste, saturacion, exposicion,
        tono, temperatura, tinte, desenfoque, nitidez,
        fecha
     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
);

if ($stmt) {
    echo "<br>Preparación SQL: OK";
} else {
    echo "<br>Error SQL: " . $conexion->error;
}
?>
```

Abre `http://localhost:8080/editor-fotos/test_guardar.php` y comparte lo que dice.

---

### **Paso 3: Revisa el log de errores de Apache**

Si usas XAMPP:
```
C:\xampp\apache\logs\error.log