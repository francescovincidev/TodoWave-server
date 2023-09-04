<?php

require_once __DIR__ . '/../includes/Db.php';
class Todos_validation
{
    use Db;

    // VALIDAZIONE REGISTRAZIONE
    protected function create_update_Todo_validation($user_id, $title, $description, $deadline, $completed)
    {
        $errors = [];

        // VALIDAZIONE DATI E MESSAGGI DI ERRORE

        // INPUT
        if (empty($user_id)) {
            $errors['ids'][] = "Errore non sei loggato correttamente";
        }

        // TITOLO
        if (strlen($title) < 3) {
            $errors['titles'][] = "Aggiungi almeno 3 caratteri";
        }


        if (strlen($title) > 100) {
            $errors['titles'][] = "Il titolo non può essere più lungo di 100 caratteri";
        }

        //DESCRIZIONE
        if (strlen($description) > 1000) {
            $errors['descriptions'][] = "La descrizione non può essere più lunga di 1000 caratteri";
        }

        // DEADLINE
        if ($deadline !== null) {
            if (strtotime($deadline) === false || date('Y-m-d', strtotime($deadline)) !== $deadline) {

                $errors['deadlines'][] = "La data di scadenza non è nel formato corretto";
            }
        } else {
        }

        if ($completed != 1 && $completed != 0) {
            $errors["completeds"][] = "Il campo 'completed' deve essere un booleano valido.";
        }

        return $errors;
    }
}
