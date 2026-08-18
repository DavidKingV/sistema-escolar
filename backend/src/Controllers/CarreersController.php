<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\CarreersModel;
use Vendor\Schoolarsystem\Models\LoginModel;

class CarreersController
{
    private DBConnection $connection;
    private CarreersModel $carreers;
    private LoginModel $login;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->carreers = new CarreersModel($this->connection);
        $this->login = new LoginModel($this->connection);
    }

    public function getCarreerById(int $carreerId): array
    {
        if ($error = Validation::id($carreerId)) {
            return $error;
        }

        return $this->carreers->getCarreerById($carreerId);
    }

    public function getAllCarreers(): array
    {
        return $this->carreers->getAllCarreers();
    }

    public function addCarreer(array $carreerData): array
    {
        if ($error = Validation::requiredArray($carreerData)) {
            return $error;
        }

        return $this->carreers->addCarreer($carreerData);
    }

    public function updateCarreer(array $carreerUpdateData): array
    {
        if ($error = Validation::requiredArray($carreerUpdateData)) {
            return $error;
        }

        return $this->carreers->updateCarreer($carreerUpdateData);
    }

    public function deleteCarreerById(int $carreerId, string $password): array
    {
        if ($error = Validation::id($carreerId)) {
            return $error;
        }

        if ($error = Validation::password($password)) {
            return $error;
        }

        $userId = $_SESSION['userId'];

        if (!$this->login->verifyUserPassword($userId, $password)) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta."
            ];
        }

        return $this->carreers->deleteCarreerById($carreerId);
    }
}
