<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'menu_order_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }

    public function getMenuByRestaurant($restaurant_id) {
        $query = 'SELECT * FROM menu_items WHERE restaurant_id = :restaurant_id';
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':restaurant_id', $restaurant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMenuItem($menu_item) {
        $query = 'INSERT INTO menu_items (name, description, price, restaurant_id) VALUES (:name, :description, :price, :restaurant_id)';
        $stmt = $this->getConnection()->prepare($query);

        // Bind parameters
        $stmt->bindParam(':name', $menu_item['name']);
        $stmt->bindParam(':description', $menu_item['description']);
        $stmt->bindParam(':price', $menu_item['price']);
        $stmt->bindParam(':restaurant_id', $menu_item['restaurant_id']);

        // Execute the query
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    public function createOrder($order) {
        // Create the order record
        $orderQuery = 'INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total_amount)';
        $orderStmt = $this->getConnection()->prepare($orderQuery);
        $orderStmt->bindParam(':user_id', $order['user_id']);
        $orderStmt->bindParam(':total_amount', $order['total_amount']);
        $orderStmt->execute();

        // Get the ID of the created order
        $order_id = $this->conn->lastInsertId();

        // Add individual menu items to the order
        foreach ($order['items'] as $item) {
            $this->addOrderItem($order_id, $item['menu_id'], $item['quantity']);
        }

        return $order_id;
    }

    private function addOrderItem($order_id, $menu_id, $quantity) {
        // Add individual menu items to the order
        $orderItemQuery = 'INSERT INTO order_items (order_id, menu_id, quantity) VALUES (:order_id, :menu_id, :quantity)';
        $orderItemStmt = $this->getConnection()->prepare($orderItemQuery);
        $orderItemStmt->bindParam(':order_id', $order_id);
        $orderItemStmt->bindParam(':menu_id', $menu_id);
        $orderItemStmt->bindParam(':quantity', $quantity);
        $orderItemStmt->execute();
    }

    public function getOrdersByUser($user_id) {
        $query = 'SELECT o.id as order_id, o.user_id, o.total_amount, 
                          CONCAT("[", GROUP_CONCAT(
                              JSON_OBJECT("menu_name", mi.name, "quantity", oi.quantity)
                          SEPARATOR ","), "]") AS items,
                          mi.restaurant_id as restaurant_id
                  FROM orders AS o
                  JOIN order_items AS oi ON o.id = oi.order_id
                  JOIN menu_items AS mi ON oi.menu_id = mi.id
                  WHERE o.user_id = :user_id
                  GROUP BY o.id';
    
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Fetch restaurant names via API call using cURL
        foreach ($orders as &$order) {
            $restaurantId = $order['restaurant_id'];
            $restaurantInfo = $this->getRestaurantInfo($restaurantId);
            $firstRestaurant = reset($restaurantInfo);
            $order['restaurant_name'] = $firstRestaurant['name'];
        }
    
        return $orders;
    }  

    private function getRestaurantInfo($restaurantId) {
        // Initialize cURL session
        $ch = curl_init();
        
        // Set cURL options
        $url = "http://localhost/FoodDeliveryApp/restaurant-listing-service/restaurants?id=$restaurantId";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        // Execute cURL session and get the response
        $response = curl_exec($ch);
    
        // Close cURL session
        curl_close($ch);
    
        // Decode the JSON response
        $restaurantInfo = json_decode($response, true);
    
        return $restaurantInfo;
    }

    public function getOrderDetails($order_id) {
        $query = 'SELECT o.id as order_id, o.user_id, o.total_amount, 
                        CONCAT("[", GROUP_CONCAT(
                            JSON_OBJECT("menu_name", mi.name, "quantity", oi.quantity)
                        SEPARATOR ","), "]") AS items,
                        mi.restaurant_id as restaurant_id
                    FROM orders AS o
                    JOIN order_items AS oi ON o.id = oi.order_id
                    JOIN menu_items AS mi ON oi.menu_id = mi.id
                    WHERE o.id = :order_id
                    GROUP BY o.id';
    
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Fetch restaurant names via API call using cURL
        foreach ($orders as &$order) {
            $restaurantId = $order['restaurant_id'];
            $user_id = $order['user_id'];
            $restaurantInfo = $this->getRestaurantInfo($restaurantId);
            $order['restaurant'] = $restaurantInfo;
            $userInfo = $this->getUserDetails($user_id);
            $order['user'] = $userInfo;
        }
    
        return $orders;
    }

    private function getUserDetails($user_id) {
        $userDetailsApiUrl = 'http://localhost/FoodDeliveryApp/user-service/users?id=' . $user_id;
    
        // Initialize cURL session
        $ch = curl_init($userDetailsApiUrl);
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute cURL session and fetch user details
        $userDetailsJson = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Error fetching user details: ' . curl_error($ch);
            return null;
        }
    
        // Close cURL session
        curl_close($ch);
    
        // Decode the JSON response
        $userDetails = json_decode($userDetailsJson, true);
    
        // Return user details
        return $userDetails;
    }
    
}
