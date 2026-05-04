export function loadingAlert() {
    Swal.fire({
        title: "Cargando",
        html: "Espera un momento...",
        timerProgressBar: true,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
          },
    })
}

export function successAlert(message) {
    return Swal.fire({
        icon: 'success',
        title: 'Completado',
        text: message,
    })
}

export function errorAlert(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
    })
}

export function infoAlert(message) {
    Swal.fire({
        icon: 'info',
        title: 'Información',
        text: message,
    })
}

export function loadingSpinner(show, text = 'Buscando usuarios de Microsoft...') {
    if (show) {
        return `
            <div class="d-flex align-items-center py-2">
                <strong role="status" class="text-primary">${text}</strong>
                <div class="spinner-border ms-auto text-primary" aria-hidden="true"></div>
            </div>
        `;
    }
    return "";
}


export function confirmAlert(message, confirmButtonText, cancelButtonText, confirmCallback) {
    Swal.fire({
        title: '¿Estás seguro de realizar esta acción?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
    }).then((result) => {
        if (result.isConfirmed) {
            confirmCallback();
        }
    });
}

export function confirmCloseSession(message, confirmButtonText, cancelButtonText, confirmCallback) {
    Swal.fire({
        title: 'Cerrar sesión',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
    }).then((result) => {
        if (result.isConfirmed) {
            confirmCallback();
        }
    });
}