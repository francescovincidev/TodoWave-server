<?php

require_once __DIR__ . '/../setup.php';

if (strpos($_SERVER['REQUEST_URI'], '/get') !== false) {
    if ($method === 'GET' && isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        $newTodos = new Todos();
        $todos = $newTodos->getTodos($user_id);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => ' Metodo non consentito']);
    }
}


if (strpos($_SERVER['REQUEST_URI'], '/create') !== false) {
    if ($method === 'POST') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);

        $user_id = $inputData['user_id'];
        $title = $inputData['title'];
        $description = $inputData['description'];
        $deadline = $inputData['deadline'] ? $inputData['deadline'] : null;
        $completed = $inputData['completed'];

        $newTodos = new Todos();
        $newTodos->createTodo($user_id, $title, $description, $deadline, $completed);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}


if (strpos($_SERVER['REQUEST_URI'], '/update') !== false) {

    if ($method === 'PUT') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);
        $todo_id = $inputData['todo_id'];
        $completed = $inputData['completed'];
        $user_id = $inputData['user_id'];

        $newTodos = null;

        if ($todo_id !== null) {
            $newTodos = new Todos();
        } else {
            http_response_code(400); // Richiesta non valida
            echo json_encode(['error' => 'Errore nell\'aggiornamento']);
            exit;
        }


        if (strpos($_SERVER['REQUEST_URI'], '/update_completed') !== false) {
            $newTodos->updateTodoCompleted($todo_id, $completed, $user_id);
        } else {

            $title = $inputData['title'];
            $description = $inputData['description'];
            $deadline = $inputData['deadline'] ? $inputData['deadline'] : null;

            $newTodos->updateTodo($todo_id, $title, $description, $deadline, $completed, $user_id);
        }
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}


if (strpos($_SERVER['REQUEST_URI'], '/delete') !== false) {
    if ($method === 'DELETE') {
        $inputJSON = file_get_contents("php://input");
        $inputData = json_decode($inputJSON, true);
        $todo_id = $inputData['todo_id'];
        $user_id = $inputData['user_id'];


        if ($todo_id !== null) {
            $newTodos = new Todos();
            $newTodos->deleteTodo($todo_id, $user_id);
        } else {
            http_response_code(400); // Richiesta non valida
            echo json_encode(['error' => 'Errore nell\'eliminazione']);
        }
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}
