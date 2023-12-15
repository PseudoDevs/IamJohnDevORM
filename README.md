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
- PHP 8.0 or higher
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
use iamjohndev\IJDORM;

// Create a database connection
$connection = new mysqli('localhost', 'username', 'password', 'database');

// Create an instance of IJDORM
$dorm = new IJDORM($connection, 'users');

```

# Retrieving Data
**Find a Record**
To retrieve a single record from the database by its ID, use the find method:
```php
$user = $dorm->find(1);
```

This will return an object representing the retrieved record.

# Retrieve All Records
To retrieve all records from the table, use the **all()** method:
```php
$users = $dorm->all();
```

This will return an array of associative arrays, where each array represents a record.

# Custom Queries
If you need to execute custom queries, you can use the **get()** method:
```php
$query = "SELECT * FROM users WHERE age > ?";
$users = $dorm->get($query, [18]);
```

This will return an array of associative arrays, where each array represents a record that matches the query.

# Creating Records
To create a new record in the database, use the **create()** method:
```php
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
];

$result = $dorm->create($data);

if ($result['success']) {
    echo 'Record created successfully';
} else {
    echo 'Failed to create record: ' . $result['errors'];
}
```

# Creating Records with Rules
To create a new record in the database, use the **create()** method:
```php
$rules = [
    'author_name' => 'required|min:3|max:255|unique:authors,author_name',
];
$createAuthor = $orm->create([
    'author_name' => $request->author_name,
], $rules);

if ($createAuthor['success']) {
    echo "Author created successfully";
} else {
    foreach ($createAuthor['errors'] as $field => $errors) {
        foreach ($errors as $error) {
            $_SESSION['errors'][$field][] = $error;
        }
    }
}
```

The **create** method takes an associative array representing the data to be inserted. You can also provide validation rules to validate the data before insertion.

# Updating Records
To update an existing record in the database, use the **update()** method:
```php
$id = 1;
$data = [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
];

<!-- or -->
$id = 1;
$data = array(
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
);

$result = $dorm->update($id, $data);

if ($result['success']) {
    echo 'Record updated successfully';
} else {
    echo 'Failed to update record: ' . $result['errors'];
}
```

The **update** method takes the ID of the record to be updated and an associative array representing the updated data. Like the create method, you can provide validation rules to validate the data before updating.

# Deleting Records
To delete a record from the database, use the **delete()** method:
```php
$id = 1;

$result = $dorm->delete($id);

if ($result['success']) {
    echo 'Record deleted successfully';
} else {
    echo 'Failed to delete record: ' . $result['errors'];
}

```
The **delete** method takes the ID of the record to be deleted.

# Chaining Methods
You can chain multiple methods together to build complex queries:
```php
$users = $dorm->where('age', '>', 18)
              ->orderBy('name', 'asc')
              ->limit(10)
              ->get();

foreach ($users as $user) {
    echo $user['name'] . ' - ' . $user['email'] . '<br>';
}

```

In this example, we are using method chaining to build a query. We start by using the **where** method to specify a condition (age > 18). Then, we use the **orderBy** method to sort the results by name in ascending order. Next, we use the limit method to limit the number of records to retrieve (in this case, 10). Finally, we call the **get** method to execute the query and retrieve the results.

The retrieved records are then iterated over in a foreach loop, and the name and email of each user are echoed.


### Contributions
Contributions to IJDORM are welcome! If you find any issues or have suggestions for improvements, please create an issue or submit a pull request on the [GitHub repository.](https://github.com/IamJohnDev/IJDORM "GitHub repository.")

### License
**IJDORM** is open-source software licensed under the MIT License. See the [LICENSE](https://github.com/PseudoDevs/IamJohnDevORM/blob/main/LICENSE.txt "LICENSE") file for more information.
