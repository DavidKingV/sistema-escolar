<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\SensitiveActionAuthorizer;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\CarreersModel;

class CarreersController
{
    private DBConnection $connection;
    private CarreersModel $carreers;
    private SensitiveActionAuthorizer $sensitiveActions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->carreers = new CarreersModel($this->connection);
        $this->sensitiveActions = new SensitiveActionAuthorizer();
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

    public function deleteCarreerById(int $carreerId, ?string $password = null): array
    {
        if ($error = Validation::id($carreerId)) {
            return $error;
        }

        $authorization = $this->sensitiveActions->authorize($password);

        if (!$authorization['success']) {
            return $authorization;
        }

        return $this->carreers->deleteCarreerById($carreerId);
    }
}
