<?php
$conn = mysqli_connect("localhost", "root", "", "thecrux");

header('Content-Type: application/json');

if ($mysqli->connect_error) {
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
        $results;
        $result = $conn->query("SELECT id, name, grade, climbed from climb");
        $rows = [];
        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        echo(json_encode($rows));
    }
}

$conn->close();
?>