import { FillTable } from './forms.js';
import { initializeStudentDataTable } from '../datatables/index.js';

initializeStudentDataTable();

$('#studentTable').on('click', '#editStudent', function() {
    // Get the student id
    const studentId = $(this).data('id');
    if (studentId) {
        // Get the student data
        GetStudentsData(studentId);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para editar.'
        });
    }
});

const GetStudentsData = async (studentId) => {

    try {
        const response = await $.ajax({
            url: 'php/students/routes.php',
            type: 'GET',
            data: {studentId: studentId, action: 'getStudentData'}
            
        });
        if(response.success){
            // Fill the table with the data
            FillTable(response);
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al obtener los datos',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos',
            text: 'Ocurrió un error al obtener los datos del servidor, por favor intenta de nuevo más tarde.'
        });
    }

}

