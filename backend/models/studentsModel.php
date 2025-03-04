<?php
require __DIR__.'/../../php/vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;

class StudentsModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function updateStatus($statusData){
        try{
            $sql = "UPDATE students SET academical_status = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta SQL: ' . $this->connection->error);
            }

            $stmt->bind_param('ii', $statusData['studentStatus'], $statusData['studentId']);

            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta SQL: ' . $stmt->error);
            }

            if ($stmt->affected_rows == 0) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la cita',
                    'error' => 'No se encontró la cita'
                ];
            }

            $stmt->close();

            return [
                'success' => true,
                'message' => 'Estatus actualizado correctamente'
            ];
        }catch(Exception $e){
            return [
                'success' => false,
                'message' => 'Error al actualizar el estatus',
                'error' => $e->getMessage()
            ];
        }
    }
}