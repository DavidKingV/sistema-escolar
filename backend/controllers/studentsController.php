<?php
require __DIR__.'/../../php/vendor/autoload.php';
require __DIR__.'/../models/studentsModel.php';

use Vendor\Schoolarsystem\DBConnection;

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

}