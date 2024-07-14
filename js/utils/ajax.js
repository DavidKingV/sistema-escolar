export function enviarPeticionAjax(url, metodo, datos) {
    return $.ajax({
        url: url,
        type: metodo,
        data: datos
    });
}