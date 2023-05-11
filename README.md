# IJDORM
**IJDORM** is a lightweight and easy-to-use ORM (Object-Relational Mapping) library for PHP that provides a convenient way to interact with your MySQL database. With **IJDORM**, you can perform common **CRUD** (Create, Read, Update, Delete) operations on your database without having to write complex SQL queries.

### Features
- Simple and intuitive API
- Supports SELECT, INSERT, UPDATE, and DELETE operations
- Supports basic WHERE clauses for filtering data
- Easy to integrate with your PHP project
- Lightweight and fast

### Requirements
PHP 8.0 or higher
MySQL 5.5 or higher

# Installation
You can install **IJDORM** using **Composer**:
```php
composer require iamjohndev/ij-dorm
```
## Usage
First, you need to create a new instance of the **IJDORM** class and pass your database credentials to the constructor:

```php
require_once 'vendor/autoload.php';
use IamJohnDevORM\IJDORM;

$db = new IJDORM('localhost', 'root', '', 'my_database');
```
**SELECT Query**
To retrieve data from a table, you can use the select method:
```php
// Select all rows from the users table
$users = $db->select('users');

// Select only the name and email columns from the users table
$users = $db->select('users', ['name', 'email']);

// Select only the users with an id greater than 5
$users = $db->select('users', '*', 'id > 5');

```

The **`select`** method returns an array of associative arrays, where each array represents a row in the table.

**INSERT Query**
To insert data into a table, you can use the **insert** method:
```php
// Insert a new user into the users table
$id = $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);

```
The **insert** method returns the ID of the last inserted row.

**UPDATE Query**
To update data in a table, you can use the **update** method:
```php
// Update the email of the user with id 1
$rows_affected = $db->update('users', ['email' => 'new_email@example.com'], 'id = 1');

```

The **update** method returns the number of affected rows.

**DELETE Query**
To delete data from a table, you can use the **delete** method:
```php
// Delete the user with id 1 from the users table
$rows_affected = $db->delete('users', 'id = 1');

```

The **delete** method returns the number of affected rows.

## License
**IJDORM** is open-source software licensed under the **MIT license.**


