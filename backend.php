<?php
$conn = mysqli_connect("localhost", "root", "", "thecrux");

header('Content-Type: application/json');

const FB_BLOC = 'fb_bloc';
const V = 'v';
const ROUTE_ROWS = 6;
const ROUTE_COLS = 4;

if(!isset($_COOKIE['scale'])) {
    setcookie('scale', FB_BLOC);
    $_COOKIE['scale'] = FB_BLOC;
} 

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
    return_error(500, "Error while connection to Database");
}

if (isset($_GET['method']) && $_GET['method'] == 'climb') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $climb = decode_json(file_get_contents("php://input"));
        validate_climb($climb);

        $climb = convert_climb_to_dbmodel($climb);

        $stmt = $conn->prepare("INSERT INTO climb (name, grade, climbed, route) values (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $climb->name, $climb->grade, $climb->climbed, $climb->route);
        
        if (!$stmt->execute()) {
            return_error(500, "Error while saving climb.");
        }
        echo(json_encode(get_all_routes($conn)));
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT id, name, grade, climbed, route from climb where id=?");
            $stmt->bind_param('i', $_GET['id']);
            
            $error = $stmt->execute();
            $result = $stmt->get_result()->fetch_object();
            if($error && $result != null) {
                $result->route = json_decode($result->route);
                echo(json_encode($result));
            } else {
                return_error(500, "Error while loading climb with id=" . $_GET['id']);
            }
        } else {
            echo(json_encode(get_all_routes($conn)));
        }
    } else if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("DELETE FROM climb WHERE id=?");
            $stmt->bind_param('i', $_GET['id']);
            if(!$stmt->execute()) {
                return_error(500, "Error while deleting climg with id=" . $id);
            }
        }
    } else if($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        if (isset($_GET['id'])) {
            $climb = decode_json(file_get_contents("php://input"));
            validate_climb($climb);
            $climb = convert_climb_to_dbmodel($climb);
            $stmt = $conn->prepare("UPDATE climb SET name = ?, climbed = ?, grade = ? WHERE id = ?");
            $stmt->bind_param('sisi', $climb->name, $climb->climbed, $climb->grade, $climb->id);
            if(!$stmt->execute()) {
                return_error(500, "Error while updating climg with id=" . $id);
            }
        }
    } else {
        return_error(405, "Method not allowed");
    }
} else {
    return_error(404, "Not found");
}

$conn->close();


function convert_grade($climb) {
    if ($_COOKIE['scale'] === V) {
        $climb->grade = FB_TO_V[$climb->grade];
    }
}

function convert_climb_to_dbmodel($climb) {
    $climb->climbed = $climb->climbed === true;
    $climb->route = json_encode($climb->route);

    return $climb;
}

function validate_climb($climb) {
    if (is_string($climb->name) && (strlen($climb->name) < 5 || strlen($climb->name) > 40)) {
        return_error(400, "Invalid name, name must be at least 5 and at max 40 characters.");
    } else if (is_string($climb->grade) && !array_key_exists($climb->grade, FB_TO_V)) {
        return_error(400, "Invalid route grade");
    }

    $route_valid = false;
    if (is_array($climb->route) && count($climb->route) == ROUTE_ROWS) {
        foreach ($climb->route as $row) {
            $route_valid = is_array($row) 
                && count($row) === count(array_filter($row, 'is_bool'))
                && count($row) == ROUTE_COLS;
        }
        unset($row);
    }

    if (!$route_valid){
         return_error(400, "Invalid route");
    }
}

function return_error($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => ['reponseCode' => $code, 'message'=> $message]]);
    die();
}

function decode_json($json) {
    $decoded = json_decode($json);
    if ($decoded == null) {
        return_error(400, "Invalid Json.");
    }

    return $decoded;
}

function get_all_routes($conn) {
    $result = $conn->query("SELECT id, name, grade, climbed from climb");
    if (is_bool($result) && !$result) {
        return_error(500, "Error while loading climbs.");
    } else {
        $rows = [];
        while ($row = $result->fetch_object()) {
            convert_grade($row);
            $rows[] = $row;
        }

        return $rows;
    }
}
?>