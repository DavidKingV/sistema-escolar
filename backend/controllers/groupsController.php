<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../models/groupsModel.php';

use Vendor\Schoolarsystem\DBConnection;

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
}