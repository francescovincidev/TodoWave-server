<?php

require_once __DIR__ . '/../validations/User_validation.php';

class User extends User_validation
{
  use Db;

  private $username;
  private $email;
  private $password;
  private $passwordRepeat;

  public function __construct($username, $email, $password, $passwordRepeat)
  {
    $this->username = $username;
    $this->email = $email;
    $this->password = $password;
    $this->passwordRepeat = $passwordRepeat;
  }

  // REGISTRAZIONE
  public function registerUser()
  {
    $db = $this->connect();

    // VALIDAZIONE DATI E MESSAGGI DI ERRORE
    $errors = $this->registerUser_validation($this->username, $this->email, $this->password, $this->passwordRepeat);

    if (!empty($errors)) {
      http_response_code(400); // Bad Request
      echo json_encode(['errors' => $errors]);
      exit;
    }

    // hashiamo la password
    $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

    //INSERIAMO IL NUOVO UTENTE NEL DB
    $stmt = $db->prepare("INSERT INTO users (email, password, username) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $this->email, $hashedPassword, $this->username);
    if ($stmt->execute()) {
      $user_id = mysqli_insert_id($db); // Ottieni l'ID generato
      http_response_code(201); // CREATO con successo
      echo json_encode(['message' => 'Registrazione avvenuta con successo', 'logged_id' => $user_id, 'username' => $this->username]);
    } else {
      http_response_code(500); // Errore del server
      echo json_encode(['errors' => 'Errore durante la registrazione']);
    }
    $stmt->close();
  }

  //LOGIN
  public function loginUser()
  {
    $db = $this->connect();

    // VALIDAZIONE DATI E MESSAGGI DI ERRORE
    $errors = $this->loginUser_validation($this->email, $this->password);


    if (!empty($errors)) {
      http_response_code(400); // Errore negli input
      echo json_encode(['errors' => $errors]);
      exit;
    }



    $stmt = $db->prepare("SELECT username, user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $this->email);
    if ($stmt->execute()) {
      $stmt->store_result();

      if ($stmt->num_rows === 1) {
        $stmt->bind_result($username, $user_id, $hashedPassword);
        $stmt->fetch();


        if (password_verify($this->password, $hashedPassword)) {

          http_response_code(201);
          echo json_encode(['message' => 'Login avvenuto con successo', 'logged_id' => $user_id, 'username' => $username]);
        } else {
          http_response_code(400); // Errore negli input
          $errors['inputs'][] = "Accesso non valido, email o password errati";

          echo json_encode(['errors' => $errors]);
        }
      } else {
        http_response_code(400); // Errore negli input
        $errors['inputs'][] = "Accesso non valido, email o password errati";

        echo json_encode(['errors' => $errors]);
      }
    } else {
      http_response_code(500); // Errore del server
      echo json_encode(['errors' => 'Errore durante il login']);
    }

    $stmt->close();
  }
}
