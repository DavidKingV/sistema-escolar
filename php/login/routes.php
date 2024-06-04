<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

cargarVariablesEnv();
$secret_key = $_ENV['KEY'];
$tiempo_vida = $_ENV['LIFE_TIME'];

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

//casos de login
    switch($action){
        case 'login':
            parse_str($_POST['data'], $data);
            $user = $data['user'] ?? '';
            $pass = $data['password'] ?? '';

            $control = new LoginControl($con);
            $login = $control -> indexLogin($user, $pass);

            if($login['success']){
                //se crea una sesion
                session_set_cookie_params($tiempo_vida);
                session_start();
                
                $_SESSION['userId'] = $login['userId'];

                //se crea un token utilizando jwt
                $payload = [
                    "userId" => $login['userId'],
                    "userName" => $user
                ];

                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                //se genera un cookie con el token
                setcookie("auth", $jwt, time() + ($tiempo_vida), "/", "", 1, 1);

                header('Content-Type: application/json');
                echo json_encode($login);

            }elseif(!$login['success']){
                header('Content-Type: application/json');
                echo json_encode($login);
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "message" => "Error en la consulta de la base de datos"));
            }
        break;

        case 'preconfirm_action':
            //verificar si el usuario tiene una sesion valida
            if(isset($_COOKIE['auth'])){ 
                $jwt = $_COOKIE['auth'];
                $control = new LoginControl($con);
                $login = $control -> VerifySessionCookie($jwt);

                if($login['status'] === 'success'){
                    $user_id = $login['user_id'];
                    $verify = $control -> VerifyCurrentUserPassword($user_id, $_POST['password']);

                    if($verify['status'] === 'success'){
                        header('Content-Type: application/json');
                        echo json_encode($verify);
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode($verify);
                    }
                }else{
                    header('Content-Type: application/json');
                    echo json_encode($login);
                }
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("status" => "error", "message" => "Sesión inválida"));
            }
        break;

//-----------------------------------------------casos para los productos-------------------------------------------------//       
        case 'get_products':

            $control = new ProductsControl($con);
            $products = $control -> getProducts();

            header('Content-Type: application/json');
            echo json_encode($products);

        break;

        case 'get_single_product':
            $id_facturapi = $_POST['id_facturapi'];

            $control = new ProductsControl($con);
            $product = $control -> GetProduct($id_facturapi);

            header('Content-Type: application/json');
            echo json_encode($product);
        break;

        case 'get_price_product':
            $id_facturapi = $_POST['id_facturapi'];

            $control = new FacturapiServices();
            $prices = $control -> getProduct($id_facturapi);

            header('Content-Type: application/json');
            echo json_encode($prices);
        break;

        case 'add_product':
            //verificar si el usuario tiene una sesion valida
            if(isset($_COOKIE['auth'])){
        
                parse_str($_POST['data'], $data);

                $control_facturapi = new FacturapiServices();
                $facturapi = $control_facturapi -> addProduct($data);

                $control = new ProductsControl($con);
              
                if(isset($facturapi->id)){
                    $data_product = array(
                        "id_facturapi" => $facturapi->id,
                        "sku" => $facturapi->sku,
                        "product_name" => $facturapi->description,
                        "price" => $facturapi->price,
                        "unit_name" => $facturapi->unit_name
                    );

                    $result_add_product_db = $control -> addProduct($data_product);
                    if($result_add_product_db['status'] === 'success'){
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "success", "message" => "Producto agregado"));
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "error", "message" => $result_add_product_db['message']));
                    }

                }elseif(isset($facturapi->message)){
                    header('Content-Type: application/json');
                    echo json_encode(array("status" => "error", "message" => $facturapi->message));
                }else{
                    header('Content-Type: application/json');
                    echo json_encode(array("status" => "error", "message" => "Error en la consulta de la base de datos"));
                }
        
               
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("status" => "error", "message" => "Sesión inválida"));
            }
    
        break;

        case 'delete_product':
            //verificar si el usuario tiene una sesion valida
            if(isset($_COOKIE['auth'])){
                $id_facturapi = $_POST['id_facturapi'];

                $control = new ProductsControl($con);
                $result = $control -> deleteProduct($id_facturapi);

                if($result['status'] === 'success'){
                    $control_facturapi = new FacturapiServices();
                    $facturapi = $control_facturapi -> deleteProduct($id_facturapi);

                    if($facturapi->id){
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "success", "message" => "Producto eliminado"));
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "error", "message" => $facturapi->message));
                    }
                }else{
                    header('Content-Type: application/json');
                    echo json_encode(array("status" => "error", "message" => $result['message']));
                }
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("status" => "error", "message" => "Sesión inválida"));
            }
        break;

        case 'get_data_edit_product':
            //verificar si el usuario tiene una sesion valida
            if(isset($_COOKIE['auth'])){
                $id_facturapi = $_POST['id_facturapi'];

                $control = new ProductsControl($con);
                $result = $control -> getDataEditProduct($id_facturapi);

                if($result['status'] === 'success'){
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }else{
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("status" => "error", "message" => "Sesión inválida"));
            }
        break;

        case 'edit_product':
            //verificar si el usuario tiene una sesion valida
            if(isset($_COOKIE['auth'])){
                parse_str($_POST['data'], $data);

                $control = new ProductsControl($con);
                $result = $control -> editProduct($data);

                if($result['status'] === 'success'){
                   $control_facturapi = new FacturapiServices();
                    $facturapi = $control_facturapi -> updateProduct($data);

                    if($facturapi->id){
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "success", "message" => "Producto actualizado"));
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode(array("status" => "error", "message" => $facturapi->message. 'Se edito el producto en la base de datos, pero no se pudo editar en facturapi, por favor contactese con el administrador'));
                    }
                }else{
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }
            }else{
                header('Content-Type: application/json');
                echo json_encode(array("status" => "error", "message" => "Sesión inválida"));
            }
            
        break;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////       
//-----------------------------------------------casos para los clientes-------------------------------------------------//    

        




















///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        case 'logout':
            $control = new LoginControl($con);
            $control -> logout();
            header('Content-Type: application/json');
            echo json_encode(array("status" => "success", "message" => "Sesión cerrada"));
        break;
        default:
        // code...
        break;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    switch ($_GET['action']) {

        case 'get_name_clients':
            $control = new ClientsControl($con);
            $clients = $control -> GetNameCostumers();

            header('Content-Type: application/json');
            echo json_encode($clients);
        break;

    }
    
}