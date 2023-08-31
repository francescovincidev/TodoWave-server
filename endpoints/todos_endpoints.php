<?php

require_once __DIR__ . '/../setup.php';

if (strpos($_SERVER['REQUEST_URI'], '/get') !== false) {

    if ($method === 'POST') {
        $user_id = $_POST['user_id'];

        $newTodos = new Todos();
        $newTodos->getTodos($user_id);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => 'Method not allowed']);
    }
}


if (strpos($_SERVER['REQUEST_URI'], '/create') !== false) {

    if ($method === 'POST') {

        $user_id = $_POST['user_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        //se arriva lo 0 passa null
        $deadline = $_POST['deadline'] ? $_POST['deadline'] : null;
        $completed = $_POST['completed'];



        $newTodos = new Todos();
        $newTodos->createTodo($user_id, $title, $description, $deadline, $completed);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => 'Method not allowed']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/update') !== false) {

    if ($method === 'POST') {
        $todo_id = $_POST['todo_id'];
        $completed = $_POST['completed'];
        $newTodos = new Todos();

        if (strpos($_SERVER['REQUEST_URI'], '/update_completed') !== false) {
            $newTodos->updateTodoCompleted($todo_id, $completed);
        } else {

            $todo_id = $_POST['todo_id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            //se arriva lo 0 passa null
            $deadline = $_POST['deadline'] ? $_POST['deadline'] : null;

            $newTodos->updateTodo($todo_id, $title, $description, $deadline, $completed);
        }
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => 'Method not allowed']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/delete') !== false) {

    if ($method === 'POST') {

        $todo_id = $_POST['todo_id'];

        $newTodos = new Todos();
        $newTodos->deleteTodo($todo_id);
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['errors' => 'Method not allowed']);
    }
}
