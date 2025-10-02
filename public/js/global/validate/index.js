export function validateForm(formId, rules, messages) {
    $(formId).validate({
        rules: rules,
        messages: messages,
        errorElement: 'span', // Opcional: Puedes definir el elemento de error
        errorClass: 'badge text-bg-danger', // Opcional: Agregar una clase personalizada
        highlight: function(element) {
            $(element).closest('.form-control').addClass('has-error'); // Añade la clase a la estructura que contiene el input
            $(element).addClass('input-error'); // Añade una clase al input para darle borde rojo
        },
        unhighlight: function(element) {
            $(element).closest('.form-control').removeClass('has-error'); // Remueve la clase si el campo es válido
            $(element).removeClass('input-error'); // Remueve la clase del input si el campo es válido
        }
    });

    $.validator.addMethod("valueNotEquals", function(value, element, arg){
        return arg !== value;
    }, "Por favor, selecciona una opción");

    $.validator.addMethod("noSpace", function(value, element) {
        return this.optional(element) || (value.trim().indexOf(" ") === -1);
    }, "El campo no debe contener espacios");
}

export function capitalizeFirstLetter(input) {
    if(!input) return input;
    input = String(input); // Asegurar que sea una cadena de texto
    return input.charAt(0).toUpperCase() + input.slice(1);
}

export function capitalizeAll(input) {
    if(!input) return input;
    input = String(input); // Asegurar que sea una cadena de texto
    return input.toUpperCase();
}

export function capitalizeAllWords(input) {
    if(!input) return input;
    input = String(input); // Asegurar que sea una cadena de texto
    return input.replace(/\b\w/g, l => l.toUpperCase());
}


export function inputLowerCase(input) {
    if(!input) return input;
    return input.toLowerCase();
}


