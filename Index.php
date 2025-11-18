<?php

session_start();

$usuarios = [
    "juan" => "Clave123",
    "maria" => "Pass2024",
    "admin" => "Admin123"
];

function normalizar_usuario($usuario) {
    $usuario = trim($usuario);
    if (strpos($usuario, '@') !== false) {
        $usuario = explode('@', $usuario)[0];
    }
    return preg_replace('/[^A-Za-z0-9._-]/', '', $usuario);
}

function validar_contraseña($clave) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,12}$/', $clave);
}

$mensaje = "";
$error = "";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['login'])) {
    $usuario = normalizar_usuario($_POST['usuario']);
    $clave = $_POST['clave'];
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // ⚠️ Usa tu CLAVE SECRETA real aquí
    $secret = "6LfOUOMrAAAAAMkLFiW7HbSd-QeNOrjLlKzqSQsv
";

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret,
        'response' => $recaptcha_response
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $verificar = file_get_contents($url, false, $context);
    $respuesta = json_decode($verificar);

    if (empty($respuesta->success)) {
        $error = "Por favor completa el reCAPTCHA correctamente.";
    } elseif (!isset($usuarios[$usuario])) {
        $error = "El usuario no existe.";
    } elseif (!validar_contraseña($clave)) {
        $error = "Contraseña inválida (8–12 caracteres, mayúscula, minúscula y número).";
    } elseif ($usuarios[$usuario] !== $clave) {
        $error = "Contraseña incorrecta.";
    } else {
        $_SESSION['login'] = true;
        $_SESSION['nombre'] = $usuario;
        $mensaje = "Bienvenido, $usuario.";
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login PHP simple</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<img src="back.jpeg" alt="Sakuratruck">
<div class="container">
    <h2>Sakura Express </h2>
    <?php
if ($error != "") {
    echo "<p style='color:red;'>$error</p>";
}
if ($mensaje != "") {
    echo "<p style='color:green;'>$mensaje</p>";
}
?>

<?php if (!empty($_SESSION['login'])): ?>
    
        
        <p>Has iniciado sesión como <b><?php echo htmlspecialchars($_SESSION['nombre']); ?></b></p>
        <p><a href="?logout=1">Cerrar sesión</a></p>

        <?php else: ?>

        <form method="post">
            <div class="usuario">
                <input type="text" name="usuario" required placeholder=" Usuario o Correo"><br><br>
            </div>
            <div class="password">
                <input type="password" name="clave" required placeholder="Contraseña">
            </div>
            
            <div class="g-recaptcha" data-sitekey="6LfOUOMrAAAAACM9fl9h61FwQKU7Hwxwx6D4bU5A"></div>
            <button type="submit" name="login">Iniciar sesión</button>
        </form>
<div class="ejemplos"><p>Usuarios disponibles para probar:</p>
<ul>
    <li>juan / Clave123</li>
    <li>maria / Pass2024</li>
    <li>admin / Admin123</li>
</ul>   </div>


<?php endif; ?>

</div>

        
</body>
</html>

