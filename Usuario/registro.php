<?php include '../bd/conexion.php'; ?>
<?php
session_start();
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="../css/formularios.css">
</head>

<body>
    <div class="container">
        <form action="../funciones/registro_proceso.php" method="post" id="registroUsuario">
            <h3 class="py-4"> <i class="fa fa-user-lock"></i>REGISTRO DE USUARIO</h3>

            <div class="progreso-container mb-5">
                <div class="progress-bar" id="progreso"></div>
            </div>

            <div class="buscador_dni mb-3">
                <div class="box-1 mb-3">
                    <label class="form-label">DNI</label>
                    <div class="box-input">
                        <input type="text" class="form-control" id="dni" name="dni" placeholder="DNI" required>
                        <i class="bi bi-card-text"></i>
                    </div>
                </div>
                <div class="search mb-3">
                    <button type="button" class="buscar" id="buscador"><i class="bi bi-search"></i> <span class="text-buscar"> Buscar</span></button>
                </div>
            </div>

            <div class="box-form mb-3">
                <label class="form-label">Nombre</label>
                <div class="box-input">
                    <input type="text" class="form-control mb-3" id="nombre" name="nombre" placeholder="Nombre" required>
                    <i class="bi bi-person"></i>
                </div>
            </div>
            <div class="box-form mb-3">
                <label for="">Apellido</label>
                <div class="box-input">
                    <input type="text" class="form-control mb-3" id="apellido" name="apellido" placeholder="Apellido" required>
                    <i class="bi bi-person"></i>
                </div>
            </div>
            <div class="box-form mb-3">
                <label for="">Dirección</label>
                <div class="box-input">
                    <input type="text" class="form-control mb-3" id="direccion" name="direccion" placeholder="Dirección" required>
                    <i class="bi bi-geo-alt"></i>
                </div>
            </div>
            <div class="box-form mb-3">
                <label for="">Correo</label>
                <div class="box-input">
                    <input type="text" class="form-control mb-3" id="correo" name="correo" placeholder="Correo" required>
                    <i class="bi bi-envelope"></i>
                </div>
            </div>

            <div class="register mb-3">
                <button type="submit" class="btn btn-primary"> <i class="bi bi-person-plus"></i>Registrar</button>
            </div>
        </form>

        <a href="../Usuario/Tipo_user.php" class="menu"> <i class="fa-solid fa-reply"></i>Regresar</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('#registroUsuario');
            const inputs = form.querySelectorAll('input');
            const progreso = document.querySelector('#progreso');
            const dni = document.querySelector('#dni');
            const buscar = document.querySelector('#buscador');

            function rellenoProgreso() {
                let completados = 0;

                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        completados++;
                    }
                });

                const barra = (completados / inputs.length) * 100;
                progreso.style.width = `${barra}%`;
                if (barra < 30) {
                    progreso.style.backgroundColor = '#e74c3c';
                } else if (barra < 70) {
                    progreso.style.backgroundColor = '#f39c12';
                } else {
                    progreso.style.backgroundColor = '#27ae60';
                }
            }

            inputs.forEach(input => {
                input.addEventListener('input', rellenoProgreso);
            });

            buscar.addEventListener("click", () => {
                const casillero_dni = dni.value.trim();
                if (casillero_dni === '') {
                    alert('No se ha ingresado un DNI válido');
                    return;
                }

                const texto = document.querySelector('.text-buscar');
                const icono = document.querySelector('.buscar i');
                icono.classList.remove('bi-search');
                texto.textContent = 'Buscando...';

                fetch(`../funciones/buscar_paciente.php?dni=${encodeURIComponent(casillero_dni)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else if (data.success) {
                            document.querySelector('#nombre').value = data.data.nombre;
                            document.querySelector('#apellido').value = data.data.apellido;
                            document.querySelector('#direccion').value = data.data.direccion;
                            document.querySelector('#correo').value = data.data.email;
                            rellenoProgreso();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al buscar el paciente');
                    })
                    .finally(() => {
                        texto.innerHTML = '<i class="bi bi-search"></i> <span class="text-buscar"> Buscar</span>';
                    });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>

</body>
</html>