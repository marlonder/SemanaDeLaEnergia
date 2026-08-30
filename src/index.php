<?php
echo "<h1>¡Funciona! 🚀</h1>";
echo "<p>PHP version: " . phpversion() . "</p>";

try {
    $pdo = new PDO(
        "mysql:host=db;dbname=" . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    echo "<p style='color:green'>Conexión a MySQL: OK</p>";
} catch (PDOException $e) {
    echo "<p style='color:orange'>Aún sin conexión a MySQL (normal si no configuraste .env dentro del contenedor app): " . $e->getMessage() . "</p>";
}
