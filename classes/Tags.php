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
                $tags[] = $row; // Aggiunge ogni riga all'array tags
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
        $errors = $this->validateTag($user_id, $tag_name);
        if (!empty($errors)) {
            http_response_code(400); // Bad Request
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $db = $this->connect();


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


    public function deleteTags($tag_ids, $user_id)
    {
        $db = $this->connect();

        // Inizia la transazione
        $db->begin_transaction();

        try {

            foreach ($tag_ids as $tag_id) {
                // Elimina i collegamenti nella tabella todo_tag
                $stmt = $db->prepare("DELETE FROM todo_tag WHERE tag_id = ?");
                $stmt->bind_param("i", $tag_id);

                $stmt->close();
            }

            // Ora elimina i tag dalla tabella tags
            foreach ($tag_ids as $tag_id) {
                $stmt = $db->prepare("DELETE FROM tags WHERE tag_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $tag_id, $user_id);

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
            echo json_encode(['error' => "Errore nell'eliminazione dei tag"]);
        }

        $db->close();
    }
}
