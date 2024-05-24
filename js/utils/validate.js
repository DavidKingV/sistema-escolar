/*import { VerifyUser } from './../students/index.js';*/

$("#controlNumber, #studentName, #studentCurp, #teacherName").on("input", function(event) {
    event.preventDefault();
    var cursorPosition = $(this).prop('selectionStart');
        
    // Convertir el valor del input a mayúsculas
    $(this).val($(this).val().toUpperCase());
    
    // Restaurar la posición del cursor después de la modificación
    $(this).prop('selectionStart', cursorPosition);
    $(this).prop('selectionEnd', cursorPosition);
});

$("#studentNation").on("input", function(event) {
    event.preventDefault();
    var cursorPosition = $(this).prop('selectionStart');

    // Obtener el valor actual del input
    var inputValue = $(this).val();

    // Función para convertir solo la primera letra a mayúscula
    function capitalizeFirstLetter(string) {
        if (!string) return string; // Maneja cadenas vacías o nulas
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }

    // Convertir el valor del input con la primera letra en mayúscula
    var capitalizedValue = capitalizeFirstLetter(inputValue);

    // Establecer el valor modificado en el input
    $(this).val(capitalizedValue);
    
    // Restaurar la posición del cursor después de la modificación
    $(this).prop('selectionStart', cursorPosition);
    $(this).prop('selectionEnd', cursorPosition);
});

$("#updateStudent").validate({
	rules: {
        controlNumber: {
            required: true,
            minlength: 4,
            specialChars: true
        },
		studentName: {
			required: true,
            lettersonly: true
		},
        studentGender: {
            required: true,
            valueNotEquals: "0"
        },
        studentBirthday: {
            required: true,
            date: true
        },
        studentState: {
            required: true,
            valueNotEquals: "0"
        },
        studentNation: {
            required: true,
            lettersonly: true
        },
        studentCurp: {
            required: true,
            minlength: 18,
            maxlength: 18
        },
        studentPhone: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12
        },
        studentEmail: {
            required: true,
            email: true
        }

	}, messages: {
        controlNumber: {
            required: "Por favor, ingresa tu número de control",
            minlength: "El número de control debe tener al menos 4 caracteres"
        },
        studentName: {
            required: "Por favor, ingresa un nombre",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentGender: {
            required: "Por favor, selecciona un género"
        },
        studentBirthday: {
            required: "Por favor, ingresa tu fecha de nacimiento",
            date: "Por favor, ingresa una fecha válida"
        },
        studentState: {
            required: "Por favor, ingresa tu estado civil",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentNation: {
            required: "Por favor, ingresa tu nacionalidad",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentCurp: {
            required: "Por favor, ingresa tu CURP",
            minlength: "El CURP debe tener 18 caracteres",
            maxlength: "El CURP debe tener 18 caracteres"
        },
        studentPhone: {
            required: "Por favor, ingresa tu número de teléfono",
            number: "Por favor, ingresa solo números",
            minlength: "El número de teléfono debe tener al menos 10 caracteres",
            maxlength: "El número de teléfono debe tener 12 caracteres"
        },
        studentEmail: {
            required: "Por favor, ingresa tu correo electrónico",
            email: "Por favor, ingresa un correo electrónico válido"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
    
});

$("#addStudents").validate({
	rules: {
        controlNumber: {
            required: true,
            minlength: 4,
            specialChars: true
        },
		studentName: {
			required: true,
            lettersonly: true
		},
        studentGender: {
            required: true,
            valueNotEquals: "0"
        },
        studentBirthday: {
            required: true,
            date: true
        },
        studentState: {
            required: true,
            valueNotEquals: "0"
        },
        studentNation: {
            required: true,
            lettersonly: true
        },
        studentCurp: {
            required: true,
            minlength: 18,
            maxlength: 18
        },
        studentPhone: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12
        },
        studentEmail: {
            required: true,
            email: true
        }

	}, messages: {
        controlNumber: {
            required: "Por favor, ingresa tu número de control",
            minlength: "El número de control debe tener al menos 4 caracteres"
        },
        studentName: {
            required: "Por favor, ingresa un nombre",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentGender: {
            required: "Por favor, selecciona un género"
        },
        studentBirthday: {
            required: "Por favor, ingresa tu fecha de nacimiento",
            date: "Por favor, ingresa una fecha válida"
        },
        studentState: {
            required: "Por favor, ingresa tu estado civil",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentNation: {
            required: "Por favor, ingresa tu nacionalidad",
            lettersonly: "Por favor, ingresa solo letras"
        },
        studentCurp: {
            required: "Por favor, ingresa tu CURP",
            minlength: "El CURP debe tener 18 caracteres",
            maxlength: "El CURP debe tener 18 caracteres"
        },
        studentPhone: {
            required: "Por favor, ingresa tu número de teléfono",
            number: "Por favor, ingresa solo números",
            minlength: "El número de teléfono debe tener al menos 10 caracteres",
            maxlength: "El número de teléfono debe tener 12 caracteres"
        },
        studentEmail: {
            required: "Por favor, ingresa tu correo electrónico",
            email: "Por favor, ingresa un correo electrónico válido"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
    
});

$("#addStudentsUsers").validate({
    rules:{
        studentUserAdd: {
            required: true,
            minlength: 4,
        },
        studentUserPass: {
            required: true,
            minlength: 8,
        }
    }, messages: {
        studentUserAdd: {
            required: "Por favor, ingresa un usuario",
        },
        studentUserPass: {
            required: "Por favor, ingresa una contraseña",
            minlength: "La contraseña debe tener al menos 8 caracteres"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});

$("#editStudentsUsers").validate({
    rules:{
        studentUserAddEdit: {
            required: true,
            minlength: 4,
        },
        studentUserPassEdit: {
            required: true,
            minlength: 8,
        }
    }, messages: {
        studentUserAddEdit: {
            required: "Por favor, ingresa un usuario",
        },
        studentUserPassEdit: {
            required: "Por favor, ingresa una contraseña",
            minlength: "La contraseña debe tener al menos 8 caracteres"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});

$("#editTeacherForm").validate({
    rules: {
        teacherGenderEdit: {
            required: true,
            valueNotEquals: "0"
        },
        teacherBirthdayEdit: {
            required: true,
            date: true
        },
        teacherStateEdit: {
            required: true,
            valueNotEquals: "0"
        },
        teacherPhoneEdit: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12
        },
        teacherEmailEdit: {
            required: true,
            email: true
        }
    }, messages: {
        teacherGenderEdit: {
            required: "Por favor, ingresa un nombre",
        },
        teacherBirthdayEdit: {
            required: "Por favor, ingresa tu fecha de nacimiento",
            date: "Por favor, ingresa una fecha válida"
        },
        teacherStateEdit: {
            required: "Por favor, ingresa tu estado civil"
        },
        teacherPhoneEdit: {
            required: "Por favor ingresa tu número de teléfono",
            number: "Por favor, ingresa solo números",
            minlength: "El número de teléfono debe tener al menos 10 caracteres",
            maxlength: "El número de teléfono debe tener 12 caracteres"
        },
        teacherEmailEdit: {
            required: "Por favor, ingresa tu correo electrónico",
            email: "Por favor, ingresa un correo electrónico válido"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});

$("#addTeachers").validate({
    rules: {
        teacherName: {
            required: true,
            lettersonly: true,
            minlength: 4,
        },
        teacherGender: {
            required: true,
            valueNotEquals: "0"
        },
        teacherBirthday: {
            required: true,
            date: true
        },
        teacherState: {
            required: true,
            valueNotEquals: "0"
        },
        teacherPhone: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12
        },
        teacherEmail: {
            required: true,
            email: true
        }
    }, messages: {
        teacherName: {
            required: "Por favor, ingresa un nombre",
            lettersonly: "Por favor, ingresa solo letras",
            minlength: "El nombre debe tener al menos 4 caracteres"
        },
        teacherGender: {
            required: "Por favor, selecciona un género"
        },
        teacherBirthday: {
            required: "Por favor, ingresa tu fecha de nacimiento",
            date: "Por favor, ingresa una fecha válida"
        },
        teacherState: {
            required: "Por favor, ingresa tu estado civil"
        },
        teacherPhone: {
            required: "Por favor ingresa tu número de teléfono",
            number: "Por favor, ingresa solo números",
            minlength: "El número de teléfono debe tener al menos 10 caracteres",
            maxlength: "El número de teléfono debe tener 12 caracteres"
        },
        teacherEmail: {
            required: "Por favor, ingresa tu correo electrónico",
            email: "Por favor, ingresa un correo electrónico válido"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});

$("#addTeachersUsers").validate({
    rules:{
        teacherUserAdd: {
            required: true,
            minlength: 4,
        },
        teacherUserPass: {
            required: true,
            minlength: 8,
        }
    }, messages: {
        teacherUserAdd: {
            required: "Por favor, ingresa un usuario",
        },
        teacherUserPass: {
            required: "Por favor, ingresa una contraseña",
            minlength: "La contraseña debe tener al menos 8 caracteres"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});

$("#editTeachersUsers").validate({
    rules:{
        teacherUserAddEdit: {
            required: true,
            minlength: 4,
        },
        teacherUserPassEdit: {
            required: true,
            minlength: 8,
        }
    }, messages: {
        teacherUserAddEdit: {
            required: "Por favor, ingresa un usuario",
        },
        teacherUserPassEdit: {
            required: "Por favor, ingresa una contraseña",
            minlength: "La contraseña debe tener al menos 8 caracteres"
        },
        errorPlacement: function(error, element) {
            var elementId = element.attr("id");
            error.insertBefore($("#" + elementId + "-error")); // Coloca el error después de la etiqueta de error personalizada
        }
    }
});


$.validator.addMethod("lettersonly", function(value) {
    return /[a-zA-Z\'\-\sáéíóúñÑÁÉÍÓÚüÜ]+$/.test(value);
}, "Por favor, ingresa solo letras");

$.validator.addMethod("specialChars", function(value) {
    return /^[a-zA-Z0-9]*$/.test(value);
}, "Por favor, ingresa solo letras y números");

//metodo para validar un select no tenga un valor vacio
$.validator.addMethod("valueNotEquals", function(value, element, arg){
    return arg !== value;
}, "Por favor, selecciona una opción");




/*terminan las reglas de validación para el formulario de registro de productos*/
///////////////////////////////////////////////////////////////////////////////

