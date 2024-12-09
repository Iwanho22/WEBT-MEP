<?php
$conn = mysqli_connect("localhost", "root", "", "thecrux");
header('Content-Type: application/json');

const FB_BLOC = 'fb_bloc';
const V = 'v';
$selectedSkala;

if(!isset($_COOKIE['skala'])) {
    setcookie('skala', FB_BLOC);
    $selectedSkala = FB_BLOC;
} else {
    $selectedSkala = $_COOKIE['skala'];
}
define('SELECTED_SKALA',$selectedSkala);

const FB_TO_V = [
    "4"=> "V0",
    "5"=> "V1",
    "5+"=> "V2",
    "6a"=> "V3",
    "6a+"=> "V4",
    "6b"=> "V4",
    "6b+"=> "V5",
    "6c"=> "V5",
    "6c+"=> "V6",
    "7a"=> "V6",
    "7a+"=> "V7",
    "7b"=> "V8",
    "7b+"=> "V9",
    "7c"=> "V9",
    "7c+"=> "V10",
    "8a"=> "V11",
    "8a+"=> "V12",
    "8b"=> "V13",
    "8b+"=> "V14",
    "8c"=> "V15",
    "8c+"=> "V16",
    "9a"=> "V17"
];

if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


if (isset($_GET['method']) && $_GET['method'] == 'climb') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $climb = convertClimbToDBModel(file_get_contents("php://input"));
        
        $stmt = $conn->prepare("INSERT INTO climb (name, grade, climbed, route) values (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $climb->name, $climb->grade, $climb->climbed, $climb->route);
        
        $stmt->execute();
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
                convert_grade($row);
                $rows[] = $row;
            }

            echo(json_encode($rows));
        }
    } else if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("DELETE FROM climb WHERE id=?");
            $stmt->bind_param('i', $_GET['id']);
            $stmt->execute();
        }
    } else if($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        if (isset($_GET['id'])) {
            $climb = convertClimbToDBModel(file_get_contents("php://input"));
            $stmt = $conn->prepare("UPDATE climb SET name = ?, climbed = ?, grade = ? WHERE id = ?");
            $stmt->bind_param('sisi', $climb->name, $climb->climbed, $climb->grade, $climb->id);
            $stmt->execute();
        }
    }
}

$conn->close();


function convert_grade($climb) {
    if (SELECTED_SKALA === V) {
        $climb->grade = FB_TO_V[$climb->grade];
    }
}

function convertClimbToDBModel($json) {
    $climb = json_decode($json);
    $climb->climbed = $climb->climbed === true; // TODO fix this
    $climb->route = json_encode($climb->route);

    return $climb;
}
?>