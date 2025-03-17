<?php
require __DIR__.'/../../php/vendor/autoload.php';
require __DIR__.'/../models/gradesModel.php';

use Vendor\Schoolarsystem\DBConnection;

class GradesController{
    private $connection;
    private $grades;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->grades = new GradesModel($dbConnection);
    }

    public function addMakeOverGrade($data){       
        return $this->grades->addMakeOverGrade($data);
    }

    public function getMakeOverGrades($makeOverId){
        return $this->grades->getMakeOverGrades($makeOverId);
    }
}