# Restaurant Listing Service

A PHP and MySQL based microservice for managing restaurant listings, including adding new restaurants and retrieving details of existing ones

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/your-username/restaurant-listing-service.git
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

3. Configure the database in `config/database.php`.

4. Run the service:

    ```bash
    php -S localhost:8001
    ```

## API Endpoints

- `GET /restaurant-listing-service/restaurants`: Retrieve all restaurants.
- `GET /restaurant-listing-service/restaurants?id=[restaurant_id]`: Get details of a specific restaurant.
- `POST /restaurant-listing-service/restaurants`: Add a new restaurant.

## Dependencies

- PHP
- MySQL
- Composer
