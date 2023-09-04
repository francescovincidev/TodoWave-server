<?php

require_once __DIR__ . '/../setup.php';



if (strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
    if ($method === 'POST') {
        // Prendiamo e convertiamo i json
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);


        $email = $inputData['email'];
        $password = $inputData['password'];

        $newUser = new User('', $email, $password, '');
        $user_id = $newUser->loginUser();
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/register') !== false) {
    if ($method === 'POST') {
        // Prendiamo e convertiamo i json
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);


        $email = $inputData['email'];
        $password = $inputData['password'];
        $passwordRepeat = $inputData['passwordRepeat'];
        $username = $inputData['username'];

        $newUser = new User($username, $email, $password, $passwordRepeat);
        $newUser->registerUser();
    } else {
        http_response_code(405); // Metodo non consentito
        echo json_encode(['error' => 'Metodo non consentito']);
    }
}
