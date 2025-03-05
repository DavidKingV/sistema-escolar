export function FormattedSessionInput(input) {
    let value = $(input).val(); 
    let formatted = value.replace(/[^0-9]/g, '') // Remover cualquier carácter que no sea número
                        .substring(0, 4) // Limitar a 4 dígitos (MMDD)
                        .replace(/(\d{2})(\d+)/, '$1/$2'); // Insertar el slash después de dos dígitos

    $(input).val(formatted); // Establecer el valor formateado

    // Manejar la eliminación del slash si se borran los dígitos
    if (formatted.length < 3) {
        $(this).val(formatted.replace('/', ''));
    }
}