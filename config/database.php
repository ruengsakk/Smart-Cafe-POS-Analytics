<?php
class Database {
    private $host = "localhost";
    private $database_name = "coffeeshop_analytics";
    private $username = "root";
    private $password = "";
    private $connection;

    public function getConnection() {
        $this->connection = null;

        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Don't echo directly, let the caller handle the error
            error_log("Database connection error: " . $exception->getMessage());
            return null;
        }

        return $this->connection;
    }
}
?>