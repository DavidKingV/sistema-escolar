<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
       case 'getStudents':

            $getStudents = new StudentsControl($con, $sesion);
            $students = $getStudents->GetStudents();

            header('Content-Type: application/json');
            echo json_encode($students);

            break;

        case 'addStudent':
            $studentData = $_POST['studentData'];
            
            parse_str($studentData, $studentDataArray);

            $addStudent = new StudentsControl($con, $sesion);
            $add = $addStudent->addStudent($studentDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'deleteStudent':

            $studentId = $_POST['studentId'];

            $deleteStudent = new StudentsControl($con, $sesion);
            $delete = $deleteStudent->DeleteStudent($studentId);

            header('Content-Type: application/json');
            echo json_encode($delete);

            break;

        case 'updateStudent':
                $studentData = $_POST['studentData'];
                
                parse_str($studentData, $studentDataArray);
    
                $updateStudent = new StudentsControl($con, $sesion);
                $update = $updateStudent->UpdateStudent($studentDataArray);
    
                header('Content-Type: application/json');
                echo json_encode($update);
    
                break;

        case 'getStudentsUsers':

            $getStudentsUsers = new StudentsControl($con, $sesion);
            $students = $getStudentsUsers->GetStudentsUsers();

            header('Content-Type: application/json');
            echo json_encode($students);

            break;

        case 'verifyStudentUser':

            $studentUser = $_POST['studentUserAdd'];

            $verifyStudent = new StudentsControl($con, $sesion);
            $verify = $verifyStudent->VerifyStudentUser($studentUser);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }

}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){

        case 'getStudentData':
                
            $studentId = $_GET['studentId'];

            $getStudent = new StudentsControl($con, $sesion);
            $student = $getStudent->GetStudent($studentId);

            header('Content-Type: application/json');
            echo json_encode($student);

        break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }

}