<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar los datos del formulario
    $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mensaje = filter_var(trim($_POST['mensaje']), FILTER_SANITIZE_STRING);

    // Validar datos
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }

    // Dirección de correo a la que quieres recibir los mensajes
    $destinatario = "consultas@amcop.com.ar"; // Cambia esta línea por la dirección de correo a la que deseas recibir los mensajes

    // Asunto del correo
    $asunto = "Mensaje de contacto desde mi sitio web";

    // Construir el cuerpo del mensaje
    $cuerpoMensaje = "Nombre: $nombre\n";
    $cuerpoMensaje .= "Email: $email\n";
    $cuerpoMensaje .= "Mensaje:\n$mensaje\n";

    // Cabeceras del correo
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Intentar enviar el correo
    if (mail($destinatario, $asunto, $cuerpoMensaje, $headers)) {
        echo "¡Correo enviado correctamente!";
    } else {
        echo "Error al enviar el correo.";
    }
} else {
    echo "Método de solicitud no válido.";
}
?>
