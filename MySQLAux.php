<?php

class MySQLAux {

    private $server;
    private $db;
    private $usr;
    private $pass;

    public $conn;

    public function __construct($server, $db, $usr, $pass) {
        $this->server = $server;
        $this->db = $db;
        $this->usr = $usr;
        $this->pass = $pass;

        $connectionString = "mysql:host={$this->server};dbname={$this->db};charset=utf8mb4";

        try {
            $this->conn = new PDO($connectionString, $this->usr, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function obtenTodasLasFilas($tabla) {
        $query = "SELECT * FROM $tabla";
        $comando = $this->conn->prepare($query);
        $comando->execute();

        $res = [];
        while ($fila = $comando->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $fila;
        }

        return $res;
    }

    // 🔥 CORREGIDO
    public function insertarPan($nombre, $precio, $descripcion) {
        $query = "INSERT INTO panes (nombre, precio, descripcion) VALUES (?, ?, ?)";
        $comando = $this->conn->prepare($query);
        return $comando->execute([$nombre, $precio, $descripcion]);
    }

}