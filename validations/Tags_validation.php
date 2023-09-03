<?php require_once __DIR__ . '/../includes/Db.php';

class Tags_validation
{
    use Db;

    // VALIDAZIONE CREAZIONE/AGGIORNAMENTO TAG
    protected function validateTag($user_id, $tag_name)
    {
        $errors = [];

        // VALIDAZIONE DATI E MESSAGGI DI ERRORE

        // USER_ID
        if (empty($user_id)) {
            $errors['user_ids'][] = "Errore, l'ID dell'utente non è valido";
        }

        // TAG_NAME
        if (empty($tag_name)) {
            $errors['tag_names'][] = "Il nome del tag non può essere vuoto";
        } elseif (strlen($tag_name) > 20) {
            $errors['tag_names'][] = "Il nome del tag non può essere più lungo di 20 caratteri";
        }

        // Verifica se l'utente ha già 10 tag
        $db = $this->connect();
        $countStmt = $db->prepare("SELECT COUNT(*) AS tag_count FROM tags WHERE user_id = ?");
        $countStmt->bind_param("i", $user_id);
        if ($countStmt->execute()) {
            $countStmt->bind_result($tag_count);
            $countStmt->fetch();
            if ($tag_count >= 10) {
                http_response_code(400); // Bad Request
                // echo json_encode(['error' => 'Un utente può avere al massimo 10 tag.']);
                $errors['tag_names'][] = "Un utente può avere al massimo 10 tag";
            }
        } else {
            http_response_code(500); // Errore del server
            echo json_encode(['error' => "Errore nell'inserimento del tag"]);
            exit;
        }
        $countStmt->close();

        return $errors;
    }
}
