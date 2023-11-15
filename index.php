<?php
// Include database configuration
require 'config/database.php';

// Get the HTTP method and path
$method = $_SERVER['REQUEST_METHOD'];

$request_uri = $_SERVER['REQUEST_URI'];

// Extract the path from REQUEST_URI
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove any base path from the path
$base_path = '/menu-order-service'; // Update this to your actual base path
$path = str_replace($base_path, '', $path);

// Parse the path to determine the request
$request = explode('/', trim($path, '/'));

// Connect to the database
$db = new Database();

if ($method === 'GET' && $request[0] === 'menu') {
    // To get the menu of a specific restaurant, pass the restaurant_id as a query parameter
    $restaurant_id = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : null;
    
    if ($restaurant_id) {
        $menu = $db->getMenuByRestaurant($restaurant_id);
        echo json_encode($menu);
    } else {
        // Return an error or handle the case where restaurant_id is missing
        echo json_encode(array('error' => 'Restaurant ID is missing'));
    }
} elseif ($method === 'POST' && $request[0] === 'orders') {
    // Add a new order
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = $db->createOrder($input);
    echo json_encode(array('order_id' => $order_id));
} elseif ($method === 'GET' && $request[0] === 'orders' && isset($_GET['user_id'])) {
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    // Retrieve orders
    $orders = $db->getOrdersByUser($user_id);
    echo json_encode($orders);
} elseif ($method === 'GET' && $request[0] === 'orders' && isset($_GET['order_id'])) {
    $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
    // Retrieve orders
    $order = $db->getOrderDetails($order_id);
    echo json_encode($order);
} elseif ($method === 'POST' && $request[0] === 'menu') {
    // Add a new menu item
    $input = json_decode(file_get_contents('php://input'), true);
    $menu_id = $db->addMenuItem($input);
    echo json_encode(array('menu_id' => $menu_id));
}
