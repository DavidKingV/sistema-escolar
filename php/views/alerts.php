<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert</title>
    <!-- Incluye la biblioteca SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script type="text/javascript">
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No tienes permisos de administrador",
            backdrop: "rgba(37,54,166,10)",
            showConfirmButton: false,
            timer: 4000
        }).then(function() {
            window.location.href = "index.php?admin=false";
        });
    </script>
</body>
</html>
