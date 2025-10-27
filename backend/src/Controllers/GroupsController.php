<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\GroupsModel;

class GroupsController{
    private $connection;
    private $groups;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->groups = new GroupsModel($dbConnection);
    }

    public function getNoGroupStudentsList(){
        $search = $_POST['search'] ?? '';
        $page = intval($_POST['page'] ?? 1);
        $limit = 30;

        $studentsList = $this->groups->getNoGroupStudentsList($search, $page, $limit);
        $studentsTotal = $this->groups->getGroupsCount($search);

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

    public function addSchedule($data){
        $addSchedule = $this->groups->addSchedule($data);
        return $addSchedule;
    }

    public function getSchedulesGroup($groupId){
        $schedulesGroup = $this->groups->getSchedulesGroup($groupId);
        return $schedulesGroup;
    }

    public function getGroups(){
        return $this->groups->getGroups();
    }

    public function getGroupsStudents($groupId){
        return $this->groups->getGroupsStudents($groupId);
    }

    public function getStudentsNames(){
        return $this->groups->getStudentsNames();
    }

    public function getGroupData($groupId){
        return $this->groups->getGroupData($groupId);
    }

    public function getGroupsJson(){
        return $this->groups->getGroupsJson();
    }

    public function addGroup($groupDataArray){
        return $this->groups->addGroup($groupDataArray);
    }

    public function updateGroup($groupDataEditArray){
        return $this->groups->updateGroup($groupDataEditArray);
    }

    public function deleteGroup($groupId){
        return $this->groups->deleteGroup($groupId);
    }

    public function addStudentGroup($groupId, $studentId){
        return $this->groups->addStudentGroup($groupId, $studentId);
    }

    public function deleteStudentGroup($studentId){
        return $this->groups->deleteStudentGroup($studentId);
    }

    public function getGroupCareer($studentId){
        return $this->groups->getGroupCareer($studentId);
    }
}
