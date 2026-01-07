<?php
class PDOWrapper {
    private $pdo;
    private $stmt;

    public function __construct($dsn, $user, $pass) {
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function prepare($query) {
        $this->stmt = $this->pdo->prepare($query);
        return $this;
    }

    public function bind_param($type, &...$params) {
        foreach ($params as $i => $val) {
            $this->stmt->bindValue($i+1, $val); // PDO bindValue
        }
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function get_result() {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
