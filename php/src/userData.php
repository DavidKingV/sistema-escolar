<?php
namespace Vendor\Schoolarsystem;

class userData{
        
        private $connection;
        
        public function __construct($connection){
            $this->connection = $connection;
        }
    
        public function GetCurrentUserData($userId){
            $stmt = $this->connection->prepare("SELECT * FROM data_users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
    
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                return array("success" => true, "userName" => $row['nombre'], "email" => $row['email'], "phone" => $row['telefono']);
            }else{
                return array("success" => false, "message" => "Usuario no encontrado");
            }
        }

}