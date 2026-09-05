# SupplySync

## My Project Introduction

SupplySync is my student project for managing the stock and supply requests of a hotel kitchen. I created it to replace a manual stock-recording process with a simple web application.

The system has two types of users:

- **Administrator:** manages products, suppliers, reports, and staff requests.
- **Staff member:** views available stock, checks low-stock items, and sends requests for new items.

I developed this project using PHP, MySQL, HTML, CSS, Bootstrap, and Font Awesome. The application runs locally using XAMPP.

## Project Purpose

In a hotel kitchen, staff need to know which ingredients are available and administrators need to know when stock is low. SupplySync helps with these tasks by keeping product information in a database.

The main goals of my project are:

1. Store product, supplier, user, and request information in one database.
2. Allow administrators to manage kitchen inventory.
3. Allow staff members to request items without using paper forms.
4. Show low-stock products quickly.
5. Keep administrator and staff features separate using login roles.

## Main Features

### Login and User Roles

- Users log in with an email address and password.
- Passwords are checked using PHP password hashing.
- The user is redirected to the correct dashboard based on their role.
- Staff cannot open administrator pages, and administrators cannot use staff-only pages.
- Users can log out and end their session.

### Administrator Features

- View dashboard totals for products, suppliers, low-stock products, and pending requests.
- Add new products to inventory.
- Edit product names, quantities, prices, and images.
- Delete products.
- Search and browse products.
- Add and view suppliers.
- See products that have low stock.
- View inventory and supplier reports.
- Approve or reject staff stock requests.
- Add an approved request to the product inventory.

### Staff Features

- View the staff dashboard.
- View all available products.
- Search the current stock.
- View low-stock products.
- Submit a request for an item and quantity.
- Add notes to a request.
- View the status of previous requests.

### Product Images

Products can have images stored in the `uploads/` folder. The administrator can upload an image while editing a product. The product record stores the image filename in the database.

## How the System Works

The basic workflow is:

1. An administrator creates suppliers.
2. The administrator adds products and connects each product to a supplier.
3. Staff members log in and view the available stock.
4. A staff member submits a request when an item is needed.
5. The request appears in the administrator's Staff Requests page with a `pending` status.
6. The administrator selects a supplier and approves or rejects the request.
7. When approved, the item is added to the product inventory and the request status changes to `approved`.
8. Staff members can see the final result in My Requests.

The approval process uses a database transaction. This means the product is added and the request is approved together. If one operation fails, the changes are rolled back so that the database does not show an approved request without a product.

## Detailed Project Structure

```text
supplysync/
|
|-- index.php
|-- hash.php
|-- README.md
|
|-- admin/
|   |-- dashboard.php
|   |-- add_product.php
|   |-- edit_products.php
|   |-- delete_product.php
|   |-- list_products.php
|   |-- add_supplier.php
|   |-- view_suppliers.php
|   |-- low_stock.php
|   |-- reports.php
|   |-- staff_requests.php
|
|-- auth/
|   |-- login.php
|   |-- logout.php
|   |-- forgot_password.php
|
|-- config/
|   |-- db.php
|
|-- staff/
|   |-- dashboard.php
|   |-- view_stock.php
|   |-- low_stock.php
|   |-- request_item.php
|   |-- my_requests.php
|
|-- css/
|   |-- style.css
|
|-- images/
|
|-- uploads/
```

### Root Files

| File | Purpose |
| --- | --- |
| `index.php` | Starting page. It redirects visitors to the login page. |
| `hash.php` | Development helper that creates password hashes. It should not be left publicly accessible in production. |
| `README.md` | Project explanation and setup guide. |

### `admin/` Folder

| File | Purpose |
| --- | --- |
| `dashboard.php` | Shows administrator statistics and recent activity. |
| `add_product.php` | Adds a new product, quantity, supplier, price, and optional image name. |
| `edit_products.php` | Updates an existing product and can upload a replacement image. |
| `delete_product.php` | Deletes a selected product. |
| `list_products.php` | Displays, searches, paginates, and manages products. |
| `add_supplier.php` | Adds supplier contact information. |
| `view_suppliers.php` | Displays the suppliers stored in the database. |
| `low_stock.php` | Shows products with a quantity below the low-stock limit. |
| `reports.php` | Shows inventory value, product information, and supplier-related reports. |
| `staff_requests.php` | Lists staff requests and allows an administrator to approve or reject them. |

### `auth/` Folder

| File | Purpose |
| --- | --- |
| `login.php` | Checks the user's email and password and starts a session. |
| `logout.php` | Clears the session and returns the user to login. |
| `forgot_password.php` | Creates a temporary reset code for a registered email address. |

### `config/` Folder

| File | Purpose |
| --- | --- |
| `db.php` | Creates the MySQLi connection to the `SupplySync` database. Most PHP pages include this file. |

### `staff/` Folder

| File | Purpose |
| --- | --- |
| `dashboard.php` | Shows staff stock totals and recent requests. |
| `view_stock.php` | Allows staff to view and search products. |
| `low_stock.php` | Shows products that need attention because their quantity is low. |
| `request_item.php` | Provides the form for creating a stock request. |
| `my_requests.php` | Shows the logged-in staff member's request history and statuses. |

### `css/`, `images/`, and `uploads/`

- `css/style.css` contains common project styling.
- `images/` is for static images used by the interface.
- `uploads/` stores product images uploaded through the application.
- Bootstrap and Font Awesome are loaded from online CDNs in the PHP pages.

## Database Design

The project uses a MySQL database called `SupplySync`. The main tables are:

### `users`

Stores login details and the user's role.

Important columns:

- `id` - unique user ID.
- `name` - user's display name.
- `email` - login email address.
- `password` - password hash.
- `role` - either `admin` or `staff`.

### `suppliers`

Stores the companies or people who supply kitchen products. Important columns include `supplier_name`, `phone`, `email`, and `address`.

### `products`

Stores the kitchen inventory.

Important columns:

- `id` - unique product ID.
- `product_name` - name of the item.
- `quantity` - current stock quantity.
- `supplier_id` - supplier connected to the product.
- `price` - price per item or unit.
- `image` - uploaded image filename, when available.

### `stock_requests`

Stores requests made by staff members.

Important columns:

- `item_name` - requested item.
- `quantity_needed` - amount requested.
- `notes` - optional explanation.
- `staff_id` - user who made the request.
- `status` - `pending`, `approved`, or `rejected`.
- `created_at` - date and time of the request.

### `password_resets`

Stores temporary password-reset codes and their expiry time. In this student version, the generated code is displayed on the page for testing instead of being sent through a real email service.

## Installation Using XAMPP

### Requirements

- Windows computer.
- XAMPP.
- Apache server.
- MySQL or MariaDB.
- PHP with the `mysqli` extension.
- Web browser such as Chrome, Edge, or Firefox.

### Steps

1. Install XAMPP.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Put the project folder inside:

   ```text
   D:\xampp\htdocs\supplysync
   ```

4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database named `SupplySync`.
6. Create the required tables: `users`, `suppliers`, `products`, `stock_requests`, and `password_resets`.
7. Check the database settings in `config/db.php`:

   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "SupplySync";
   ```

8. Create at least one admin user, one staff user, and one supplier.
9. Open the project at `http://localhost/supplysync/`.

The application should be opened through Apache. Opening a PHP file directly from the file system will not run the PHP code or connect to MySQL.

## Creating Passwords

Passwords should be saved as hashes, not as normal text. PHP can create a hash using:

```php
password_hash('your-password', PASSWORD_DEFAULT);
```

The login page checks the hash with `password_verify()`. The `hash.php` file is only a development helper and should be deleted or protected before deploying the project to a real server.

## Important URLs

| URL | User | Purpose |
| --- | --- | --- |
| `/` | Everyone | Opens the login page. |
| `/auth/login.php` | Everyone | User login. |
| `/auth/forgot_password.php` | Everyone | Password reset request. |
| `/admin/dashboard.php` | Admin | Administrator dashboard. |
| `/admin/list_products.php` | Admin | Product list and inventory management. |
| `/admin/staff_requests.php` | Admin | Approve or reject staff requests. |
| `/admin/reports.php` | Admin | Inventory reports. |
| `/staff/dashboard.php` | Staff | Staff dashboard. |
| `/staff/view_stock.php` | Staff | View and search stock. |
| `/staff/request_item.php` | Staff | Create a stock request. |
| `/staff/my_requests.php` | Staff | View personal request history. |

## Technologies Used

- **PHP:** server-side application logic.
- **MySQL:** stores users, products, suppliers, and requests.
- **MySQLi:** connects PHP to MySQL and supports prepared statements.
- **HTML:** page structure and forms.
- **CSS:** project-specific styling.
- **Bootstrap 5:** responsive layout and components.
- **Font Awesome:** icons used in navigation and buttons.
- **XAMPP:** local Apache and MySQL development environment.

## Validation

I can check the syntax of all PHP files from the project directory with this PowerShell command:

```powershell
Get-ChildItem -Path . -Filter *.php -Recurse | ForEach-Object {
    & 'D:\xampp\php\php.exe' -l $_.FullName
}
```

The command should report `No syntax errors detected` for every PHP file.

## Security and Future Improvements

This is a student project, so there are features I would improve before using it in a real hotel:

- Add CSRF tokens to forms that change data.
- Use environment variables instead of storing database credentials in the PHP file.
- Send password-reset codes by email instead of displaying them on the page.
- Add a proper password-reset form that verifies the code and changes the password.
- Validate image file types, file sizes, and file extensions more strictly.
- Use POST requests for delete actions and add CSRF protection.
- Add database migrations or an SQL export file for easier installation.
- Add automated tests for login, product management, and request approval.
- Add an audit log for administrator actions.

## Conclusion

SupplySync helped me practice PHP, MySQL, database relationships, user sessions, form handling, prepared statements, and role-based access control. The project demonstrates how a small inventory system can support the daily work of a hotel kitchen and make stock requests easier to track.
