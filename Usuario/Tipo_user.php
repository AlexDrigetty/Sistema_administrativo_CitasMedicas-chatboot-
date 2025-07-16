<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/inicio.css">
</head>

<body>


    <div class="container">
        <h4 class="mb-3">¿Eres un paciente nuevo o registrado?</h4>
        <p class="text-muted mb-5">Selecciona tu tipo de usuario para continuar con la atención médica</p>

        <div class="box-botons mb-3">
            <div class="card">
                <h4 class="mb-3"><i class="bi bi-person-add"></i> Paciente Nuevo</h4>
                <p class="text-muted mb-4">Si es tu primera vez usando nuestro sistema médico, registrate solo usando tu dni</p>
                <a href="../Usuario/registro.php"><i class="bi bi-arrow-right "> </i> Registrarse</a>
            </div>
            <div class="card">
                <h4 class="mb-3"><i class="bi bi-person-check"></i> Paciente Registrado</h4>
                <p class="text-muted">Si ya tienes una cuenta en nuestro sistema, accede a tus beneficios</p>

                <a href="../Usuario/login.php"><i class="bi bi-arrow-right "> </i> Iniciar</a>
            </div>
        </div>

        <a href="../Inicio.php" class="menu"><i class="fa-solid fa-reply"></i> Regresar</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>
</body>

</html>