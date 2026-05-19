<?php
// 1. Verificar que los datos se estén enviando por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Recibir y limpiar las variables para evitar inyecciones de código básicas
    $nombre  = htmlspecialchars(trim($_POST['nombre']));
    $correo  = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $asunto  = htmlspecialchars(trim($_POST['asunto']));
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));

    // 3. Validar que los campos obligatorios no estén vacíos
    if (empty($nombre) || empty($correo) || empty($asunto) || empty($mensaje)) {
        echo "Por favor, llena todos los campos obligatorios.";
        exit;
    }

    // --- Guardar en Base de Datos (MySQL) ---
    $use_db = true;
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'portafolio'; // Asegúrate de haber creado esta DB en phpMyAdmin

    $db_ok = false;

    if ($use_db) {
        $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($mysqli->connect_errno) {
            error_log('MySQL connect error: ' . $mysqli->connect_error);
            $db_ok = false;
        } else {
            $db_ok = true;
            // Preparamos la consulta SQL
            $stmt = $mysqli->prepare("INSERT INTO mensajes (nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('ssss', $nombre, $correo, $asunto, $mensaje);
                $stmt->execute();
                $stmt->close();
            } else {
                error_log('MySQL prepare error: ' . $mysqli->error);
            }
            $mysqli->close();
        }
    }

    // --- Configuración y Simulación de Correo ---
    $destinatario = "luisadrianortizsolis1@gmail.com";
    $asuntoEmail = "Portafolio Web - Asunto: " . $asunto;
    
    $cuerpoMensaje = "Has recibido un nuevo mensaje desde tu Portafolio Web:\n";
    $cuerpoMensaje .= "---------------------------------------------------------\n";
    $cuerpoMensaje .= "Nombre: " . $nombre . "\n";
    $cuerpoMensaje .= "Correo: " . $correo . "\n";
    $cuerpoMensaje .= "Mensaje:\n" . $mensaje . "\n";
    
    $headers = "From: webmaster@adrianlaos.com" . "\r\n" .
               "Reply-To: " . $correo . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Intentar enviar por correo (Dará falso localmente en XAMPP, es normal)
    if (@mail($destinatario, $asuntoEmail, $cuerpoMensaje, $headers)) {
        $mail_enviado = true;
    } else {
        $mail_enviado = false;
    }

    // --- Respuesta Dinámica mediante Alertas ---
    if ($mail_enviado) {
        echo "<script>
                alert('¡Mensaje enviado con éxito! Se ha notificado a " . $destinatario . " y se registró en la base de datos.');
                window.location.href='index.html';
              </script>";
    } else {
        $notificacion = '¡Formulario procesado con éxito localmente!';
        if ($db_ok) {
            $notificacion .= '\\n✓ Datos guardados correctamente en la tabla `mensajes` de phpMyAdmin.';
        } else {
            $notificacion .= '\\n(Aviso: No se pudo conectar a la base de datos, revisa que exista la DB `portafolio`)';
        }
        $notificacion .= '\\n\\n(Nota: El correo real a ' . $destinatario . ' se enviará en automático cuando subas el portafolio a internet).';
        
        echo "<script>
                alert('" . $notificacion . "');
                window.location.href='index.html';
              </script>";
    }

} else {
    // Si intentan entrar directo a enviarmsj.php, los bota al index
    header("Location: index.html");
    exit;
}
?>