<?php
// Configuración de la conexión a la base de datos PostgreSQL
$host = 'localhost';
$port = '5432'; // Puerto predeterminado de PostgreSQL
$dbname = 'Geca'; // Nombre de tu base de datos
$user = 'postgres'; // Usuario de tu base de datos
$password = 'aqui_va_tu_password'; // Contraseña de tu usuario
$serie = ''; // Inicialización de Serie

// Intenta conectarse a la base de datos
$dbconn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verificar si la conexión fue exitosa
if (!$dbconn) {
    die("Error: No se pudo conectar a la base de datos.");
}

$api_key_valido = 0;
$api_key = isset($_GET['api_key']) ? $_GET['api_key'] : '';

if ($api_key == 'OJs63dp1kjSGQiXJ9ngZ1ImIDVETMf0Y' || $api_key == 'pMX22-BL1CPeHYIMmXNQevd1svh1') {
    $api_key_valido = 1;
}

if ($api_key_valido == 1) {
    $serie = $_GET['serie'];//isset($_GET['serie']) ? $_GET['serie'] : '';
    //echo $serie;

    $sql = "SELECT * FROM inventario WHERE serie='$serie'";
    //echo $sql;
    $equipo = pg_query($dbconn, $sql);
    $num_registro = pg_num_rows($equipo);

    if ($num_registro > 0) {
        for ($i = 0; $i < $num_registro; $i++) {
            $id_inventario = pg_fetch_result($equipo, $i, 'id_inventario');
            $sql_inventario_revision = "SELECT * FROM inventario_revision WHERE serie='$serie'";
            $ya_revisado = pg_query($dbconn, $sql_inventario_revision);
            $num_revisado = pg_num_rows($ya_revisado);

            if ($num_revisado > 0) {
                $sql_ya_revisado = "INSERT INTO inventario_ya_revisado (serie, created_at, created_by) VALUES ('$serie', now(), 'api')";
                pg_query($dbconn, $sql_ya_revisado);
            } else {
                $sql_ya_revisado = "INSERT INTO inventario_revision (serie, encontrado, fecha_verificacion, created_at, created_by, verificado_por) VALUES ('$serie', 1, now(), now(), 'api', 'api')";
                pg_query($dbconn, $sql_ya_revisado);
            }
        }
    }

    if ($num_registro == 0) {
        $sql_inventario_revision = "SELECT * FROM inventario_revision WHERE serie='$serie'";
        $ya_revisado = pg_query($dbconn, $sql_inventario_revision);
        $num_revisado = pg_num_rows($ya_revisado);

        if ($num_revisado > 0) {
            $sql_ya_revisado = "INSERT INTO inventario_ya_revisado (serie, created_at, created_by) VALUES ('$serie', now(), 'api')";
            pg_query($dbconn, $sql_ya_revisado);
        } else {
            $sql_ya_revisado = "INSERT INTO inventario_revision (serie, encontrado, fecha_verificacion, created_at, created_by, verificado_por) VALUES ('$serie', 0, now(), now(), 'api', 'api')";
            pg_query($dbconn, $sql_ya_revisado);
        }
    }

    $datos = ['serie' => $serie, 'encontrado' => $num_registro];
    echo json_encode($datos);
    
} else {
    // La clave de API no es válida
}
?>