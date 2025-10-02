<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\TeachersModel;

class TeachersController{
    private $connection;
    private $teachers;

    public function __construct(DBConnection $dbConnection){
        $this->connection = $dbConnection;
        $this->teachers = new TeachersModel($dbConnection);
    }

    public function getTeachers(){
        return $this->teachers->getTeachers();
    }

    public function getTeacher($teacherId){
        return $this->teachers->getTeacher($teacherId);
    }

    public function addTeacher($teacherData){
        return $this->teachers->addTeacher($teacherData);
    }

    public function updateTeacherData($teacherData){
        return $this->teachers->updateTeacherData($teacherData);
    }

    public function deleteTeacher($teacherId){
        return $this->teachers->deleteTeacher($teacherId);
    }

    public function getTeachersUsers(){
        return $this->teachers->getTeachersUsers();
    }

    public function verifyTeacherUser($teacherUser){
        return $this->teachers->verifyTeacherUser($teacherUser);
    }

    public function addTeacherUser($teacherUserData){
        return $this->teachers->addTeacherUser($teacherUserData);
    }

    public function desactivateTeacherUser($teacherUserId){
        return $this->teachers->desactivateTeacherUser($teacherUserId);
    }

    public function reactivateTeacherUser($teacherUserId){
        return $this->teachers->reactivateTeacherUser($teacherUserId);
    }

    public function updateTeacherUserData($teacherUserData){
        return $this->teachers->updateTeacherUserData($teacherUserData);
    }
}
