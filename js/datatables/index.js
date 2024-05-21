function initializeStudentDataTable() {
    
    $("#studentTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "php/students/routes.php", 
            type: "POST",
            data: { action: "getStudents" },
            dataSrc: function(data){
                if(!data[0].success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
                return data;
            }
        },
        "columns": [
            // Define las columnas
            { "data": "id", "className": "text-center" },
            { "data": "no_control", "className": "text-center" },
            { "data": "name", "className": "text-center" },
            { "data": "phone", "className": "text-center" },
            { "data": "email", "className": "text-center" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-id="'+row.id+'" class="btn btn-primary btn-circle editStudent" data-bs-toggle="modal" data-bs-target="#StutentEditModal"><i class="bi bi-pencil-square"></i></button><button id="deleteStudent" data-id="'+row.id+'" class="btn btn-danger btn-circle"><i class="bi bi-trash-fill"></i></button>';
                
                },
                "className": "text-center"
            }
        ]
    });
    
}

function initializeStudentsUsersTable() {
    
    $("#studentsUsersTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "../php/students/routes.php", 
            type: "POST",
            data: { action: "getStudentsUsers" },
            dataSrc: function(data){
                if(!data[0].success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
                return data;
            }
        },
        "columns": [
            // Define las columnas
            { "data": "id", "className": "text-center" },
            { "data": "name", "className": "text-center" },
            { "data": "user", "className": "text-center", "defaultContent": "No asignado"},
            { "data": "status", "className": "text-center", "defaultContent": "Inactivo"},
            {
                "data": null,
                "render": function(data, type, row) {

                    if (row.status == "Activo") {
                        return '<button id="editUser" data-id="'+row.id+'" class="btn btn-primary btn-circle" data-bs-toggle="modal" data-bs-target="#StutentUserEditModal"><i class="bi bi-pencil-square"></i></button><button id="desactivateUser" data-id="'+row.id+'" class="btn btn-danger btn-circle"><i class="bi bi-arrow-down-square-fill"></i></button>';
                    }

                    return '<button data-id="'+row.id+'" data-name="'+row.name+'" class="btn btn-primary btn-circle addUserStudents" data-bs-toggle="modal" data-bs-target="#StutentUserModal"><i class="bi bi-arrow-up-square-fill"></i></button>';
                
                },
                "className": "text-center"
            }
        ]
    });
    
}

export { initializeStudentDataTable, initializeStudentsUsersTable };