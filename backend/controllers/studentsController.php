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
}