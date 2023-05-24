# IJDORM
**IJDORM** is a lightweight and easy-to-use ORM (Object-Relational Mapping) library for PHP that provides a convenient way to interact with your MySQL database. With **IJDORM**, you can perform common **CRUD** (Create, Read, Update, Delete) operations on your database without having to write complex SQL queries.


### Features
- Execute custom queries
- Retrieve all rows from a table
- Retrieve the first row from a table
- Retrieve the last row from a table
- Select specific columns from a table
- Filter rows based on conditions
- Insert new rows into a table
- Update existing rows in a table
- Delete rows from a table
- Join tables based on specified conditions

## Requirements
- PHP 5.6 or higher
- MySQL database


## Installation
IJDORM can be installed via Composer, the dependency management tool for PHP. Run the following command in your project directory to add IJDORM as a dependency:

`composer require iamjohndev/ijd-orm`

After the installation, you can include the Composer autoloader in your PHP scripts to autoload the IJDORM classes:

`require_once 'vendor/autoload.php';`

## Usage
### Creating an Instance
To start using IJDORM, create an instance of the IJDORM class by providing the table name and a database connection object (an instance of mysqli):

```php
use IamJohnDevORM\IJDORM;

$tableName = 'your_table_name';
$dbConnection = new mysqli('localhost', 'username', 'password', 'database_name');
$dorm = new IJDORM($tableName, $dbConnection);
```

### Executing Custom Queries
You can execute custom SQL queries using the customQuery method. It returns an array of results.

```php
$query = 'SELECT * FROM your_table_name WHERE column = value';
$results = $dorm->customQuery($query);

```

### Retrieving All Rows
To retrieve all rows from a table, use the getAll method. It returns an array of results.
```php
$results = $dorm->getAll();
```

### Retrieving the First Row
To retrieve the first row from a table, use the getFirst method. It returns an associative array representing the row.
```php
$result = $dorm->getFirst();
```

### Retrieving the Last Row
To retrieve the last row from a table, use the getLast method. It returns an associative array representing the row.
```php
$result = $dorm->getLast();
```

### Selecting Specific Columns
To select specific columns from a table, use the select method. You can pass a string of column names separated by commas or use '*' to select all columns. This method returns the IJDORM object, allowing you to chain additional methods.
```php
$dorm->select('column1, column2');
// or
$dorm->select('*');

```

### Filtering Rows with Conditions
You can filter rows based on conditions using the where method. Pass a string representing the conditions in SQL syntax. This method also returns the IJDORM object for method chaining.

```php
$dorm->where('column1 = value AND column2 > value');
```

### Inserting Rows
To insert new rows into a table, use the insert method. Pass an associative array where the keys represent column names and the values represent the corresponding values. It returns the ID of the inserted row.
```php
$data = [
    'column1' => 'value1',
    'column2' => 'value2',
];
$insertedId = $dorm->insert($data);

```

### Updating Rows
To update existing rows in a table, use the update method. Pass an associative array where the keys represent column names and the values represent the new values. It returns the number of affected rows.
```php
$data = [
    'column1' => 'new_value1',
    'column2' => 'new_value2',
];
$affectedRows = $dorm->update($data);

```

### Deleting Rows
To delete rows from a table, use the delete method. It returns the number of affected rows.
```php
$affectedRows = $dorm->delete();
```

### Joining Tables
To perform a join operation between tables, use the join method. Pass the name of the table to join, the join condition, and an optional join type ('INNER' by default). This method also returns the IJDORM object.
```php
$dorm->join('other_table', 'your_table.column = other_table.column');
// or
$dorm->join('other_table', 'your_table.column = other_table.column', 'LEFT');
```

### Contributions
Contributions to IJDORM are welcome! If you find any issues or have suggestions for improvements, please create an issue or submit a pull request on the [GitHub repository.](https://github.com/IamJohnDev/IJDORM "GitHub repository.")

### License
**IJDORM** is open-source software licensed under the MIT License. See the [LICENSE](https://github.com/PseudoDevs/IamJohnDevORM/blob/main/LICENSE.txt "LICENSE") file for more information.
