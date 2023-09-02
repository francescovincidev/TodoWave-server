<?php
require_once __DIR__ . '/../setup.php';

if (strpos($_SERVER['REQUEST_URI'], '/get') !== false) {
    if ($method === 'GET' && isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        $newTags = new Tags();
        $tags = $newTags->getTags($user_id);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => 'Metodo non consentito']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/create') !== false) {
    if ($method === 'POST') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);

        $user_id = $inputData['user_id'];
        $tag_name = $inputData['tag_name'];

        $newTags = new Tags();
        $newTags->createTag($user_id, $tag_name);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/update') !== false) {
    if ($method === 'PUT') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);
        $tag_id = $inputData['tag_id'];
        $user_id = $inputData['user_id'];
        $new_tag_name = $inputData['new_tag_name'];

        $newTags = new Tags();
        $newTags->updateTag($tag_id, $user_id, $new_tag_name);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/delete') !== false) {
    if ($method === 'DELETE') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);
        $tag_id = $inputData['tag_id'];
        $user_id = $inputData['user_id'];

        $newTags = new Tags();
        $newTags->deleteTag($tag_id, $user_id);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}
