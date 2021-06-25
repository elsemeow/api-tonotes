<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");

require_once "../../config/Database.php";
require_once "../../models/Note.php";
require_once "../../models/HttpResponse.php";

$db = new Database();
$note = new Note($db);
$http = new HttpResponse();

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
  if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
    $http->notAuthorized("You must authenticate yourself before you can use my REST API services");
    exit();
  } else {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    $query = "SELECT * FROM users WHERE username = ?";
    $results = $db->fetchOne($query, $username);
    if ($results === 0 || $results['password'] !== $password) {
      $http->notAuthorized("You provided wrong credentials");
      exit();
    } else {
      $user_id = $results['id'];
    }
  }
}

// CHECK INCOMING GET REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($_GET['id']) && !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // ERROR ONLY INTEGER IS ALLOWED
    $http->badRequest("Only a valid integer is allowed to fetch a single note");
    die();
  }
  // FETCH ONE NOTE IF ID EXISTS OR ALL IF ID DOESN'T EXIST
  $resultsData = isset($_GET['id']) ? $note->fetchOneNote($_GET['id']) : $note->fetchAllNotes();
  $resultsInfo = $db->executeCall($username, 1000, 86400);

  if ($resultsData === 0) {
    $message = "No ";
    $message .= isset($_GET['id']) ? "note with the id " . $_GET['id'] . " " : "notes ";
    $message .= "was found";
    $http->notFound($message);
  } else if ($resultsInfo === -1) {
    $http->paymentRequired();
  } else {
    $http->OK($resultsInfo, $resultsData);
  }
} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $noteReceived = json_decode(file_get_contents("php://input"));
  $results = $note->insertNote($noteReceived, $user_id);
  $resultsInfo = $db->executeCall($username, 1000, 86400);
  if ($results === -1) {
    $http->badRequest("A valid JSON of title field is required");
  } else if ($resultsInfo === -1) {
    $http->paymentRequired();
  } else {
    $http->OK($resultsInfo, $results);
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $noteReceived = json_decode(file_get_contents("php://input"));
  if (!$noteReceived->id) {
    // POST ID NOT PROVIDED BAD REQUEST
    $http->badRequest("Please an id is required to make a PUT request");
    exit();
  }
  $query = "SELECT * FROM notes WHERE id = ?";
  $results = $db->fetchOne($query, $noteReceived->id);
  if ($results === 0) {
    // Post NOT Found
    $http->notFound("Note with the id $noteReceived->id was not found");
  } else if ($results['user_id'] !== $user_id) {
    $http->notAuthorized("You are not authorized to update this note");
  } else {
    // USER CAN UPDATE
    $parameters = [
      'id' => $noteReceived->id,
      'title' => isset($noteReceived->title) ? $noteReceived->title : $results['title'],
      'todos' => isset($noteReceived->todos) ? $noteReceived->todos : $results['todos'],
    ];

    $resultsData = $note->updateNote($parameters);
    $resultsInfo = $db->executeCall($username, 1000, 86400);

    if ($resultsInfo === -1) {
      $http->paymentRequired();
    } else {
      $http->OK($resultsInfo, $resultsData);
    }
  }
} else if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
  $idReceived = json_decode(file_get_contents("php://input"));
  if (!$idReceived->id) {
    $http->badRequest("No id was provided");
    exit();
  }
  $query = "SELECT * FROM notes WHERE id = ?";
  $results = $db->fetchOne($query, $idReceived->id);

  if ($results === 0) {
    // POST NOT FOUND
    $http->notFound("Note with the id $idReceived->id was not found");
    exit();
  }
  if ($results['user_id'] !== $user_id) {
    // NOT AUTHORIZED TO DELETE
    $http->notAuthorized("You are not authorized to delete this note");
  } else {
    // User CAN NOW DELETE NOTE
    $resultsData = $note->deleteNote($idReceived->id);
    $resultsInfo = $db->executeCall($username, 1000, 86400);

    if ($resultsInfo === -1) {
      $http->paymentRequired();
    } else {
      $http->OK($resultsInfo, $resultsData);
    }
  }
}
