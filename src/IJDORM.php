<?php 

namespace IamJohnDevORM;

class IJDORM {
    private $conn;

    public function __construct($host, $username, $password, $database) {
        $this->conn = new mysqli($host, $username, $password, $database);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function select($table, $fields = "*", $where = "") {
        $sql = "SELECT $fields FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error: " . $this->conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function insert($table, $data) {
        $keys = implode(",", array_keys($data));
        $values = "'" . implode("','", array_values($data)) . "'";

        $sql = "INSERT INTO $table ($keys) VALUES ($values)";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error: " . $this->conn->error);
        }

        return $this->conn->insert_id;
    }

    public function update($table, $data, $where = "") {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = '$value'";
        }

        $set = implode(",", $set);

        $sql = "UPDATE $table SET $set";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error: " . $this->conn->error);
        }

        return $this->conn->affected_rows;
    }

    public function delete($table, $where = "") {
        $sql = "DELETE FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error: " . $this->conn->error);
        }

        return $this->conn->affected_rows;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
