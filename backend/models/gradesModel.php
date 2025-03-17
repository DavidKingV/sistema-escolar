<?php
require __DIR__.'/../../php/vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;

class GradesModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();      
    }

    public function addMakeOverGrade($data){
        $this->connection->begin_transaction();

        $subjectChildId = $data['subjectChildId'] ?? NULL;
        $subjectId = $data['subjectId'];

        try{
            if($subjectChildId){
                $slq = "INSERT INTO makeOverGrades (studentId, subjectChildId, continuousGrade, examGrade, finalGrade) VALUES (?,?,?,?,?)";
                $stmt = $this->connection->prepare($slq);
                
                if(!$stmt){
                    throw new Exception('Error al preparar la consulta');
                }

                $stmt->bind_param('iiddd', $data['studentId'], $data['subjectChildId'], $data['continuousGrade'], $data['examGrade'], $data['finalGrade']);

                if (!$stmt->execute()) {
                    throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmt->error);
                }

                if($stmt->affected_rows === 0){
                    throw new Exception('Error al insertar la calificación' . $stmt->error);
                }

                $createdId = $stmt->insert_id;

                if($createdId){
                    $sqli = "UPDATE student_grades_child SET makeOverId = ? WHERE id = ?";
                    $stmti = $this->connection->prepare($sql);
                    if(!$stmti){
                        throw new Exception('Error al preparar la consulta');
                    }

                    $stmti->bind_param('ii', $createdId, $data['gradeId']);

                    if (!$stmti->execute()) {
                        throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmti->error);
                    }

                    if($stmti->affected_rows === 0){
                        throw new Exception('Error al insertar la calificación' . $stmti->error);
                    }
                    
                }else{
                    throw new Exception('Error al insertar la calificación' . $stmt->error);
                }

                $this->connection->commit();

                return [
                    'success' => true,
                    'message' => 'Se ha insertado la calificación correctamente'
                ];
            }else{
                $slq = "INSERT INTO makeOverGrades (studentId, subjectId, continuosGrade, examGrade, finalGrade) VALUES (?,?,?,?,?)";
                $stmt = $this->connection->prepare($slq);
                
                if(!$stmt){
                    throw new Exception('Error al preparar la consulta');
                }

                $stmt->bind_param('iiddd', $data['studentId'], $data['subjectId'], $data['continuosGrade'], $data['examGrade'], $data['finalGrade']);

                if (!$stmt->execute()) {
                    throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmt->error);
                }

                if($stmt->affected_rows === 0){
                    throw new Exception('Error al insertar la calificación' . $stmt->error);
                }

                $createdId = $stmt->insert_id;

                $this->connection->commit();

                if($createdId){
                    $sqlid = "UPDATE student_grades SET makeOver = ? WHERE id = ?";
                    $stmtid = $this->connection->prepare($sqlid);
                    if(!$stmtid){
                        throw new Exception('Error al preparar la consulta');
                    }

                    $stmtid->bind_param('ii', $createdId, $data['gradeId']);

                    if (!$stmtid->execute()) {
                        throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmtid->error);
                    }

                    if($stmtid->affected_rows === 0){
                        throw new Exception('Error al insertar la calificación' . $stmtid->error);
                    }
                    
                }else{
                    throw new Exception('Error al insertar la calificación' . $stmt->error);
                }

                return [
                    'success' => true,
                    'message' => 'Se ha insertado la calificación correctamente'
                ];
            }
        }catch (Exception $e){
            $this->connection->rollback();
            return ['error' => 'Error al insertar la calificación', 'message' => $e->getMessage()];
        }
    }

    public function getMakeOverGrades($makeOverId){
        $sql = "SELECT 
    makeOverGrades.*, 
    subjects.nombre AS subject_nombre, 
    subject_child.nombre AS subject_child_nombre
FROM makeOverGrades
INNER JOIN subjects ON makeOverGrades.subjectId = subjects.id
LEFT JOIN subject_child ON makeOverGrades.subjectChildId = subject_child.id
WHERE makeOverGrades.id = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            return ['error' => 'Error al preparar la consulta'];
        }

        $stmt->bind_param('i', $makeOverId);

        if (!$stmt->execute()) {
            return ['error' => "Error ejecutando sentencia para UPDATE en practical_hours: " . $stmt->error];
        }

        $result = $stmt->get_result();

        if($result->num_rows === 0){
            return ['error' => 'No se encontraron calificaciones'];
        }

        $grades = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'grades' => $grades
        ];
    }
}