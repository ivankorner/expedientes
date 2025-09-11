<?php
// Script para probar la validación de números con ceros a la izquierda

echo "<h2>🧪 Prueba de Validación - Números con Ceros a la Izquierda</h2>";

// Función de saneado (copiada del archivo original)
function sanear_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Casos de prueba
$casos_prueba = [
    '0001' => '0001 (con ceros a la izquierda)',
    '1234' => '1234 (número normal)',
    '000123' => '000123 (6 dígitos con ceros)',
    '0' => '0 (cero simple)',
    '00' => '00 (doble cero)',
    '123456' => '123456 (6 dígitos)',
    'abc' => 'abc (letras - debe fallar)',
    '12345a' => '12345a (con letra - debe fallar)',
    '1234567' => '1234567 (7 dígitos - debe fallar)',
    '' => '(vacío - debe fallar)'
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'>";
echo "<th>Valor de Prueba</th><th>Descripción</th><th>Validación Anterior</th><th>Validación Nueva</th><th>Estado</th>";
echo "</tr>";

foreach ($casos_prueba as $valor => $descripcion) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($valor) . "</strong></td>";
    echo "<td>" . htmlspecialchars($descripcion) . "</td>";
    
    // Validación anterior (filter_var)
    $validacion_anterior = filter_var($valor, FILTER_VALIDATE_INT);
    $resultado_anterior = $validacion_anterior !== false ? "✅ Válido ($validacion_anterior)" : "❌ Inválido";
    echo "<td>$resultado_anterior</td>";
    
    // Validación nueva (regex)
    $validacion_nueva = preg_match('/^[0-9]{1,6}$/', $valor);
    $resultado_nuevo = $validacion_nueva ? "✅ Válido" : "❌ Inválido";
    echo "<td>$resultado_nuevo</td>";
    
    // Estado del cambio
    $cambio = "";
    if ($validacion_anterior === false && $validacion_nueva) {
        $cambio = "<span style='color: green;'>🎉 Ahora funciona</span>";
    } elseif ($validacion_anterior !== false && !$validacion_nueva) {
        $cambio = "<span style='color: red;'>⚠️ Ahora falla</span>";
    } elseif ($validacion_anterior !== false && $validacion_nueva) {
        $cambio = "<span style='color: blue;'>✅ Sigue funcionando</span>";
    } else {
        $cambio = "<span style='color: gray;'>❌ Sigue fallando</span>";
    }
    echo "<td>$cambio</td>";
    
    echo "</tr>";
}

echo "</table>";

echo "<h3>📊 Resumen de Cambios:</h3>";
echo "<ul>";
echo "<li><strong>✅ Números con ceros a la izquierda:</strong> Ahora funcionan correctamente (0001, 00123, etc.)</li>";
echo "<li><strong>✅ Números normales:</strong> Siguen funcionando como antes</li>";
echo "<li><strong>❌ Validaciones de seguridad:</strong> Se mantienen (no letras, máximo 6 dígitos)</li>";
echo "</ul>";

echo "<h3>🔧 Cambio Técnico Realizado:</h3>";
echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Antes:</h4>";
echo "<pre>filter_var(\$_POST['numero'], FILTER_VALIDATE_INT)</pre>";
echo "<p>❌ Rechazaba '0001' porque no es un entero válido</p>";

echo "<h4>Después:</h4>";
echo "<pre>preg_match('/^[0-9]{1,6}$/', \$data['numero'])</pre>";
echo "<p>✅ Acepta cualquier secuencia de 1-6 dígitos, incluyendo ceros a la izquierda</p>";
echo "</div>";

echo "<hr>";
echo "<p><a href='carga_expedientes.php'>🔙 Volver a Cargar Expediente</a></p>";
echo "<p><strong>Ahora puedes probar cargar un expediente con números como 0001, 0123, etc.</strong></p>";
?>

<style>
table {
    border-collapse: collapse;
    width: 100%;
    margin: 15px 0;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

pre {
    background-color: #e9ecef;
    padding: 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

a {
    color: #007bff;
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid #007bff;
    border-radius: 4px;
    display: inline-block;
}

a:hover {
    background-color: #007bff;
    color: white;
}
</style>