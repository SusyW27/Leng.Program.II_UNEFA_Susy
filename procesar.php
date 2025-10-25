<?php
// Configuración de la Base de Datos (ajusta si usas otra contraseña o nombre de BD)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Contraseña de XAMPP por defecto
define('DB_NAME', 'empresa_validaciones'); 

// Regla de negocio
define('LONGITUD_REQUERIDA', 8);

$codigos_validos = [];
$codigos_invalidos = [];
$inserciones_exitosas = 0;

// ----------------------------------------------------
// PASO 1: CONEXIÓN A LA BASE DE DATOS
// ----------------------------------------------------
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión a la BD: " . $conn->connect_error);
}

// Configuración de estilos para la respuesta HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de la Validación y BD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: \'Inter\', sans-serif; background-color: #f4f7f9; }
        .container { max-width: 600px; margin: 50px auto; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="container bg-white p-8 rounded-xl shadow-lg">';

echo "<h2 class='text-3xl font-bold mb-6 text-gray-800'>Validación e Inserción en BD</h2>";

if (isset($_POST['codigos']) && is_array($_POST['codigos'])) {
    $codigos_a_validar = $_POST['codigos'];

    echo "<ul class='space-y-3 mb-6'>";

    // ----------------------------------------------------
    // PASO 2: CICLO, VALIDACIÓN Y SANITIZACIÓN
    // ----------------------------------------------------
    foreach ($codigos_a_validar as $codigo) {
        $codigo = trim($codigo); // Sanitización ligera
        
        // CONDICIONAL: Regla de Negocio
        if (strlen($codigo) === LONGITUD_REQUERIDA) {
            
            // Sanitización de Seguridad (prevención de Inyección SQL)
            $codigo_sanitizado = $conn->real_escape_string($codigo);
            $codigos_validos[] = $codigo_sanitizado;
            
            // NOTA: Asumimos que la regla de validación (regla_id) es 1 para este ejemplo.
            $regla_id = 1; 

            // ----------------------------------------------------
            // PASO 3: INSERCIÓN EN LA BD
            // ----------------------------------------------------
            $sql = "INSERT INTO PRODUCTOS (codigo_producto, nombre, regla_id) VALUES ('$codigo_sanitizado', 'Producto Genérico', $regla_id)";
            
            if ($conn->query($sql) === TRUE) {
                $inserciones_exitosas++;
                echo "<li class='p-3 bg-green-50 rounded-lg text-green-700 border-l-4 border-green-500'>
                        ✅ Válido e insertado en BD: <strong>" . htmlspecialchars($codigo) . "</strong> (ID: " . $conn->insert_id . ")
                      </li>";
            } else {
                // Manejo de errores específicos de la BD (ej: código duplicado)
                echo "<li class='p-3 bg-yellow-50 rounded-lg text-yellow-700 border-l-4 border-yellow-500'>
                        ⚠️ Válido, pero fallo de inserción (¿duplicado?): <strong>" . htmlspecialchars($codigo) . "</strong>. Error: " . $conn->error . "
                      </li>";
            }

        } else {
            // Código inválido (no pasa la regla de negocio)
            $codigos_invalidos[] = $codigo;
            echo "<li class='p-3 bg-red-50 rounded-lg text-red-700 border-l-4 border-red-500'>
                    ❌ Inválido (Rechazado): <strong>" . htmlspecialchars($codigo) . "</strong> (Longitud incorrecta).
                  </li>";
        }
    }
    echo "</ul>";
    
    echo "<h3 class='text-xl font-semibold mt-8 mb-3 text-gray-800'>Resumen:</h3>";
    echo "<p class='text-gray-700'>Se validaron " . count($codigos_a_validar) . " códigos. <strong>$inserciones_exitosas</strong> fueron insertados exitosamente.</p>";

} else {
    echo "<p class='text-red-500'>Error: No se recibieron datos para validar.</p>";
}

// Cerrar la conexión
$conn->close();

echo '<br><a href="index.html" class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-150">← Volver al Formulario</a>
    </div>
</body>
</html>';
?>
