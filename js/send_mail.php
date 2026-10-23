<?php
// Formulario de contacto (contacto.html). La pagina lo manda por detras (js/contacto.js) y
// muestra la respuesta en el mismo formulario; si el navegador no corre JavaScript, el
// formulario se envia como siempre y se ve el texto de la respuesta.

$destinatario = "consultas@amcop.com.ar";
$asunto = "Mensaje de contacto desde mi sitio web";

// Si lo pide la pagina, la respuesta va en JSON: { "ok": true|false, "mensaje": "..." }
$quiereJson = strpos($_SERVER["HTTP_ACCEPT"] ?? "", "application/json") !== false;

function responder($ok, $mensaje, $codigo = 200)
{
    global $quiereJson;
    http_response_code($codigo);
    if ($quiereJson) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(["ok" => $ok, "mensaje" => $mensaje], JSON_UNESCAPED_UNICODE);
    } else {
        header("Content-Type: text/plain; charset=UTF-8");
        echo $mensaje;
    }
    exit;
}

// Texto del formulario: sin espacios de mas, sin etiquetas HTML y con un largo maximo
function campo($nombre, $largo)
{
    $valor = trim(strip_tags((string) ($_POST[$nombre] ?? "")));
    return function_exists("mb_substr") ? mb_substr($valor, 0, $largo, "UTF-8") : substr($valor, 0, $largo);
}

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    responder(false, "Método de solicitud no válido.", 405);
}

// Sin saltos de linea: el nombre y el email van en las cabeceras del correo
$nombre = str_replace(["\r", "\n"], " ", campo("nombre", 100));
$email = filter_var(campo("email", 200), FILTER_SANITIZE_EMAIL);
$mensaje = campo("mensaje", 5000);

if ($nombre === "" || $email === "" || $mensaje === "") {
    responder(false, "Todos los campos son obligatorios.", 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, "Correo electrónico no válido.", 400);
}

$cuerpoMensaje = "Nombre: $nombre\n";
$cuerpoMensaje .= "Email: $email\n";
$cuerpoMensaje .= "Mensaje:\n$mensaje\n";

// Sale desde la casilla del local (un correo "desde" la direccion del cliente puede terminar en
// spam o ser rechazado); al tocar "Responder" se le contesta al cliente
$cabeceras = "From: Web AMCOP <$destinatario>\r\n";
$cabeceras .= "Reply-To: $email\r\n";
$cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($destinatario, $asunto, $cuerpoMensaje, $cabeceras)) {
    responder(true, "¡Gracias! Recibimos tu consulta y te respondemos a la brevedad.");
}
responder(false, "No pudimos enviar tu consulta. Probá de nuevo en unos minutos o escribinos por WhatsApp.", 500);
