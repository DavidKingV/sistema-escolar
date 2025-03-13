<?php
require __DIR__.'/../../php/vendor/autoload.php';
require __DIR__.'/../models/practicalHoursModel.php';

use Vendor\Schoolarsystem\DBConnection;

class PracticalHoursController{
    private $connection;
    private $practicalHours;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->practicalHours = new PracticalHoursModel($dbConnection);
    }

    public function addEvent($data){
        return $this->practicalHours->addEvent($data);
    }

    public function getEventDetails($eventId){
        return $this->practicalHours->getEventDetails($eventId);
    }

    public function confirmHours($hoursData){
        return $this->practicalHours->confirmHours($hoursData);
    }

    public function deteleEvent($hoursData){
        return $this->practicalHours->deteleEvent($hoursData);
    }

    public function studentsHours(){
        return $this->practicalHours->studentsHours();
    }

    public function addStudentHours($hoursData){
        return $this->practicalHours->addStudentHours($hoursData);
    }

    public function getStudentlHoursData($studentId){
        return $this->practicalHours->getStudentlHoursData($studentId);
    }
}