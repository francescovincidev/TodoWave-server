<?php

require_once __DIR__ . '/../includes/Db.php';

class User_validation
{
    use Db;


    // VALIDAZIONE REGISTRAZIONE
    protected function registerUser_validation($username, $email, $password, $passwordRepeat)
    {
        $db = $this->connect();
        $errors = [];

        // VALIDAZIONE DATI E MESSAGGI DI ERRORE

        // INPUT
        if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
            $errors['inputs'][] = "Tutti i campi sono obbligatori";
        }

        //USERNAME
        if (strlen($username) > 20) {
            $errors['usernames'][] = "L'username non puo essere piu lungo di 20 caratteri";
        }

        if (strlen($username) < 2) {
            $errors['usernames'][] = "L'username non puo essere un solo carattere";
        }

        // EMAIL
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['emails'][] = "L'indirizzo email non è valido";
        }

        if (strlen($email) > 255) {
            $errors['emails'][] = "L'indirizzo email è troppo lungo";
        }


        //verifichiamo che l'email non sia già usata
        $stmt = $db->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) { // Esecuzione riuscita

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $errors['emails'][] = "Email già registrata";
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => 'Errore durante la registrazione']);
        }
        $stmt->close();


        //PASSWORD
        if (strlen($password) < 8) {
            $errors['passwords'][] = "La password deve essere lunga almeno 8 caratteri";
        }

        if (strlen($password) > 255) {
            $errors['passwords'][] = "La password è troppo lunga";
        }

        if ($password !== $passwordRepeat) {
            $errors['passwordsRepeat'][] = "Le due password non corrispondono";
        }
        return $errors;
    }

    //VALIDAZIONE LOGIN
    protected function loginUser_validation($email, $password)
    {
        $errors = [];


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['emails'][] = "L'indirizzo email non è valido";
        }

        if (empty($email) || empty($password)) {
            $errors['inputs'][] = "Tutti i campi sono obbligatori";
        }

        return $errors;
    }
}
