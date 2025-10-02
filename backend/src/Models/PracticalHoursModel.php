<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;

class PracticalHoursModel{
    private $connection;
    private $googleCalendar;
    

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection(); 
        $this->googleCalendar = new GoogleCalendarModel();     
    }

    public function addEvent($data) {
        $this->connection->begin_transaction();
        try {
            $calendar = $this->googleCalendar->addEventCalendar($data['studentName'], $data['date'], $data['start'], $data['end']);

            if ($calendar['success']) {
                $eventId = $calendar['eventId'];

                $sql = "INSERT INTO practical_hours (googleCalendarId, id_student, date, start, end) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error preparando sentencia para INSERT en practical_hours: " . $this->connection->error);
                }

                $stmt->bind_param(
                    'sisss',
                    $eventId,
                    $data['student'],
                    $data['date'],
                    $data['start'],
                    $data['end']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error ejecutando sentencia para INSERT en practical_hours: " . $stmt->error);
                }

                $result = $stmt->affected_rows;
                $stmt->close();
                if ($result > 0) {
                    $this->connection->commit();
                    return [
                        'success' => true,
                        'message' => 'Evento registrado correctamente',
                        'eventId' => $eventId
                    ];
                } else {
                    throw new Exception("Error al registrar evento en la base de datos");
                }
            } else {
                throw new Exception("Error al registrar evento en calendario: " . $calendar['message']);
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getEventDetails($eventId) {
        $sql = "SELECT *, DATE_FORMAT(start, '%H:%i') as start, DATE_FORMAT(end, '%H:%i') as end FROM practical_hours WHERE googleCalendarId = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando sentencia para SELECT en practical_hours: " . $this->connection->error);
        }

        $stmt->bind_param('s', $eventId);

        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando sentencia para SELECT " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            if ($row['hours'] !== null) {
                return [
                    'success' => false,
                    'message' => 'El evento ya fue confirmado'
                ];
            }
    
            return [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $row                    
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se encontraron datos'
            ];
        }
    }

    public function confirmHours($hoursData) {
        $this->connection->begin_transaction();
        try {
            $sql = "UPDATE practical_hours SET status_id = 1, start = ?, end = ?, hours = ? WHERE googleCalendarId = ?";
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando sentencia para UPDATE en practical_hours: " . $this->connection->error);
            }

            $stmt->bind_param('ssss', $hoursData['start'], $hoursData['end'], $hoursData['totalHours'], $hoursData['eventId']);

            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmt->error);
            }

            $result = $stmt->affected_rows;
            $stmt->close();
            if ($result > 0) {
                $this->connection->commit();
                return [
                    'success' => true,
                    'message' => 'Horas confirmadas correctamente'
                ];
            } else {
                throw new Exception("Error al confirmar horas en la base de datos");
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deteleEvent($hoursData) {
        $this->connection->begin_transaction();
        try {
            $sql = "UPDATE practical_hours SET status_id = ?, hours = 0 WHERE googleCalendarId = ?";
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando sentencia para UPDATE en practical_hours: " . $this->connection->error);
            }

            $stmt->bind_param('is', $hoursData['deleteRazon'], $hoursData['eventId']);

            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando sentencia para UPDATE en practical_hours: " . $stmt->error);
            }

            $result = $stmt->affected_rows;
            $stmt->close();

            if ($result > 0) {
                $this->connection->commit();
                return [
                    'success' => true,
                    'message' => 'Evento cancelado correctamente'
                ];
            } else {
                throw new Exception("Error al cancelar evento en la base de datos");
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function addStudentHours($hoursData) {
        $this->connection->begin_transaction();
        $status = 1;
        try {
            $sql = "INSERT INTO practical_hours (status_id, id_student, date, start, end, hours) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando sentencia para INSERT en practical_hours: " . $this->connection->error);
            }

            $stmt->bind_param(
                'iissss',
                $status,
                $hoursData['studentId'],
                $hoursData['date'],
                $hoursData['start'],
                $hoursData['end'],
                $hoursData['totalHours']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando sentencia para INSERT en practical_hours: " . $stmt->error);
            }

            $result = $stmt->affected_rows;
            $stmt->close();
            if ($result > 0) {
                $this->connection->commit();
                return [
                    'success' => true,
                    'message' => 'Horas registradas correctamente'
                ];
            } else {
                throw new Exception("Error al registrar horas en la base de datos");
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function studentsHours() {
        try{
            $sql = "SELECT sub.id_student, s.nombre, sub.date, sub.start, sub.end, sub.hours, sub.total_hours FROM ( SELECT ph.*, SUM(hours) OVER (PARTITION BY id_student) AS total_hours, ROW_NUMBER() OVER (PARTITION BY id_student ORDER BY date DESC) AS rn FROM practical_hours ph ) sub JOIN students s ON sub.id_student = s.id WHERE sub.rn = 1;";

            $result = $this->connection->query($sql);
            if(!$result){
                throw new Exception("Error al obtener horas de los alumnos: " . $this->connection->error);
            }

            $data = [];

            while($row = $result->fetch_assoc()){
                $data[] =[
                        'success' => true,
                        'studentId' => $row['id_student'],
                        'nombre' => $row['nombre'],
                        'date' => $row['date'],
                        'start' => $row['start'],
                        'end' => $row['end'],
                        'hours' => $row['hours'],
                        'total_hours' => $row['total_hours'] ?? 0
                    ];
            }

            return $data;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getStudentlHoursData($studentId) {
        try{
            $sql = "SELECT practical_hours.*, practical_hours_status.status FROM practical_hours LEFT JOIN practical_hours_status ON practical_hours.status_id = practical_hours_status.id WHERE id_student = ? ORDER BY date DESC";

            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando sentencia para SELECT en practical_hours: " . $this->connection->error);
            }

            $stmt->bind_param('i', $studentId);

            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando sentencia para SELECT " . $stmt->error);
            }

            $result = $stmt->get_result();
            $stmt->close();

            $data = [];

            while($row = $result->fetch_assoc()){
                
                $data[] =[
                        'success' => true,
                        'date' => $row['date'],
                        'start' => $row['start'],
                        'end' => $row['end'],
                        'status' => $row['status'] ?? 'Pendiente',
                        'hours' => $row['hours']
                    ];
            }

            return $data;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
