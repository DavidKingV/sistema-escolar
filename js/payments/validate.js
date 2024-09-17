$("#monthAmountForm").validate({
    rules: {
        studentAmount: {
            required: true,
            number: true,
            min: 2
        }
    },
    messages: {
        studentAmount: {
            required: "Por favor ingrese un monto",
            number: "Ingrese solo números",
            min: "El precio puede ser demasiado bajo"
        }
    },
    errorPlacement: function(error, element) {
        var elementId = element.attr("id");
        error.insertAfter($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
    }
});