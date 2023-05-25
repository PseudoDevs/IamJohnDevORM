<?php 

namespace IamJohnDevORM;

use mysqli;

class IJDORM
{
    protected $tableName;
    protected $dbConnection;
    protected $query;

    public function __construct($tableName, $dbConnection)
    {
        $this->tableName = $tableName;
        $this->dbConnection = $dbConnection;
    }

    public function customQuery($query)
    {
        // Execute a custom query
        $result = $this->dbConnection->query($query);
        $results = $result->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->tableName;
        $result = $this->dbConnection->query($query);
        $results = $result->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    public function getFirst()
    {
        $query = "SELECT * FROM " . $this->tableName;
        // Append the WHERE clause if it exists
        if (!empty($this->whereClause)) {
            $query .= $this->whereClause;
        }
        $query .= " LIMIT 1";
        $result = $this->dbConnection->query($query);
        $result = $result->fetch_assoc();

        return $result;
    }

    public function getLast()
    {
        $query = "SELECT * FROM " . $this->tableName . " ORDER BY id DESC LIMIT 1";
        $result = $this->dbConnection->query($query);
        $result = $result->fetch_assoc();

        return $result;
    }

    public function select($columns = '*')
    {
        $this->query = "SELECT " . $this->sanitizeColumnNames($columns) . " FROM " . $this->tableName;

        return $this;
    }

    public function where($conditions)
    {
        $where = $this->query .= " WHERE " . $this->sanitizeConditions($conditions);
        $this->whereClause = $where;

        return $this;
    }

    public function insert($data)
    {
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";

        $query = "INSERT INTO " . $this->tableName . " (" . $this->sanitizeColumnNames($columns) . ") VALUES (" . $values . ")";
        $result = $this->dbConnection->query($query);

        return $this->dbConnection->insert_id;
    }

    public function update($data)
    {
        $setClause = "";

        foreach ($data as $key => $value) {
            $setClause .= $this->sanitizeColumnName($key) . " = '" . $value . "', ";
        }

        $setClause = rtrim($setClause, ", ");

        $query = "UPDATE " . $this->tableName . " SET " . $setClause . $this->query;
        $result = $this->dbConnection->query($query);

        return $this->dbConnection->affected_rows;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->tableName . $this->query;
        $result = $this->dbConnection->query($query);

        return $this->dbConnection->affected_rows;
    }

    public function join($table, $condition, $type = 'INNER')
    {
        $this->query .= " $type JOIN " . $this->sanitizeTableName($table) . " ON " . $this->sanitizeConditions($condition);

        return $this;
    }

    private function sanitizeTableName($tableName)
    {
        $sanitizedTableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        return $sanitizedTableName;
    }

    private function sanitizeColumnNames($columns)
    {
        $sanitizedColumns = preg_replace('/[^a-zA-Z0-9_,\s*]/', '', $columns);
        return $sanitizedColumns;
    }

    private function sanitizeColumnName($column)
    {
        $sanitizedColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        return $sanitizedColumn;
    }

    private function sanitizeConditions($conditions)
    {
        $sanitizedConditions = preg_replace('/[^a-zA-Z0-9\'_\s*><=!=]/', '', $conditions);
        return $sanitizedConditions;
    }
}
