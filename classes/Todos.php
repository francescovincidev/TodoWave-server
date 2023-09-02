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

    public function updateTodo($todo_id, $title, $description, $deadline, $completed, $tags_add, $tags_remove, $user_id)
    {
        $errors = $this->create_update_Todo_validation($todo_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        // Connessione al database
        $db = $this->connect();

        // Inizializza le istruzioni preparate all'inizio
        $checkStmt = null;
        $tagAddStmt = null;
        $tagRemoveStmt = null;
        $currentStmt = null;
        $stmt = null;

        // Verifica se è possibile avviare una transazione
        if ($db->begin_transaction()) {
            $errorOccurred = false;
            $tagModified = false;
            // $message = '';

            // Itera sui tag da aggiungere
            foreach ($tags_add as $tag_id) {
                // Controlla se il tag esiste già per il todo specifico
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM todo_tag WHERE todo_id = ? AND tag_id = ?");
                $checkStmt->bind_param("ii", $todo_id, $tag_id);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                // Se il collegamento non esiste, esegui l'inserimento
                $tagAddStmt = $db->prepare("INSERT INTO todo_tag (todo_id, tag_id) VALUES (?, ?)");
                if ($count == 0) {
                    $tagAddStmt->bind_param("ii", $todo_id, $tag_id);
                    if ($tagAddStmt->execute()) {
                        if ($tagAddStmt->affected_rows > 0) {
                            $tagModified = true; // Imposta a true solo se viene aggiunto almeno un nuovo tag
                        } else {
                            $errorOccurred = true;
                        }
                    } else {
                        $errorOccurred = true;
                    }
                }
            }

            // Itera sui tag da rimuovere
            foreach ($tags_remove as $tag_id) {
                // Rimuovi il collegamento tra il todo e il tag
                $tagRemoveStmt = $db->prepare("DELETE FROM todo_tag WHERE todo_id = ? AND tag_id = ?");
                $tagRemoveStmt->bind_param("ii", $todo_id, $tag_id);
                if (!$tagRemoveStmt->execute()) {
                    $errorOccurred = true;
                } else {
                    // Verifica se almeno una riga è stata colpita dalla rimozione
                    if ($tagRemoveStmt->affected_rows > 0) {
                        $tagModified = true; // Se viene rimosso almeno un tag, i tag sono stati modificati
                    }
                }
            }
            // var_dump('TAGS: ' . $tagModified);

            // if (!$errorOccurred && $tagModified) {
            //     $message = 'Todo modificato con successo';
            // }

            // Ottieni i dati correnti del todo
            $currentStmt = $db->prepare("SELECT title, description, deadline, completed FROM todos WHERE todo_id=? AND user_id=?");
            $currentStmt->bind_param("ii", $todo_id, $user_id);
            if (!$currentStmt->execute()) {
                $errorOccurred = true;
            }
            $currentStmt->bind_result($currentTitle, $currentDescription, $currentDeadline, $currentCompleted);
            $currentStmt->fetch();
            $currentStmt->close();

            // Verifica se i dati sono stati effettivamente modificati
            if (!$errorOccurred && $title === $currentTitle && $description === $currentDescription && $deadline === $currentDeadline && $completed === $currentCompleted) {
                // Se non ci sono modifiche, conferma la transazione
                $db->commit();
                http_response_code(200); // OK
                echo json_encode(['message' => !$errorOccurred && $tagModified ? 'Todo modificato con successo' : 'Nessuna modifica effettuata']);
            } else {
                // Esegui l'aggiornamento solo se ci sono modifiche effettive
                $stmt = $db->prepare("UPDATE todos SET title=?, description=?, deadline=?, completed=? WHERE todo_id=? AND user_id=?");
                $stmt->bind_param("sssiii", $title, $description, $deadline, $completed, $todo_id, $user_id);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $db->commit();
                        http_response_code(200); // OK
                        echo json_encode(['message' => 'Todo modificato con successo']);
                    } else {
                        // Se non ci sono righe colpite dall'aggiornamento, annulla la transazione
                        $db->rollback();
                        http_response_code(404); // Non trovato
                        echo json_encode(['error' => 'Impossibile modificare il todo']);
                    }
                } else {
                    // In caso di errore nell'esecuzione dell'aggiornamento, annulla la transazione
                    $db->rollback();
                    http_response_code(500); // Errore del server
                    echo json_encode(['error' => 'Errore durante l\'aggiornamento del todo']);
                }
            }

            // Chiudi tutte le istruzioni preparate alla fine
            if ($tagAddStmt !== null) {
                $tagAddStmt->close();
            }
            if ($tagRemoveStmt !== null) {
                $tagRemoveStmt->close();
            }
            if ($stmt !== null) {
                $stmt->close();
            }
        } else {
            // Se non è possibile avviare la transazione, restituisci un errore
            http_response_code(500); // Errore del server

        }
    }
    public function updateTodoCompleted($todo_id, $completed, $user_id)
    {

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
