export function enviarPeticionAjax(url, metodo, datos) {
    return $.ajax({
        url: url,
        type: metodo,
        data: datos
    });
}

export function enviarPeticionAjaxAction(url, metodo, action, data) {
    return $.ajax({
        url: url,
        type: metodo,
        data: {action: action, data: data}
    });
}