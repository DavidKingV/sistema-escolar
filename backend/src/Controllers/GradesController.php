<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\GradesModel;

class GradesController
{
    private DBConnection $connection;
    private GradesModel $grades;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->grades = new GradesModel($this->connection);
    }

    public function getMakeOverGrades(int $makeOverId): array
    {
        if ($error = Validation::id($makeOverId)) {
            return $error;
        }

        return $this->grades->getMakeOverGrades($makeOverId);
    }

    public function addMakeOverGrade(array $makeOverData): array
    {
        $data = $makeOverData['makeOverData'] ?? $makeOverData;

        if (is_string($data)) {
            parse_str($data, $data);
        }

        if (!is_array($data)) {
            $data = [];
        }

        if ($error = Validation::requiredArray($data)) {
            return $error;
        }

        return $this->grades->addMakeOverGrade($data);
    }
}
