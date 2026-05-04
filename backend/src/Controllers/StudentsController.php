<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\StudentsModel;

class StudentsController{
    private $connection;
    private $students;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->students = new StudentsModel($dbConnection);
    }

    public function updateStatus($statusData){
        $updateStatus = $this->students->updateStatus($statusData);        

        return $updateStatus;
    }

    public function getStudentName($studentId){
        return $this->students->getStudentName($studentId);
    }

    public function getStudentsListSelect(){

        $search = $_POST['search'] ?? '';
        $page = intval($_POST['page'] ?? 1);
        $limit = 30; 

        $studentsList = $this->students->getStudentsListSelect($search, $page, $limit);
        $studentsTotal = $this->students->getStudentsCount($search);

        $students=array();

        if(!$studentsList !== NULL){
            while ($row = $studentsList->fetch_assoc()) {
                $students[] = array(
                    'id' => $row['id'],
                    'text' => $row['nombre'] // Cambiado a 'text' para compatibilidad con Select2
                );
            }        
            return array(
                'results' => $students,
                'pagination' => array(
                    'more' => ($page * $limit) < $studentsTotal
                ),
                'total_count' => $studentsTotal
            );
        }else{
            return array(
                'results' => [],
                'pagination' => array(
                    'more' => false
                ),
                'total_count' => 0
            );
        }
    }

    public function getStudents(){
        return $this->students->getStudents();
    }

    public function getStudent($studentId){
        return $this->students->getStudent($studentId);
    }

    public function addStudent($studentData){
        return $this->students->addStudent($studentData);
    }

    public function updateStudent($studentData){
        return $this->students->updateStudent($studentData);
    }

    public function deleteStudent($studentId){
        return $this->students->deleteStudent($studentId);
    }

    public function getStudentsNames(){
        return $this->students->getStudentsNames();
    }

    public function getStudentsUsers(){
        return $this->students->getStudentsUsers();
    }

    public function getMicrosoftStudentsUsers(){
        return $this->students->getMicrosoftStudentsUsers();
    }

    public function verifyStudentUser($studentUser){
        return $this->students->verifyStudentUser($studentUser);
    }

    public function addStudentUser($studentData){
        return $this->students->addStudentUser($studentData);
    }

    public function updateStudentUser($studentData){
        return $this->students->updateStudentUser($studentData);
    }

    public function desactivateStudentUser($studentId){
        return $this->students->desactivateStudentUser($studentId);
    }

    public function reactivateStudentUser($studentId){
        return $this->students->reactivateStudentUser($studentId);
    }

    public function getSubjectsNames($carrerId){
        return $this->students->getSubjectsNames($carrerId);
    }

    public function getChildSubjectsNames($idSubject){
        return $this->students->getChildSubjectsNames($idSubject);
    }

    public function verifyGroupStudent($studentIdGroup){
        return $this->students->verifyGroupStudent($studentIdGroup);
    }

    public function getStudentGrades($studentId){
        return $this->students->getStudentGrades($studentId);
    }

    public function addGradeStudent($gradeData){
        return $this->students->addGradeStudent($gradeData);
    }

    public function getGroupsNames(){
        return $this->students->getGroupsNames();
    }

    public function addStudentGroup($studentGroupData){
        return $this->students->addStudentGroup($studentGroupData);
    }

    public function searchMicrosoftUser($displayName){
        return $this->students->searchMicrosoftUser($displayName);
    }

    public function assignMicrosoftUserToStudent($studentId, $microsoftUserId, $microsoftDisplayName, $microsoftEmail){
        return $this->students->assignMicrosoftUserToStudent($studentId, $microsoftUserId, $microsoftDisplayName, $microsoftEmail);
    }

    public function verifyTokenStudent($studentId, $studentSecretKey){
        return $this->students->verifyTokenStudent($studentId, $studentSecretKey);
    }
}
