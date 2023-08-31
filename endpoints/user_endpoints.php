<?php

require_once __DIR__ . '/../setup.php';

if (strpos($_SERVER['REQUEST_URI'], '/login') !== false) {

    if ($method === 'POST') {

        $email = $_POST['email'];
        $password = $_POST['password'];

        $newUser = new User('', $email, $password, '');
        $user_id = $newUser->loginUser();
    } else {
        http_response_code(405); // Metodo non consentito
        $errors['methods'][] = 'Method not allowed';
        echo json_encode(['errors' => $errors]);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/register') !== false) {
    if ($method === 'POST') {

        $email = $_POST['email'];
        $password = $_POST['password'];
        $passwordRepeat = $_POST['passwordRepeat'];
        $username = $_POST['username'];


        $newUser = new User($username, $email, $password, $passwordRepeat);
        $newUser->registerUser();
    } else {
        http_response_code(405); // Metodo non consentito
        $errors['methods'][] = 'Method not allowed';
        echo json_encode(['errors' => $errors]);
    }
}
