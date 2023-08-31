<?php

require_once __DIR__ . '/../validations/Todos_validation.php';

class Todos extends Todos_validation
{

    use Db;

    public function getTodos($user_id)
    {
        $db = $this->connect();
        $todos = [];

        $stmt = $db->prepare("SELECT * FROM todos WHERE user_id = ?");

        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $todos[] = $row; // Aggiungi ogni riga al tuo array di risultati
            }
            echo json_encode($todos);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['errors' => 'Errore durante la registrazione']);
        }
        $stmt->close();
    }

    public function createTodo($user_id, $title, $description, $deadline, $completed)
    {
        $errors = $this->create_update_Todo_validation($user_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        $stmt = $db->prepare("INSERT INTO todos (user_id, title, description, deadline, completed) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $title, $description, $deadline, $completed);

        if ($stmt->execute()) {

            http_response_code(201); // CREATO con successo
            echo json_encode(['message' => 'Utente registrato con successo', 'logged_id' => $user_id]);
        } else {
            http_response_code(500); // Errore del server

            // return false; // Ritorna false in caso di errore
            echo json_encode(['Errors' => "Errore nell'inserimenti del todo"]);
        }

        $stmt->close();
    }

    public function updateTodo($todo_id, $title, $description, $deadline, $completed)
    {
        $errors = $this->create_update_Todo_validation($todo_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        $stmt = $db->prepare("UPDATE todos SET title=?, description=?, deadline=?, completed=? WHERE todo_id=?");
        $stmt->bind_param("sssii", $title, $description, $deadline, $completed, $todo_id);

        if ($stmt->execute()) {
            http_response_code(200); // OK
            echo json_encode(['message' => 'Todo aggiornato con successo']);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['errors' => "Errore nell'aggiornamento del todo"]);
        }

        $stmt->close();
    }

    public function updateTodoCompleted($todo_id, $completed)
    {
        // $errors = $this->createTodo_validation($todo_id, $title, $description, $deadline, $completed);
        // if (!empty($errors)) {
        //     http_response_code(400); // Bad Request
        //     echo json_encode(['errors' => $errors]);
        //     exit;
        // }

        $db = $this->connect();

        $stmt = $db->prepare("UPDATE todos SET completed=? WHERE todo_id=?");
        $stmt->bind_param("ii", $completed, $todo_id);

        if ($stmt->execute()) {
            http_response_code(200); // OK
            echo json_encode(['message' => 'Todo aggiornato con successo']);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['errors' => "Errore nell'aggiornamento del todo"]);
        }

        $stmt->close();
    }

    public function deleteTodo($todo_id)
    {
        $db = $this->connect();

        $stmt = $db->prepare("DELETE FROM todos WHERE todo_id=?");
        $stmt->bind_param("i", $todo_id);

        if ($stmt->execute()) {
            http_response_code(200); // OK
            echo json_encode(['message' => 'Todo eliminato con successo']);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['errors' => "Errore durante l'eliminazione del todo"]);
        }

        $stmt->close();
    }
}
