$("#studentTable").DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
        url: "php/students/routes.php", 
        type: "POST",
        data: { action: "getStudents" },
        dataSrc: function(data){
            return data;
        }
    },
});