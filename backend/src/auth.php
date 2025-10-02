<?php
namespace Vendor\Schoolarsystem;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\MicrosoftActions;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class auth {

    public static function verify($jwt){
        loadEnv::cargar();

        $microsoftAccessToken = $_SESSION["adnanhussainturki/microsoft"]["accessToken"] ?? NULL;
        $secretKey = $_ENV['KEY'] ?? NULL;

        if(isset($_SESSION['userId'])&&isset($_COOKIE['auth'])){
            try {
                $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

                $dbConnection = new DBConnection();
                $connection   = $dbConnection->getConnection();
                $accessData   = self::resolveAccessProfile($connection, (string) $decoded->userId, 'local');

                return array_merge(
                    array(
                        'success'     => true,
                        'userId'      => $decoded->userId,
                        'authSource'  => 'local',
                        'admin'       => $accessData['isAdmin'] ? 'Local' : false,
                    ),
                    $accessData
                );
            } catch (\Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        } else if($microsoftAccessToken != NULL){

            $dbConnection = new DBConnection();
            $connection = $dbConnection->getConnection();
            $microsoftActions = new MicrosoftActions($connection);

            $userId = $microsoftActions->getUserId($microsoftAccessToken);
            if($userId['success']){
                $accessData = self::resolveAccessProfile($connection, (string) $userId['userId'], 'microsoft');
                $verifyUserRegistration = $microsoftActions->getUserRegistration($userId['userId']);

                if($verifyUserRegistration && !$accessData['isAdmin']){
                    $accessData['role'] = $accessData['role'] ?? 'admin';
                    $accessData['roleName'] = $accessData['roleName'] ?? 'Administrador';
                    $accessData['isAdmin'] = true;
                }

                return array_merge(
                    array(
                        'success'     => true,
                        'userId'      => $userId['userId'],
                        'authSource'  => 'microsoft',
                        'accessToken' => $microsoftAccessToken,
                        'admin'       => $accessData['isAdmin'],
                    ),
                    $accessData
                );
            }else{
                return array('success' => false, 'message' => $userId['error']);
            }
        }
        else{
            return array('success' => false, 'message' => 'Sesión no iniciada');
        }
    }

   public static function check(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $jwt = $_COOKIE['auth'] ?? NULL;
        $verification = self::verify($jwt);

        if(!$verification['success']){
            if(isset($_COOKIE['auth'])){
                setcookie('auth', '', time() - 3600, '/');
            }
            if(session_status() === PHP_SESSION_ACTIVE){
                session_unset();
                session_destroy();
            }
        }
        return $verification;
    }

    private static function resolveAccessProfile($connection, string $userIdentifier, string $authSource): array
    {
        $profile = array(
            'role'        => null,
            'roleName'    => null,
            'permissions' => array(),
            'isAdmin'     => false,
        );

        if(!($connection instanceof \mysqli)){
            return $profile;
        }

        $roleId = null;

        // Intentar obtener la relación directa del usuario con la tabla user_roles
        if(self::tableExists($connection, 'user_roles')){
            $hasProviderColumn = self::columnExists($connection, 'user_roles', 'provider');
            $query = 'SELECT * FROM user_roles WHERE user_id = ?';
            if($hasProviderColumn){
                $query .= ' AND provider = ?';
            }
            $query .= ' LIMIT 1';

            if($stmt = $connection->prepare($query)){
                if($hasProviderColumn){
                    $stmt->bind_param('ss', $userIdentifier, $authSource);
                }else{
                    $stmt->bind_param('s', $userIdentifier);
                }

                if($stmt->execute()){
                    $result = $stmt->get_result();
                    if($result && $result->num_rows > 0){
                        $row = $result->fetch_assoc();
                        $roleId = $row['role_id'] ?? $row['roleId'] ?? $roleId;
                        $profile['role'] = $row['role_key'] ?? $row['role'] ?? $profile['role'];
                        $profile['roleName'] = $row['role_name'] ?? $row['roleName'] ?? $profile['roleName'];
                        if(isset($row['is_admin'])){
                            $profile['isAdmin'] = (bool)$row['is_admin'];
                        }elseif(isset($row['isAdmin'])){
                            $profile['isAdmin'] = (bool)$row['isAdmin'];
                        }
                    }
                    if($result){
                        $result->free();
                    }
                }
                $stmt->close();
            }
        }

        // Obtener detalles del rol desde la tabla roles en caso de contar con un role_id
        if($roleId !== null && self::tableExists($connection, 'roles')){
            if($stmt = $connection->prepare('SELECT id, slug, name, is_admin FROM roles WHERE id = ? LIMIT 1')){
                $stmt->bind_param('i', $roleId);
                if($stmt->execute()){
                    $result = $stmt->get_result();
                    if($result && $result->num_rows > 0){
                        $row = $result->fetch_assoc();
                        $profile['role'] = $row['slug'] ?? $profile['role'];
                        $profile['roleName'] = $row['name'] ?? $profile['roleName'];
                        if(isset($row['is_admin'])){
                            $profile['isAdmin'] = (bool)$row['is_admin'];
                        }
                    }
                    if($result){
                        $result->free();
                    }
                }
                $stmt->close();
            }
        }

        /* Fallback para obtener información directamente desde data_users si existe la tabla
        if(!$profile['role'] && self::columnExists($connection, 'data_users', 'role')){
            if($stmt = $connection->prepare('SELECT role FROM data_users WHERE id = ? LIMIT 1')){
                $stmt->bind_param('s', $userIdentifier);
                if($stmt->execute()){
                    $result = $stmt->get_result();
                    if($result && $result->num_rows > 0){
                        $row = $result->fetch_assoc();
                        $profile['role'] = $row['role'] ?? $profile['role'];
                    }
                    if($result){
                        $result->free();
                    }
                }
                $stmt->close();
            }
        }*/

        $profile['permissions'] = self::fetchUserPermissions($connection, $roleId, $userIdentifier, $authSource);

        return $profile;
    }

    private static function fetchUserPermissions($connection, $roleId, string $userIdentifier, string $authSource): array
    {
        $permissions = array();

        if(!($connection instanceof \mysqli)){
            return $permissions;
        }

        // Permisos asociados directamente al usuario
        if(self::tableExists($connection, 'user_permissions')){
            $hasProviderColumn = self::columnExists($connection, 'user_permissions', 'provider');
            $query = 'SELECT * FROM user_permissions WHERE user_id = ?';
            if($hasProviderColumn){
                $query .= ' AND provider = ?';
            }

            if($stmt = $connection->prepare($query)){
                if($hasProviderColumn){
                    $stmt->bind_param('ss', $userIdentifier, $authSource);
                }else{
                    $stmt->bind_param('s', $userIdentifier);
                }

                if($stmt->execute()){
                    $result = $stmt->get_result();
                    if($result){
                        while($row = $result->fetch_assoc()){
                            if(isset($row['permission'])){
                                $permissions[] = $row['permission'];
                            }elseif(isset($row['permission_key'])){
                                $permissions[] = $row['permission_key'];
                            }elseif(isset($row['permission_id'])){
                                $permissions[] = (int)$row['permission_id'];
                            }
                        }
                        $result->free();
                    }
                }
                $stmt->close();
            }
        }

        // Permisos asociados al rol
        if($roleId !== null && self::tableExists($connection, 'role_permissions')){
            $query = 'SELECT * FROM role_permissions WHERE role_id = ?';
            if($stmt = $connection->prepare($query)){
                $stmt->bind_param('i', $roleId);
                if($stmt->execute()){
                    $result = $stmt->get_result();
                    if($result){
                        while($row = $result->fetch_assoc()){
                            if(isset($row['permission'])){
                                $permissions[] = $row['permission'];
                            }elseif(isset($row['permission_key'])){
                                $permissions[] = $row['permission_key'];
                            }elseif(isset($row['permission_id'])){
                                $permissions[] = $row['permission_id'];
                            }
                        }
                        $result->free();
                    }
                }
                $stmt->close();
            }
        }

        // Traducir ids numéricos de permisos a llaves legibles si existe la tabla permissions
        if(!empty($permissions) && self::tableExists($connection, 'permissions')){
            $numericPermissions = array_values(array_filter($permissions, 'is_int'));

            if(!empty($numericPermissions)){
                $placeholders = implode(',', array_fill(0, count($numericPermissions), '?'));
                $types = str_repeat('i', count($numericPermissions));

                $sql = 'SELECT id, slug FROM permissions WHERE id IN ('.$placeholders.')';
                if($stmt = $connection->prepare($sql)){
                    $bindParams = array($types);
                    foreach($numericPermissions as $index => $value){
                        $bindParams[] = &$numericPermissions[$index];
                    }

                    call_user_func_array(array($stmt, 'bind_param'), $bindParams);

                    if($stmt->execute()){
                        $result = $stmt->get_result();
                        if($result){
                            $map = array();
                            while($row = $result->fetch_assoc()){
                                $map[(int)$row['id']] = $row['slug'] ?? (string)$row['id'];
                            }
                            $result->free();
                            $permissions = array_map(function($permission) use ($map){
                                if(is_int($permission) && isset($map[$permission])){
                                    return $map[$permission];
                                }
                                return $permission;
                            }, $permissions);
                        }
                    }
                    $stmt->close();
                }
            }
        }

        return array_values(array_unique(array_filter($permissions)));
    }

    private static function tableExists($connection, string $tableName): bool
    {
        if(!($connection instanceof \mysqli)){
            return false;
        }

        $escapedTable = $connection->real_escape_string($tableName);
        $query = "SHOW TABLES LIKE '$escapedTable'";
        if($result = $connection->query($query)){
            $exists = $result->num_rows > 0;
            $result->free();
            return $exists;
        }
        return false;
    }

    private static function columnExists($connection, string $tableName, string $columnName): bool
    {
        if(!($connection instanceof \mysqli)){
            return false;
        }

        if(!self::tableExists($connection, $tableName)){
            return false;
        }

        $escapedTable = $connection->real_escape_string($tableName);
        $escapedColumn = $connection->real_escape_string($columnName);
        $query = "SHOW COLUMNS FROM `$escapedTable` LIKE '$escapedColumn'";

        if($result = $connection->query($query)){
            $exists = $result->num_rows > 0;
            $result->free();
            return $exists;
        }

        return false;
    }

    public static function userHasAccess(array $sessionData, array $allowedRoles = array(), array $allowedPermissions = array()): bool
    {
        if(empty($sessionData) || empty($sessionData['success']) || !$sessionData['success']){
            return false;
        }

        if(!empty($sessionData['isAdmin'])){
            return true;
        }

        $userRole = $sessionData['role'] ?? null;
        if($userRole && !empty($allowedRoles) && in_array($userRole, $allowedRoles, true)){
            return true;
        }

        if(empty($allowedRoles) && empty($allowedPermissions)){
            // Si no se especificaron restricciones, el usuario autenticado tiene acceso
            return true;
        }

        if(!empty($allowedPermissions)){
            $userPermissions = $sessionData['permissions'] ?? array();
            foreach($allowedPermissions as $permission){
                if(in_array($permission, $userPermissions, true)){
                    return true;
                }
            }
        }

        // Permitir acceso si se proporciona una lista de roles y el usuario coincide con alguno
        if($userRole && in_array($userRole, $allowedRoles, true)){
            return true;
        }

        return false;
    }

}