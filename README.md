## About Facility Booking Module

This Laravel-based application is designed to manage the booking of facilities such as Clubhouse and Tennis Court. It allows users to book facilities, view existing bookings, and clear all session data. 

**Note:**
(As per the requirement provided)
- **Data Storage**: This application uses PHP sessions for data management instead of a database.
- **UI**: The application does not use UI view files. All interactions are done via API endpoints. Use Postman to test and interact with the API endpoints.
- **Session-Based**: Data is stored in the session, so until the session is cleared, bookings will be visible. Clearing the session will remove all bookings.

  
## Features

- **Facility Booking**: Reserve facilities with specified time slots.
- **Booking List**: View all current bookings.
- **Clear Bookings**: Clear all session bookings.

## Requirements

- PHP >= 7.4
- Laravel >= 8.x
- Composer

## Installation

1. **Clone the Repository**

    ```bash
    git clone https://github.com/nishanth186/Facility-booking-module.git
    cd Facility-booking-module
    ```

2. **Install Dependencies**

    ```bash
    composer install
    ```

3. **Set Up Environment**

    Copy the `.env.example` file to `.env` and configure your environment variables:

    ```bash
    copy .env.example .env
    ```

4. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

5. **Start the Development Server**

    ```bash
    php artisan serve
    ```

    The application will be available at `http://127.0.0.1:8000`.

## Endpoints

- **Book a Facility**

    - **Method**: `POST`
    - **Endpoint**: `/booking/add`
    - **Parameters**:
      - `facility` (string): The facility to book.
      - `date` (string): Booking date (YYYY-MM-DD).
      - `start_time` (string): Start time (H:i).
      - `end_time` (string): End time (H:i).
- **Request Example**:

  ```json
  {
    "facility": "Clubhouse",   //Should be Clubhouse or Tennis Court
    "date": "2024-10-26",
    "start_time": "16:00",
    "end_time": "22:00"
  }

- **List Bookings**

    - **Method**: `GET`
    - **Endpoint**: `/booking/list`

- **Request Example**:
  ```json
  {
      "success": true,
      "bookings": [
        {
          "facility": "Clubhouse",
          "date": "2024-10-26",
          "start_time": "16:00",
          "end_time": "22:00"
        }
      ]
    }     

- **Clear All Bookings**

    - **Method**: `POST`
    - **Endpoint**: `/booking/clear`
- **Request Example**:
  ```json
    {
      "success": true,
      "message": "All session data cleared"
    }

## Code Structure

- **Controllers**: `app/Http/Controllers/BookingController.php`
- **Routes**: `routes/web.php`

## Testing

To run tests, use:

```bash
php artisan test
```

## Contributing

Feel free to fork this repository and submit pull requests. For major changes, please open an issue first to discuss the proposed changes.

## License

This project is open-source and available under the [MIT License](https://opensource.org/licenses/MIT).
