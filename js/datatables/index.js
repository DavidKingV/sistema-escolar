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
                        text: data[0].message,
                    });
                    return [];
                }
                return data;
            }
        },
        "columns": [
            // Define las columnas
            { "data": "no_control", "className": "text-center" },
            { "data": "name", "className": "text-center" },
            { "data": "phone", "className": "text-center" },
            { "data": "email", "className": "text-center" },
            { "data": "group_name", "className": "text-center", "defaultContent": "No asignado"},
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-token="'+row.token+'" data-id="'+row.id+'" class="btn btn-primary btn-circle GradeStudent"><i class="bi bi-journal-check"></i></button>';
                
                },
                "className": "text-center"
            },
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
                        return '<button data-id="'+row.id+'" data-name="'+row.name+'" data-user="'+row.user+'" class="btn btn-primary btn-circle editStudentUser" data-bs-toggle="modal" data-bs-target="#StutentUserEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle desactivateStudentUser"><i class="bi bi-arrow-down-square-fill"></i></button>';
                    }
                    else if (row.status == "Inactivo") {
                        return '<button data-id="'+row.id+'" class="btn btn-warning btn-circle reactivateStudentUser"><i class="bi bi-arrow-clockwise"></i></button>';
                    }
                    return '<button data-id="'+row.id+'" data-name="'+row.name+'" class="btn btn-primary btn-circle addUserStudents" data-bs-toggle="modal" data-bs-target="#StutentUserModal"><i class="bi bi-arrow-up-square-fill"></i></button>';
                
                },
                "className": "text-center"
            }
        ]
    });
    
}

function initializeTeachersDataTable() {
    
    $("#teachersTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "php/teachers/routes.php", 
            type: "POST",
            data: { action: "getTeachers" },
            dataSrc: function(data){
                console.log(data);
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
            { "data": "phone", "className": "text-center" },
            { "data": "email", "className": "text-center" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-id="'+row.id+'" data-name="'+row.name+'" class="btn btn-primary btn-circle editTeacher" data-bs-toggle="modal" data-bs-target="#TeacherEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle deleteTeacher"><i class="bi bi-trash-fill"></i></button>';
                
                },
                "className": "text-center"
            }
        ]
    });
    
}

function initializeTeachersUsersTable(){
    $("#teacherUsersTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "../php/teachers/routes.php", 
            type: "POST",
            data: { action: "getTeachersUsers" },
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
                        return '<button data-id="'+row.id+'" data-name="'+row.name+'" data-user="'+row.user+'" class="btn btn-primary btn-circle editTeacherUser" data-bs-toggle="modal" data-bs-target="#TeacherUserEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle desactivateTeacherUser"><i class="bi bi-arrow-down-square-fill"></i></button>';
                    }
                    else if (row.status == "Inactivo") {
                        return '<button data-id="'+row.id+'" class="btn btn-warning btn-circle reactivateTeacherUser"><i class="bi bi-arrow-clockwise"></i></button>';
                    }
                    return '<button data-id="'+row.id+'" data-name="'+row.name+'" class="btn btn-primary btn-circle addUserTeachers" data-bs-toggle="modal" data-bs-target="#teacherUserModal"><i class="bi bi-arrow-up-square-fill"></i></button>';
                
                },
                "className": "text-center"
            }
        ]
    });
}

function initializeCarreersDataTable() {
        
    $("#carreersTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "php/carreers/routes.php", 
            type: "POST",
            data: { action: "getCareers" },
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
            { "data": "area", "className": "text-center" },
            { "data": "subarea", "className": "text-center" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-id="'+row.id+'" class="btn btn-primary btn-circle editCarreer" data-bs-toggle="modal" data-bs-target="#CareerEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle deleteCarreer"><i class="bi bi-trash-fill"></i></button>';
                    
                },
                "className": "text-center"
            }
        ]
    });  
}

function initializeGroupsDataTable() {
            
     $("#groupsTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "php/groups/routes.php", 
            type: "POST",
            data: { action: "getGroups" },
            dataSrc: function(data){
                console.log(data);
                if(!data[0].success) {
                    Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data[0].message,
                });
                return [];
            }
            return data;
            }
        },
        "columns": [
            // Define las columnas
            { "data": "id", "className": "text-center" },
            { "data": "id_carreer", "className": "text-center" },
            { "data": "key", "className": "text-center" },
            { "data": "name", "className": "text-center" },
            { "data": "startDate", "className": "text-center" },
            { "data": "endDate", "className": "text-center" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-id="'+row.id+'" class="btn btn-primary btn-circle groupDetails"><i class="bi bi-eye-fill"></i></button><button data-id="'+row.id+'" class="btn btn-primary btn-circle editGroup" data-bs-toggle="modal" data-bs-target="#GroupsEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle deleteGroup"><i class="bi bi-trash-fill"></i></button>';
                        
                },
                "className": "text-center"
            }
        ]
    });  
}

function initializeGroupsStudentsDataTable(groupId) {
                
    $("#groupStudentsTable").DataTable({
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
        url: "../php/groups/routes.php", 
        type: "POST",
        data: {groupId: groupId, action: "getGroupsStudents" },
        dataSrc: function(data){
            console.log(data);
            if(!data[0].success) {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data[0].message,
            });
            return [];
        }
        return data;
        }
    },
    "columns": [
         // Define las columnas
         { "data": "student_id", "className": "text-center" },
        { "data": "student_name", "className": "text-center" },
        {
            "data": null,
            "render": function(data, type, row) {
                return '</button><button data-id="'+row.student_id+'" class="btn btn-danger btn-circle deleteGroupStudent"><i class="bi bi-trash-fill"></i></button>';
                        
            },
            "className": "text-center"
        }
    ]
    });  
}

function initializeSubjectsDataTable(){
    $("#subjectsTable").DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: "php/subjects/routes.php", 
            type: "POST",
            data: { action: "getSubjects" },
            dataSrc: function(data){
                if(!data[0].success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data[0].message,
                    });

                    return [];
                }
                return data;
            }
        },
        "columns": [
            // Define las columnas
            { "data": "id", "className": "text-center" },
            { "data": "name", "className": "text-center" },
            { "data": "career", "className": "text-center" },
            { "data": "description", "className": "text-center" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button data-id="'+row.id+'" class="btn btn-primary btn-circle editSubject" data-bs-toggle="modal" data-bs-target="#SubjectsEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="'+row.id+'" class="btn btn-danger btn-circle deleteSubject"><i class="bi bi-trash-fill"></i></button>';
                    
                },
                "className": "text-center"
            }
        ]
    });  

}

export { initializeStudentDataTable, initializeStudentsUsersTable, initializeTeachersDataTable, initializeTeachersUsersTable, initializeCarreersDataTable, initializeGroupsDataTable, initializeGroupsStudentsDataTable, initializeSubjectsDataTable };