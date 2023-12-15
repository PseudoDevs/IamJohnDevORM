<?php

// use mysqli driver
namespace iamjohndev;

class IJDORM
{
    private $connection;
    private $table;
    private $whereConditions = [];
    private $limitValue;
    private $orderByColumn;
    private $groupByColumn;
    private $statement;

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function find($id)
    {
        $statement = $this->connection->prepare("SELECT * FROM $this->table WHERE id = ?");
        $statement->execute([$id]);
        $result = $statement->get_result()->fetch_object();
        return $result;
    }

    public function all()
    {
        $query = "SELECT * FROM $this->table";
        $values = [];

        if (!empty($this->whereConditions)) {
            $whereClause = implode(" AND ", $this->whereConditions);
            $query .= " WHERE $whereClause";
            $values = array_column($this->whereConditions, 'value');
        }

        if (!empty($this->groupByColumn)) {
            $query .= " GROUP BY $this->groupByColumn";
        }

        if (!empty($this->orderByColumn)) {
            $query .= " ORDER BY $this->orderByColumn";
        }

        if (!empty($this->limitValue)) {
            $query .= " LIMIT $this->limitValue";
        }

        $statement = $this->connection->prepare($query);
        $statement->execute($values);

        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function get()
    {
        $query = "SELECT * FROM $this->table";
        $values = [];
        $types = '';
        $results = [];

        if ($this->whereConditions) {
            $whereClause = implode(" AND ", array_column($this->whereConditions, 'condition'));
            $query .= " WHERE $whereClause";

            foreach ($this->whereConditions as $condition) {
                if (is_array($condition['value'])) {
                    $values = array_merge($values, $condition['value']);
                } else {
                    $values[] = $condition['value'];
                }
            }

            $types = str_repeat('s', count($values)); // Assuming all values are strings
        }

        if ($this->groupByColumn) {
            $query .= " GROUP BY $this->groupByColumn";
        }

        if ($this->orderByColumn) {
            $query .= " ORDER BY $this->orderByColumn";
        }

        if ($this->limitValue) {
            $query .= " LIMIT ?";
            $values[] = $this->limitValue;
            $types .= 'i'; // Assuming the limit value is an integer
        }

        $statement = $this->connection->prepare($query);

        if ($values) {
            $statement->bind_param($types, ...$values);
        }

        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_object()) {
            $results[] = $row;
        }

        $statement->close(); // Close the statement
        return $results;
    }



    public function first()
    {
        $query = "SELECT * FROM $this->table LIMIT 1";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->get_result()->fetch_object();

        $statement->close(); // Close the statement
        return $result;
    }

    public function last()
    {
        $query = "SELECT * FROM $this->table ORDER BY id DESC LIMIT 1";

        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->get_result()->fetch_object();

        $statement->close(); // Close the statement
        return $result;
    }

    public function create($data, $rules = [])
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!empty($rules)) {
            $validationResult = $this->validate($data, $rules);

            if (!$validationResult['valid']) {
                // Validation failed, return the error messages
                return ['success' => false, 'errors' => $validationResult['errors']];
            }
        }

        // Validation succeeded or no rules provided, proceed with the create operation
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);

        $query = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
        $statement = $this->connection->prepare($query);

        if ($statement) {
            $statement->bind_param(str_repeat('s', count($values)), ...$values);
            $statement->execute();

            $success = $statement->affected_rows > 0;
            $statement->close(); // Close the statement

            return ['success' => $success];
        } else {
            // Failed to prepare the statement
            return ['success' => false, 'errors' => 'Failed to prepare the statement.'];
        }
    }

    public function update($id, $data, $rules = [])
{
    $validationResult = $this->validate($data, $rules);

    if (!$validationResult['valid']) {
        // Validation failed, return the error messages
        return ['success' => false, 'errors' => $validationResult['errors']];
    }

    // Validation succeeded, proceed with the update operation
    $updates = implode(', ', array_map(function ($key) {
        return "$key = ?";
    }, array_keys($data)));

    $values = array_values($data);
    $values[] = $id;

    $types = str_repeat('s', count($values)-1).'i'; // define types

    if (!$this->statement) { // reuse prepared statement
        $query = "UPDATE {$this->table} SET $updates WHERE id = ?";
        $this->statement = $this->connection->prepare($query);
    }

    $this->statement->bind_param($types, ...$values); // use types in bind_param
    $this->statement->execute();

    $success = $this->statement->affected_rows > 0;
    $rows_affected = $this->statement->affected_rows;

    if($success) {
        return ['success' => $success, 'rows_affected' => $rows_affected];
    } else {
        return ['success' => $success, 'errors' => $this->statement->error];
    }
}


    /*
     * This method is used to delete a record from the table
     * Usage: $orm->delete(1);
     * This will delete the record with id = 1
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $statement = $this->connection->prepare($query);

        if ($statement) {
            $statement->bind_param('i', $id);
            $statement->execute();

            $rowsAffected = $statement->affected_rows;
            $statement->close(); // Close the statement

            return ['success' => $rowsAffected > 0, 'rowsAffected' => $rowsAffected];
        } else {
            // Failed to prepare the statement
            return ['success' => false, 'errors' => 'Failed to prepare the statement.'];
        }
    }

    /*
     * This method is used to add a where condition to the query
     * Usage: $orm->where('id', '=', 1)->get();
     * This will return all records where id = 1
     */

    public function where($column, $operator, $value)
    {
        $this->whereConditions[] = [
            'condition' => "$column $operator ?",
            'value' => $value,
        ];

        return $this;
    }

    /*
     *  This method is used to select specific columns from the table
     *  Usage: $orm->select('id', 'name')->get();
     */
    public function select($columns)
    {
        // Comma separated list of columns
        $columns = implode(', ', func_get_args());

        $query = "SELECT $columns FROM $this->table";
        $values = [];

        if (!empty($this->whereConditions)) {
            $whereClause = implode(" AND ", array_column($this->whereConditions, 'condition'));
            $query .= " WHERE $whereClause";
            $values = array_column($this->whereConditions, 'value');
        }

        if (!empty($this->groupByColumn)) {
            $query .= " GROUP BY $this->groupByColumn";
        }

        if (!empty($this->orderByColumn)) {
            $query .= " ORDER BY $this->orderByColumn";
        }

        if (!empty($this->limitValue)) {
            $query .= " LIMIT $this->limitValue";
        }

        $statement = $this->connection->prepare($query);

        if ($values) {
            $statement->bind_param(str_repeat('s', count($values)), ...$values);
        }

        $statement->execute();

        // return as array of objects
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);

//         return as array of objects
         $results = [];
         $result = $statement->get_result();

         while ($row = $result->fetch_object()) {
             $results[] = $row;
         }
    }

    public function toDataTables($data) {

        // Remove the indexes from the data array
        $data = array_values($data);

        // Loop through the data array
        foreach ($data as $key => $value) {
            // Add the DT_RowId property to the data array
            $data[$key]['DT_RowId'] = $value['id'];
        }

        // Make this result set compatible with DataTables
        $result = [
            'draw' => 1,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ];

        return $result;


    }

    public function count()
    {
        $query = "SELECT COUNT(*) FROM $this->table";
        $values = [];

        if (!empty($this->whereConditions)) {
            $whereClause = implode(" AND ", $this->whereConditions);
            $query .= " WHERE $whereClause";
            $values = array_column($this->whereConditions, 'value');
        }

        if (!empty($this->groupByColumn)) {
            $query .= " GROUP BY $this->groupByColumn";
        }

        if (!empty($this->orderByColumn)) {
            $query .= " ORDER BY $this->orderByColumn";
        }

        if (!empty($this->limitValue)) {
            $query .= " LIMIT $this->limitValue";
        }

        $statement = $this->connection->prepare($query);
        $statement->execute($values);

        return $statement->get_result()->fetch_row()[0];
    }

    /*
     * This method is used to offset the results returned from the query
     * Usage: $orm->offset(10)->get();
     * This will return results starting from the 10th record
     */
    public function offset($offset)
    {
        $this->offsetValue = $offset;
        return $this;
    }


    /*
     * This method is used to dump and die the data
     * Usage: $orm->dd($data);
     * This will dump and die the data
     */
    public function dd($data)
    {
        // Add syntax highlighting to the var_dump
        $highlighted = $this->syntaxHighlighting($data);

        // Output the data with background color
        echo "<pre style='background-color: #11052C; color: #F43B86; padding: 20px; margin: 20px; border-radius: 5px;'>";
        echo $highlighted;
        echo "</pre>";

    }

    /*
     * Syntax highlighting for var_dump
     * Usage: $this->syntaxHighlighting($data);
     */
    private function syntaxHighlighting($data)
    {
        // Start the output buffer
        ob_start();

        // Output the data
        print_r($data);

        // Get the output buffer contents
        $contents = ob_get_contents();

        // Clean the output buffer
        ob_end_clean();

        // Replace double quotes with single quotes
        $contents = str_replace('"', "'", $contents);

        // Add syntax highlighting to the var_dump
        $contents = preg_replace('/(string\(|int\(|float\(|bool\(|array\(|object\(|NULL\(|\)\n)/', '<span style="color: blue">$1</span>', $contents);

        // Add syntax highlighting to the array keys
        $contents = preg_replace('/(\[)(.*?)(\] =>)/', '$1<span style="color: #FFE459">$2</span>$3', $contents);

        // Add syntax highlighting to the array values
        $contents = preg_replace('/(\=\>)(.*?)(\n)/', '$1<span style="color: #9575DE">$2</span>$3', $contents);

        return $contents;
    }
    /*
     * This method is used to limit the number of results returned from the query
     * Usage: $orm->limit(10)->get();
     * This will return only 10 results
     */
    public function limit($limit)
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function orderBy($column)
    {
        $this->orderByColumn = $column;
        return $this;
    }

    public function groupBy($column)
    {
        $this->groupByColumn = $column;
        return $this;
    }

    public function request()
    {
        return (object) $_REQUEST;
    }

    public function old($fieldName)
    {
        if (isset($_SESSION['oldData'][$fieldName])) {
            return htmlspecialchars($_SESSION['oldData'][$fieldName]);
        }

        return null;
    }



    public function toJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function validate(array $data, array $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            // Check for required field before proceeding with validation
            if (!isset($data[$field])) {
                $errors[$field][] = 'Field is required';
                continue;
            }

            $value = $data[$field];

            // Split the rule into individual validation rules
            $rulesArray = explode('|', $rule);

            foreach ($rulesArray as $rule) {
                // Split the rule into the rule name and parameters (if any)
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

                // Call the corresponding validation method based on the rule name
                $validationMethod = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $validationMethod)) {
                    $isValid = $this->$validationMethod($field, $value, $ruleParams);

                    if (!$isValid) {
                        // Validation failed, add an error message to the errors array
                        $errorMessage = $this->getErrorMessage($field, $ruleName, $ruleParams);
                        $errors[$field][$ruleName] = $errorMessage;

                        // Break out of the loop if the "required" rule fails
                        if ($ruleName === 'required') {
                            break;
                        }
                    }
                } else {
                    // Handle unsupported or unrecognized validation rules
                    $errors[$field][$ruleName] = 'Unsupported validation rule: ' . $ruleName;
                }
            }
        }

        // Return the validation result
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }

// Validation rules
    private function validateRequired($field, $value, $params)
    {
        return !empty($value);
    }

    private function validateMin($field, $value, $params)
    {
        return strlen($value) >= $params[0];
    }

    private function validateMax($field, $value, $params)
    {
        return strlen($value) <= $params[0];
    }

    private function validateEmail($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function validateNumeric($field, $value, $params)
    {
        return is_numeric($value);
    }

    private function validateAlpha($field, $value, $params)
    {
        return ctype_alpha($value);
    }

    private function validateAlphaNumeric($field, $value, $params)
    {
        return ctype_alnum($value);
    }

    private function validateInteger($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    private function validateBoolean($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function validateUrl($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    private function validateIp($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    private function validateMatch($field, $value, $params)
{
    $fieldName = $params[0];

    if (!isset($this->data[$fieldName])) {
        return false;
    }

    $fieldValue = $this->data[$fieldName];

    return $value === $fieldValue;
}


    private function validatePassword($field, $value, $params)
    {
        return password_verify($value, $params[0]);
    }

    private function validateStrongPassword($field, $value, $params)
    {
        $uppercase = preg_match('@[A-Z]@', $value);
        $lowercase = preg_match('@[a-z]@', $value);
        $number = preg_match('@[0-9]@', $value);

        return $uppercase && $lowercase && $number && strlen($value) >= 8;
    }

    private function validateUnique($field, $value, $params)
    {
        $query = "SELECT * FROM $this->table WHERE $field = ?";
        $statement = $this->connection->prepare($query);
        $statement->bind_param('s', $value);
        $statement->execute();
        $statement->store_result();
    
        $result = $statement->num_rows === 0;
    
        $statement->free_result();
        $statement->close();
    
        return $result;
    }    

    private function validateExists($field, $value, $params)
    {
        $query = "SELECT * FROM $this->table WHERE $field = ?";
        $statement = $this->connection->prepare($query);
        $statement->execute([$value]);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return !empty($result);
    }

    private function validateFile($field, $value, $params)
    {
        return isset($_FILES[$field]);
    }

    private function validateImage($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return false;
        }

        $file = $_FILES[$field];
        $fileType = $file['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        return in_array($fileType, $allowedTypes);
    }

    private function validateFilesize($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return false;
        }

        $file = $_FILES[$field];
        $fileSize = $file['size'];
        $maxSize = $params[0];

        return $fileSize <= $maxSize;
    }

    private function validateFiletype($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return false;
        }

        $file = $_FILES[$field];
        $fileName = $file['name'];
        $fileParts = explode('.', $fileName);
        $fileExtension = strtolower(end($fileParts));
        $allowedExtensions = $params;

        return in_array($fileExtension, $allowedExtensions);
    }

    /*
        * This method is used to get the error message for a validation rule
        * Usage: $this->getErrorMessage('email', 'required', []);
        * This will return "The email field is required"
    */
    private function getErrorMessage($field, $ruleName, $ruleParams)
    {
        $errorMessages = [
            'required' => 'The :field field is required',
            'email' => 'The :field field must be a valid email address',
            'min' => 'The :field field must be at least :param characters long',
            'max' => 'The :field field must not exceed :param characters',
            'numeric' => 'The :field field must be a number',
            'alpha' => 'The :field field must contain only letters',
            'alphaNumeric' => 'The :field field must contain only letters and numbers',
            'integer' => 'The :field field must be an integer',
            'boolean' => 'The :field field must be a boolean',
            'url' => 'The :field field must be a URL',
            'ip' => 'The :field field must be an IP address',
            'match' => 'The :field field must match the :param field',
            'password' => 'The :field field must match the password',
            'strongPassword' => 'The :field field must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number',
            'unique' => 'The :field field must be unique',
            'exists' => 'The :field field must exist in the database',
            'file' => 'The :field field must be a file',
            'image' => 'The :field field must be an image',
            'filesize' => 'The :field field must not exceed :param bytes',
            'filetype' => 'The :field field must be one of the following types: :param',
        ];

        // Check if the input rule name exists in the error messages array
        if (array_key_exists($ruleName, $errorMessages)) {
            $errorMessage = $errorMessages[$ruleName];

            // Replace the ":field" placeholder with the actual field name
            $errorMessage = str_replace(':field', $field, $errorMessage);

            // Replace the ":param" placeholder with the actual parameter value (if provided)
            if (!empty($ruleParams)) {
                $errorMessage = str_replace(':param', $ruleParams[0], $errorMessage);
            }

            return $errorMessage;
        } else {
            return 'Invalid rule name provided.';
        }
    }
}
