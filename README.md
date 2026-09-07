# SupplySync

SupplySync is a simple college project for managing hotel kitchen inventory.
It helps an administrator manage products and suppliers while staff members
can view stock and request items.

## Main Features

### Admin

- Admin login and dashboard.
- Add, edit, search, and remove products.
- Assign products to suppliers.
- View product images, quantities, prices, and SKUs.
- Add, update, and remove suppliers.
- View low-stock products and reports.
- Approve or reject staff requests.

### Staff

- Staff login and dashboard.
- View and search current stock.
- See low-stock items.
- Request new inventory items.
- Track request status.

## Project Structure

```text
Supply_Inventory_Management/
|
|-- index.php                 # Opens the login page
|-- database.sql              # Database structure and sample data
|-- README.md                 # Project instructions
|-- .htaccess                 # Protects SQL files from public access
|
|-- admin/                    # Admin pages
|-- auth/                     # Login, logout, and password reset
|-- config/                   # Database connection
|-- css/                      # Shared styles
|-- images/                   # Product images
|-- staff/                    # Staff pages
|-- uploads/                  # Uploaded product images
```

## Technologies

- PHP 8.1+
- MySQL or MariaDB
- MySQLi
- HTML and CSS
- Bootstrap 5
- Font Awesome
- XAMPP

## Run the Project with XAMPP

1. Install XAMPP on Windows.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Copy this project into:

   ```text
   C:\xampp\htdocs\Supply_Inventory_Management
   ```

4. Open phpMyAdmin:

   ```text
   http://localhost/phpmyadmin
   ```

5. Import `database.sql` into phpMyAdmin. It creates the `SupplySync` database,
   tables, users, suppliers, products, SKUs, and sample inventory data.
6. Open the application:

   ```text
   http://localhost/Supply_Inventory_Management/
   ```

The application must be opened through Apache. PHP files will not run if they
are opened directly from File Explorer.

## Database Settings

The local XAMPP settings are stored in `config/db.php`:

```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "SupplySync";
```

Change these values if your MySQL username or password is different.

## Demo Login Accounts

These accounts are for the college demonstration database only:

| Role  | Email                  | Password    |
| ----- | ---------------------- | ----------- |
| Admin | `AifaShaikh@hotel.com` | `Aifa@123`  |
| Staff | `Jamie@hotel.com`      | `Jamie@123` |

## Important URLs

| Page            | URL                         |
| --------------- | --------------------------- |
| Login           | `/auth/login.php`           |
| Admin dashboard | `/admin/dashboard.php`      |
| Products        | `/admin/list_products.php`  |
| Suppliers       | `/admin/view_suppliers.php` |
| Reports         | `/admin/reports.php`        |
| Staff dashboard | `/staff/dashboard.php`      |
| Staff stock     | `/staff/view_stock.php`     |
| Staff requests  | `/staff/my_requests.php`    |

## Check PHP Syntax

Run this command from the project folder:

```powershell
Get-ChildItem -Path . -Filter *.php -Recurse | ForEach-Object {
    & 'C:\xampp\php\php.exe' -l $_.FullName
}
```

Every file should report `No syntax errors detected`.

## Security Notes

- Passwords are stored with PHP password hashing.
- The password helper was removed before submission.
- SQL files are blocked from public Apache access by `.htaccess`.
- The demo accounts and sample phone numbers are for local college use only.
- A real deployment should add CSRF protection, environment variables, email
  password reset, and stricter upload validation.

## Project Goal

This project demonstrates PHP sessions, role-based access, MySQL relationships,
prepared statements, CRUD operations, inventory tracking, and a simple staff
request workflow.
