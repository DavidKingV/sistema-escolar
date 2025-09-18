<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\SubjectsModel;
use Vendor\Schoolarsystem\auth;

class SubjectsController{
    private $connection;
    private $subjects;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->subjects = new SubjectsModel($dbConnection);
    }

    public function GetSubjects(){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->fetchSubjects();
    }

    public function GetSubjectData($subjectId){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->findSubjectById($subjectId);
    }

    public function AddSubject($subjectDataArray){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->createSubject($subjectDataArray);
    }

    public function UpdateSubjectData($subjectDataEditArray){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->updateSubject($subjectDataEditArray);
    }

    public function DeleteSubject($subjectId){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->deleteSubject($subjectId);
    }

    public function AddSubjectChild($subjectChildDataArray){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->createSubjectChild($subjectChildDataArray);
    }

    public function GetSubjectChildData($subjectFatherId, $subjectChildId){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->findSubjectChild($subjectFatherId, $subjectChildId);
    }

    public function UpdateSubjectChild($subjectChildDataEditArray){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->updateSubjectChild($subjectChildDataEditArray);
    }

    public function DeleteSubjectChild($subjectChildId){
        $verifySession = auth::check();

        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        return $this->subjects->deleteSubjectChild($subjectChildId);
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
