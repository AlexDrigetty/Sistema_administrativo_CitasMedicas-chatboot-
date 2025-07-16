<?php 
function conectarDB() {
    $host = 'localhost'; // 
    $dbname = 'prioriza';
    $user = 'root';
    $pass = 'master.';   
    
    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Log del error (en producción)
        error_log("Error de conexión: " . $e->getMessage());
        
        // Mensaje amigable
        die("No se pudo conectar a la base de datos. Por favor, intente más tarde.");
    }
}
?>

<?php
// // bd/conexion.php
// $host = 'localhost';
// $dbname = 'priorizanow';
// $username = 'root';
// $password = 'master.';

// try {
//     $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
//     $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Error de conexión: " . $e->getMessage());
// }
?>