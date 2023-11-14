# Menu Order Service

A PHP and MySQL based microservice for handling menu and order management in a food delivery application, including placing orders and retrieving menu items.

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/your-username/menu-order-service.git
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

3. Configure the database in `config/database.php`.

4. Run the service:

    ```bash
    php -S localhost:8002
    ```

## API Endpoints

- `GET /menu-order-service/menu?restaurant_id=[restaurant_id]`: Retrieve the menu for a specific restaurant.
- `POST /menu-order-service/menu`: Add a new menu item.
- `POST /menu-order-service/orders`: Place a new order.
- `GET /menu-order-service/orders?user_id=[user_id]`: Retrieve orders made by a specific user.
- `GET /menu-order-service/orders?order_id=[order_id]`: Get details of a specific order.

## Dependencies

- PHP
- MySQL
- Composer
