<?php
require_once __DIR__ . '/../validations/Tags_validation.php';

class Tags extends Tags_validation
{
    use Db;

    public function getTags($user_id)
    {
        $db = $this->connect();
        $tags = [];

        $stmt = $db->prepare("SELECT * FROM tags WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $tags[] = $row; // Aggiungi ogni riga al tuo array di risultati
            }
            // var_dump($tags);
            echo json_encode($tags);
            http_response_code(200);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => 'Errore durante la ricerca dei tag']);
        }
        $stmt->close();
    }

    public function createTag($user_id, $tag_name)
    {
        $errors = $this->validateTag($user_id, $tag_name); // Implementa la tua validazione personalizzata
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        // Verifica se l'utente ha già 10 tag
        $db = $this->connect();
        $countStmt = $db->prepare("SELECT COUNT(*) AS tag_count FROM tags WHERE user_id = ?");
        $countStmt->bind_param("i", $user_id);
        if ($countStmt->execute()) {
            $countStmt->bind_result($tag_count);
            $countStmt->fetch();
            if ($tag_count >= 10) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'Un utente può avere al massimo 10 tag.']);
                exit;
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'inserimento del tag"]);
            exit;
        }
        $countStmt->close();

        $stmt = $db->prepare("INSERT INTO tags (user_id, tag_name) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $tag_name);

        if ($stmt->execute()) {
            http_response_code(201); // CREATO con successo
            echo json_encode(['message' => 'Tag creato']);
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'inserimento del tag"]);
        }

        $stmt->close();
    }

    public function updateTag($tag_id, $user_id, $new_tag_name)
    {
        $errors = $this->validateTag($user_id, $new_tag_name); // Implementa la tua validazione personalizzata
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();

        $stmt = $db->prepare("UPDATE tags SET tag_name = ? WHERE tag_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_tag_name, $tag_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Tag aggiornato con successo']);
            } else {
                http_response_code(404); // Non trovato
                echo json_encode(['error' => 'Impossibile modificare il tag']);
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'aggiornamento del tag"]);
        }

        $stmt->close();
    }

    public function deleteTags($tag_ids, $user_id)
    {
        $db = $this->connect();

        // Inizia la transazione
        $db->begin_transaction();

        try {
            // Elimina i collegamenti nella tabella todo_tag per ogni ID di tag
            foreach ($tag_ids as $tag_id) {
                // Elimina i collegamenti nella tabella todo_tag
                $stmt = $db->prepare("DELETE FROM todo_tag WHERE tag_id = ?");
                $stmt->bind_param("i", $tag_id);

                if (!$stmt->execute()) {
                    throw new Exception("Errore nell'eliminazione dei collegamenti nella tabella todo_tag");
                }

                $stmt->close();
            }

            // Ora elimina i tag dalla tabella tags
            foreach ($tag_ids as $tag_id) {
                $stmt = $db->prepare("DELETE FROM tags WHERE tag_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $tag_id, $user_id);

                if (!$stmt->execute()) {
                    throw new Exception("Errore nell'eliminazione dei tag");
                }

                $stmt->close();
            }

            // Se tutte le eliminazioni hanno successo, effettua il commit della transazione
            $db->commit();

            http_response_code(200); // OK
            echo json_encode(['message' => 'Tutti i tag selezionati sono stati eliminati con successo']);
        } catch (Exception $e) {
            // Se si verifica un errore in una delle eliminazioni, effettua il rollback della transazione
            $db->rollback();

            http_response_code(500); // Errore del server
            echo json_encode(['error' => $e->getMessage()]);
        }

        // Chiudi la connessione
        $db->close();
    }
}
