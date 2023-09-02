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

        return $errors;
    }

    // Aggiungi altre funzioni di validazione se necessario per le tue operazioni CRUD sui tag.
}
