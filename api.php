<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json; charset=UTF-8');

class sqlWorker
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "123";
    private $basename = "phonedict";
    private $tablename = "phonedict_table";
    public $conn;

    function __construct()
    {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->basename);
    }

    function __destruct()
    {
        mysqli_close($this->conn);
    }

    function getAll()
    {
        $res = mysqli_query($this->conn, "SELECT * FROM " . $this->tablename);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($out, $row);
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    function insertInBase($name, $phone, $place)
    {
        mysqli_query($this->conn, "INSERT INTO " . $this->tablename . "(name, phone, place) VALUES('" . $name . "','" . $phone . "','" . $place . "')");
        return $this->conn->insert_id;;
    }

    function removeFromBase($id)
    {
        mysqli_query($this->conn, "DELETE FROM " . $this->tablename . " WHERE id=" . $id);
        return 1;
    }

    function changeInBase($id, $name, $phone, $place)
    {
        mysqli_query($this->conn, "UPDATE " . $this->tablename . " SET name='" . $name . "',phone='" . $phone . "',place='" . $place . "' WHERE id=" . $id);
        return "UPDATE " . $this->tablename . " SET name='" . $name . "',phone='" . $phone . "',place='" . $place . "' WHERE id=" . $id;
    }

    function makeDefaultData()
    {
        mysqli_query($this->conn, 'DROP TABLE IF EXISTS ' . $this->tablename);
        if (!mysqli_query($this->conn, "DESCRIBE " . $this->tablename)) {
            $createTable = "CREATE TABLE IF NOT EXISTS " . $this->tablename . "(
            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            place VARCHAR(60) NOT NULL
            )";
            mysqli_query($this->conn, $createTable);
            mysqli_query($this->conn, "INSERT INTO " . $this->tablename . "(name, phone, place) VALUES('Иванов Иван Иванович','+79999999','Банк'),('Петров Пётр Петрович','+79999999','Банк'),('Сидоров Сидор Сидорович','+79999999','Дом')");
        }
    }
}

class Api
{
    private $worker;
    private $method;
    function __construct()
    {
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->worker = new sqlWorker();
        // $this->worker->makeDefaultData();
    }
    function run()
    {
        switch ($this->method) {
            case 'GET':
                echo $this->worker->getAll();
                break;
            case 'POST':
                $_POST = json_decode(file_get_contents('php://input'), true);
                echo $this->worker->insertInbase($_POST["name"], $_POST["phone"], $_POST["place"]);
                break;
            case 'PATCH':
                $_POST = json_decode(file_get_contents('php://input'), true);
                echo $this->worker->changeInBase($_POST['id'], $_POST['name'], $_POST['phone'], $_POST['place']);
                break;
            case 'DELETE':
                $_POST = json_decode(file_get_contents('php://input'), true);
                echo $this->worker->removeFromBase($_POST['id']);
                break;
        }
    }
}

$api = new Api();
$api->run();
