<?php
$conn = mysqli_connect("localhost", "root", "", "thecrux");

header('Content-Type: application/json');

if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['method']) && $_GET['method'] == 'climb') {
        $body = file_get_contents("php://input");
        $climb = json_decode($body);
        
        $stmt = $conn->prepare("INSERT INTO climb (name, grade, climbed, route) values (?, ?, ?, ?)");
        echo "asdf";
        $stmt->bind_param("ssss", $climb->name, $climb->grade, $climb->climbed, json_encode($climb->route));
        
        $stmt->execute();

        echo($climb->name);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['method']) && $_GET['method'] == 'climb') {
        if (isset($_GET['id'])) {
           
            $stmt = $conn->prepare("SELECT id, name, grade, climbed, route from climb where id=?");
            $stmt->bind_param('i', $_GET['id']);
            if($stmt->execute()) {
                $result = $stmt->get_result()->fetch_object();
                $result->route = json_decode($result->route);
                echo(json_encode($result));
            };
        } else {
            $result = $conn->query("SELECT id, name, grade, climbed from climb");
            $rows = [];
            while ($row = $result->fetch_object()) {
                $rows[] = $row;
            }

            echo(json_encode($rows));
        }   
    }
}

$conn->close();
?>