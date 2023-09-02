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

    public function deleteTag($tag_id, $user_id)
    {
        $db = $this->connect();

        $stmt = $db->prepare("DELETE FROM tags WHERE tag_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $tag_id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Tag eliminato con successo']);
            } else {
                http_response_code(404); // Non trovato
                echo json_encode(['error' => 'Impossibile eliminare il tag']);
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'eliminazione del tag"]);
        }

        $stmt->close();
    }
}
