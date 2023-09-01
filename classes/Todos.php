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
            http_response_code(200);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => 'Errore durante la ricerca dei todo']);
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
            echo json_encode(['message' => 'Todo creato']);
        } else {
            http_response_code(500); // Errore del server

            // return false; // Ritorna false in caso di errore
            echo json_encode(['error' => "Errore nell'inserimento del todo"]);
        }

        $stmt->close();
    }

    public function updateTodo($todo_id, $title, $description, $deadline, $completed, $user_id)
    {
        $errors = $this->create_update_Todo_validation($todo_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        $stmt = $db->prepare("UPDATE todos SET title=?, description=?, deadline=?, completed=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("sssiii", $title, $description, $deadline, $completed, $todo_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Todo aggiornato con successo']);
            } else {
                http_response_code(404); // Non trovato
                echo json_encode(['error' => 'Impossibile modificare todo']);
                exit;
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'aggiornamento del todo"]);
        }


        $stmt->close();
    }

    public function updateTodoCompleted($todo_id, $completed, $user_id)
    {
        // $errors = $this->createTodo_validation($todo_id, $title, $description, $deadline, $completed);
        // if (!empty($errors)) {
        //     http_response_code(400); // Bad Request
        //     echo json_encode(['errors' => $errors]);
        //     exit;
        // }

        $db = $this->connect();

        $stmt = $db->prepare("UPDATE todos SET completed=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("iii", $completed, $todo_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Todo aggiornato con successo']);
            } else {
                http_response_code(404); // Non trovato
                echo json_encode(['error' => 'Impossibile modificare todo']);
                exit;
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'aggiornamento del todo"]);
        }


        $stmt->close();
    }

    public function deleteTodo($todo_id, $user_id)
    {
        $db = $this->connect();

        $stmt = $db->prepare("DELETE FROM todos WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("ii", $todo_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Todo eliminato con successo']);
            } else {
                http_response_code(404); // Non trovato
                echo json_encode(['error' => "Errore nell'eliminazione del Todo"]);
                exit;
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'eliminazione del Todo"]);
        }

        $stmt->close();
    }
}
