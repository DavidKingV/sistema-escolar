<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\SubjectsModel;

class SubjectsController{
    private $connection;
    private $subjects;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->subjects = new SubjectsModel($dbConnection);
    }    

    public function getSubjectsListSelect($careerId){

        $search = $_POST['search'] ?? '';
        $page = intval($_POST['page'] ?? 1);
        $limit = 30; 

        $subjectsList = $this->subjects->getSubjectsListSelect($search, $page, $limit, $careerId);
        $subjectsTotal = $this->subjects->getSubjectsCount($search);

        $subjects=array();

        if(!$subjectsList !== NULL){
            while ($row = $subjectsList->fetch_assoc()) {
                $subjects[] = array(
                    'id' => $row['id'],
                    'text' => $row['nombre'] // Cambiado a 'text' para compatibilidad con Select2
                );
            }        
            return array(
                'results' => $subjects,
                'pagination' => array(
                    'more' => ($page * $limit) < $subjectsTotal
                ),
                'total_count' => $subjectsTotal
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

    public function getChildSubject($subjectId){
        $childSubject = $this->subjects->getChildSubject($subjectId);
        
        return $childSubject;
    }

    public function subjectsListTable($careerId){
        $subjectsListTable = $this->subjects->subjectsListTable($careerId);
        
        return $subjectsListTable;
    }

    public function addSubjectCareer($subjectData){
        $addSubjectCareer = $this->subjects->addSubjectCareer($subjectData);
        
        return $addSubjectCareer;
    }

}
