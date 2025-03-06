


$("#carreersTable").on("click", ".subjectsCarreer", function(){
    let careerId = $(this).data("id");

    $.post('public/modals/careersSubjects.Modal.php', { careerId: careerId }, function (data) {
        $('#subjectsModalBody').html(data);
    });
});