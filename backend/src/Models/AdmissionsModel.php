<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class AdmissionsModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getAllNewAdmissions(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT *
                FROM registrationApplications
                WHERE approved=0;
            "
        );
    }

    public function deleteAdmission(int $id): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM registrationApplications
                WHERE id = ?;
            ",
            "i",
            [$id]
        );
    }

}