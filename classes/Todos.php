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

                // Prendiamo l'id per prendere i tags associti
                $todo_id = $row['todo_id'];
                $tags = $this->getTagsForTodo($db, $todo_id);

                // Aggiungiamo tags ai todos
                $row['tags'] = $tags;
                $todos[] = $row;
            }
            echo json_encode($todos);
            http_response_code(200);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => 'Errore durante la ricerca dei todo']);
        }
        $stmt->close();
    }

    // Funzione per ottenere i tag associati a un todo specifico
    private function getTagsForTodo($db, $todo_id)
    {
        $tags = [];

        $tagStmt = $db->prepare("SELECT tags.tag_id, tags.tag_name FROM tags INNER JOIN todo_tag ON tags.tag_id = todo_tag.tag_id WHERE todo_tag.todo_id = ?");
        $tagStmt->bind_param("i", $todo_id);

        if ($tagStmt->execute()) {
            $tagResult = $tagStmt->get_result();

            while ($tagRow = $tagResult->fetch_assoc()) {
                $tags[] = $tagRow;
            }
        }

        $tagStmt->close();

        return $tags;
    }


    private function upTags($db, $todo_id, $tags_add, $tags_remove, &$tagModified, &$errorOccurred)
    {
        $tagAddStmt = $db->prepare("INSERT INTO todo_tag (todo_id, tag_id) VALUES (?, ?)");
        $tagRemoveStmt = $db->prepare("DELETE FROM todo_tag WHERE todo_id = ? AND tag_id = ?");
        $count = 0;
        foreach ($tags_add as $tag_id) {
            // Controlla se il tag esiste già per il todo specifico
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM todo_tag WHERE todo_id = ? AND tag_id = ?");
            $checkStmt->bind_param("ii", $todo_id, $tag_id);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            // Se il collegamento non esiste, esegue l'inserimento
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

        foreach ($tags_remove as $tag_id) {
            // Rimuove il collegamento tra il todo e il tag
            $tagRemoveStmt->bind_param("ii", $todo_id, $tag_id);
            if (!$tagRemoveStmt->execute()) {
                $errorOccurred = true;
            } else {
                // Verifica se almeno un tag è stato rimosso
                if ($tagRemoveStmt->affected_rows > 0) {
                    $tagModified = true; // Se viene rimosso almeno un tag, i tag sono stati modificati
                }
            }
        }

        $tagAddStmt->close();
        $tagRemoveStmt->close();
    }


    public function createTodo($user_id, $title, $description, $deadline, $completed, $tags_add, $tags_remove)
    {
        $errors = $this->create_update_Todo_validation($user_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        // Verifica se è possibile avviare una transazione
        if ($db->begin_transaction()) {
            $tagModified = false;
            $errorOccurred = false;

            $stmt = $db->prepare("INSERT INTO todos (user_id, title, description, deadline, completed) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $user_id, $title, $description, $deadline, $completed);

            if ($stmt->execute()) {
                $todo_id = $stmt->insert_id;
                $this->upTags($db, $todo_id, $tags_add, $tags_remove, $tagModified, $errorOccurred);
                $db->commit();
                http_response_code(201); // CREATO con successo
                echo json_encode(['message' => 'Todo creato con successo']);
            } else {
                // In caso di errore nell'inserimento, annulla la transazione
                $db->rollback();
                http_response_code(500); // Errore del server
                echo json_encode(['error' => "Errore nell'inserimento del todo"]);
            }


            $stmt->close();
        } else {

            http_response_code(500); // Errore del server
        }
    }

    public function updateTodo($todo_id, $title, $description, $deadline, $completed, $tags_add, $tags_remove, $user_id)
    {
        $errors = $this->create_update_Todo_validation($todo_id, $title, $description, $deadline, $completed);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }


        $db = $this->connect();

        // Verifica se è possibile avviare una transazione
        if ($db->begin_transaction()) {
            $tagModified = false;
            $errorOccurred = false;

            // Chiamata a upTags per gestire l'aggiunta/rimozione dei tag
            $this->upTags($db, $todo_id, $tags_add, $tags_remove, $tagModified, $errorOccurred);


            // Ottieni i dati correnti del todo
            $currentStmt = $db->prepare("SELECT title, description, deadline, completed FROM todos WHERE todo_id=? AND user_id=?");
            $currentStmt->bind_param("ii", $todo_id, $user_id);
            if (!$currentStmt->execute()) {
                $errorOccurred = true;
            }
            $currentStmt->bind_result($currentTitle, $currentDescription, $currentDeadline, $currentCompleted);
            $currentStmt->fetch();
            $currentStmt->close();


            $stmt = null;
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
            if ($stmt !== null) {
                $stmt->close();
            }
        } else {

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


        $db->begin_transaction();

        // Prima elimina le righe correlate dalla tabella "todo_tag"
        $tagDeleteStmt = $db->prepare("DELETE FROM todo_tag WHERE todo_id = ?");
        $tagDeleteStmt->bind_param("i", $todo_id);
        $tagDeleteStmt->execute();
        $tagDeleteStmt->close();

        // Poi elimina il todo dalla tabella "todos"
        $stmt = $db->prepare("DELETE FROM todos WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("ii", $todo_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Conferma la transazione se tutto è andato bene
                $db->commit();
                http_response_code(200); // OK
                echo json_encode(['message' => 'Todo eliminato con successo']);
            } else {
                // Annulla la transazione se non è stato eliminato alcun todo
                $db->rollback();
                http_response_code(404); // Non trovato
                echo json_encode(['error' => "Errore nell'eliminazione del Todo"]);
            }
        } else {

            $db->rollback();
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'eliminazione del Todo"]);
        }

        // Chiudi le istruzioni preparate
        $stmt->close();
    }
}
