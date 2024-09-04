<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';
include __DIR__.'/verify.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
       case 'getStudents':

            $getStudents = new StudentsControl($connection);
            $students = $getStudents->GetStudents();

            header('Content-Type: application/json');
            echo json_encode($students);

            break;

        case 'GetStudentsNames':

            $getStudentsNames = new StudentsControl($connection);
            $studentsNames = $getStudentsNames->GetStudentsNames();

            header('Content-Type: application/json');
            echo json_encode($studentsNames);

            break;

        case 'addStudent':
            $studentData = $_POST['studentData'];
            
            parse_str($studentData, $studentDataArray);

            $addStudent = new StudentsControl($connection);
            $add = $addStudent->addStudent($studentDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'deleteStudent':

            $studentId = $_POST['studentId'];

            $deleteStudent = new StudentsControl($connection);
            $delete = $deleteStudent->DeleteStudent($studentId);

            header('Content-Type: application/json');
            echo json_encode($delete);

            break;

        case 'updateStudent':
                $studentData = $_POST['studentData'];
                
                parse_str($studentData, $studentDataArray);
    
                $updateStudent = new StudentsControl($connection);
                $update = $updateStudent->UpdateStudent($studentDataArray);
    
                header('Content-Type: application/json');
                echo json_encode($update);
    
                break;

        case 'getStudentsUsers':

            $getStudentsUsers = new StudentsControl($connection);
            $students = $getStudentsUsers->GetStudentsUsers();

            header('Content-Type: application/json');
            echo json_encode($students);

            break;

        case 'getStudentsMicrosoftUsers':

            $getStudentsMicrosoftUsers = new StudentsControl($connection);
            $studentsMicrosoftUsers = $getStudentsMicrosoftUsers->GetMicrosoftStudentsUsers();
    
            header('Content-Type: application/json');
            echo json_encode($studentsMicrosoftUsers);
    
            break;

        case 'verifyStudentUser':

            $studentUser = $_POST['studentUserAdd'];

            $verifyStudent = new StudentsControl($connection);
            $verify = $verifyStudent->VerifyStudentUser($studentUser);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

            case 'addStudentUser':
                    
                    $studentUserData = $_POST['studentUserData'];
                    parse_str($studentUserData, $studentDataArray);
        
                    $addStudentUser = new StudentsControl($connection);
                    $add = $addStudentUser->AddStudentUser($studentDataArray);
        
                    header('Content-Type: application/json');
                    echo json_encode($add);
        
                    break;

            case 'updateStudentUser': 
                $studentEditUserData = $_POST['studentUserData'];
                parse_str($studentEditUserData, $studentEditDataArray);

                $updateStudentUser = new StudentsControl($connection);
                $update = $updateStudentUser->UpdateStudentUser($studentEditDataArray);

                header('Content-Type: application/json');
                echo json_encode($update);

                break;

            case 'desactivateStudentUser':
                $studentId = $_POST['studentId'];

                $desactivateStudent = new StudentsControl($connection);
                $desactivate = $desactivateStudent->DesactivateStudentUser($studentId);

                header('Content-Type: application/json');
                echo json_encode($desactivate);

                break;

            case 'reactivateStudentUser':
                $studentId = $_POST['studentId'];

                $reactivateStudent = new StudentsControl($connection);
                $reactivate = $reactivateStudent->ReactivateStudentUser($studentId);

                header('Content-Type: application/json');
                echo json_encode($reactivate);

                break;

            case 'getStudentGrades':
                $studentId = $_POST['studentId'];

                $getGrades = new StudentsControl($connection);
                $grades = $getGrades->GetStudentGrades($studentId);

                header('Content-Type: application/json');
                echo json_encode($grades);

                break;

            case 'addGradeStudent':
                $gradeData = $_POST['studentGradeData'];
                parse_str($gradeData, $gradeDataArray);

                $addGrade = new StudentsControl($connection);
                $add = $addGrade->AddGradeStudent($gradeDataArray);

                header('Content-Type: application/json');
                echo json_encode($add);

                break;

        default:

        case 'addStudentGroup':
            $studentGroupData = $_POST['studentGroupData'] ?? NULL;
            parse_str($studentGroupData, $studentGroupDataArray);

            $addStudentGroup = new StudentsControl($connection);
            $add = $addStudentGroup->AddStudentGroup($studentGroupDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }

}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){

        case 'getStudentData':
                
            $studentId = $_GET['studentId'];

            $getStudent = new StudentsControl($connection);
            $student = $getStudent->GetStudent($studentId);

            header('Content-Type: application/json');
            echo json_encode($student);

        break;

        case 'getSubjectsNames':
            $carrerId = $_GET['carrerId'];

            $getSubjects = new StudentsControl($connection);
            $subjects = $getSubjects->GetSubjectsNames($carrerId);

            header('Content-Type: application/json');
            echo json_encode($subjects);

            break;

        case 'getChildSubjectsNames':
            $idSubject = $_GET['idSubject'];

            $getChildSubjects = new StudentsControl($connection);
            $childSubjects = $getChildSubjects->GetChildSubjectsNames($idSubject);

            header('Content-Type: application/json');
            echo json_encode($childSubjects);

            break;

        case 'verifyToken':
            $studentId = $_GET['studentId'];
            $studentSecretKey = $_GET['token'];

            $verifyToken = new AdvancedStudentsControl();
            $verify = $verifyToken->VerifyIdStudentId($studentId, $studentSecretKey);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

        case 'verifyGroupStudent':
            $studentIdGroup = $_GET['studentIdGroup'];

            $verifyGroup = new StudentsControl($connection);
            $verify = $verifyGroup->VerifyGroupStudent($studentIdGroup);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

        case 'getGroupsNames':

            $getGroups = new StudentsControl($connection);
            $groups = $getGroups->GetGroupsNames();

            header('Content-Type: application/json');
            echo json_encode($groups);

            break;

        case 'searchMicrosoftUser':
            $displayName = $_GET['displayName'];

            $searchUser = new StudentsControl($connection);
            $search = $searchUser->SearchMicrosoftUser($displayName);

            header('Content-Type: application/json');
            echo json_encode($search);

            break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }

}