<?php

namespace IamJohnDevORM;

use Exception;
use mysqli;

class IJDORM
{
    private $conn;

    public function __construct($host, $username, $password, $database)
    {
        $this->conn = new mysqli($host, $username, $password, $database);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function select($table, $fields = "*", $where = "")
    {
        $fields = $this->sanitizeFields($fields);
        $where = $this->sanitizeWhere($where);

        $stmt = $this->conn->prepare("SELECT $fields FROM $table WHERE $where");
        if ($stmt === false) {
            throw new Exception("Error preparing SQL statement: " . $this->conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error executing SQL query: " . $this->conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $this->sanitizeData($row);
        }

        return $rows;
    }

    public function insert($table, $data)
    {
        $data = $this->sanitizeData($data);

        $keys = implode(",", array_keys($data));
        $values = implode(",", array_fill(0, count($data), "?"));

        $sql = "INSERT INTO $table ($keys) VALUES ($values)";
        $params = array_values($data);

        return $this->executePreparedStatement($sql, $params);
    }

    public function update($table, $data, $where = "", $params = [])
    {
        $data = $this->sanitizeData($data);
        $where = $this->sanitizeWhere($where);

        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "UPDATE $table SET " . implode(",", $set);
        if ($where) {
            $sql .= " WHERE $where";
        }

        return $this->executePreparedStatement($sql, $params);
    }

    public function delete($table, $where = "", $params = [])
    {
        $where = $this->sanitizeWhere($where);

        $sql = "DELETE FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }

        return $this->executePreparedStatement($sql, $params);
    }

    public function join($tables, $fields = "*", $join_conditions = [], $where = "")
    {
        $fields = $this->sanitizeFields($fields);
        $where = $this->sanitizeWhere($where);

        $table_names = implode(",", $tables);

        $join = "";
        foreach ($join_conditions as $join_condition) {
            $join .= " JOIN {$join_condition['table']} ON {$join_condition['on']}";
        }

        $stmt = $this->conn->prepare("SELECT $fields FROM $table_names $join WHERE $where");
        if ($stmt === false) {
            throw new Exception("Error preparing SQL statement: " . $this->conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error executing SQL query: " . $this->conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $this->sanitizeData($row);
        }

        return $rows;
    }

    private function executePreparedStatement($sql, $params)
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $this->conn->error);
        }

        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $affected_rows;
    }

    private function sanitizeFields($fields)
    {
        $fields = preg_replace("/[^a-zA-Z0-9_,*]/", "", $fields);
        return $fields;
    }

    private function sanitizeWhere($where)
    {
        if (!$where) {
            return "";
        }
        $where = preg_replace("/[^a-zA-Z0-9_=\s]/", "", $where);
        return $where;
    }

    private function sanitizeData($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->conn->real_escape_string($value);
        }

        return $data;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
