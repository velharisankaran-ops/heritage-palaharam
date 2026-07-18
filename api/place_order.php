<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_connect.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->customer_name) &&
    !empty($data->phone) &&
    !empty($data->address) &&
    !empty($data->cart_data) &&
    !empty($data->total_price)
){
    $customer_name = $conn->real_escape_string($data->customer_name);
    $phone = $conn->real_escape_string($data->phone);
    $address = $conn->real_escape_string($data->address);
    // encode the cart array to JSON string for the DB
    $cart_data = $conn->real_escape_string(json_encode($data->cart_data));
    $total_price = floatval($data->total_price);

    $sql = "INSERT INTO orders (customer_name, phone, address, cart_data, total_price) 
            VALUES ('$customer_name', '$phone', '$address', '$cart_data', '$total_price')";

    if ($conn->query($sql) === TRUE) {
        http_response_code(201);
        echo json_encode(array("message" => "Order was created successfully.", "order_id" => $conn->insert_id));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create order. " . $conn->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create order. Data is incomplete."));
}

$conn->close();
?>
