<?php
trait Db
{
    protected function connect()
    {
        try {
            $dbhost = "localhost";
            $dbuser = "root";
            $dbpassword = "root";
            $dbname = "to-do_db";
            $db = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);
            return $db;
        } catch (Exception $e) {

            http_response_code(500);
            $errors['dbs'][] = $e->getMessage();
            echo json_encode(['errors' => $errors]);
            die();
        }
    }
}
