<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\CarreersModel;

class CarreersController{
    private $carreers;

    public function __construct(DBConnection $dbConnection){
        $this->carreers = new CarreersModel($dbConnection);
    }

    public function getCareers(){
        return $this->carreers->getCareers();
    }

    public function getCareer($idCarreer){
        return $this->carreers->getCareer($idCarreer);
    }

    public function addCarreer($carreerData){
        return $this->carreers->addCarreer($carreerData);
    }

    public function updateCarreer($carreerDataEditArray){
        return $this->carreers->updateCarreer($carreerDataEditArray);
    }

    public function deleteCarreer($idCarreer){
        return $this->carreers->deleteCarreer($idCarreer);
    }

    public function getSubjects($carreerId){
        return $this->carreers->getSubjects($carreerId);
    }

    public function getChildSubjects($subjectID){
        return $this->carreers->getChildSubjects($subjectID);
    }

    public function addSubjectsCarreer($subjectsCarreerArray){
        return $this->carreers->addSubjectsCarreer($subjectsCarreerArray);
    }
}
