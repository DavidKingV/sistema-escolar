<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\PracticalHoursModel;

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
        return $this->practicalHours->getStudentHoursData($studentId);
    }

    public function deleteHour($hourId){
        return $this->practicalHours->deleteHour($hourId);
    }
}
