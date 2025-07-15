<?php
echo "<h1>🎉 PHP funciona correctamente!</h1>";
echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Ubicación:</strong> " . __DIR__ . "</p>";

// Verificar que CodeIgniter existe
if (file_exists('index.php')) {
    echo "<p>✅ index.php encontrado</p>";
} else {
    echo "<p>❌ index.php NO encontrado</p>";
}

if (is_dir('application')) {
    echo "<p>✅ Carpeta application encontrada</p>";
} else {
    echo "<p>❌ Carpeta application NO encontrada</p>";
}

if (is_dir('system')) {
    echo "<p>✅ Carpeta system encontrada</p>";
} else {
    echo "<p>❌ Carpeta system NO encontrada</p>";
}
?>