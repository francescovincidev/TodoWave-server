<?php

try {
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpassword = "root";
    $dbname = "to-do_db";
    $db = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);
} catch (Exception $e) {

    http_response_code(500);
    $errors['dbs'][] = $e->getMessage();
    echo json_encode(['errors' => $errors]);
    die();
}
//Droppiamo le tabelle se esistono
$sql_drop_todo_tag = "DROP TABLE IF EXISTS todo_tag";
$db->query($sql_drop_todo_tag);

$sql_drop_tags = "DROP TABLE IF EXISTS tags";
$db->query($sql_drop_tags);

$sql_drop_todos = "DROP TABLE IF EXISTS todos";
$db->query($sql_drop_todos);

$sql_drop_users = "DROP TABLE IF EXISTS users";
$db->query($sql_drop_users);


// Creazione tabella users
$sql_users = "CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

if ($db->query($sql_users) === TRUE) {
    echo "Tabella 'users' creata con successo<br>";
} else {
    echo "Errore nella creazione della tabella 'users': " . $db->error . "<br>";
}

// Creazione tabella todos
$sql_todos = "CREATE TABLE todos (
    todo_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(1000),
    deadline DATE,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($db->query($sql_todos) === TRUE) {
    echo "Tabella 'todos' creata con successo<br>";
} else {
    echo "Errore nella creazione della tabella 'todos': " . $db->error . "<br>";
}

// Creazione tabella tags
$sql_tags = "CREATE TABLE tags (
    tag_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    tag_name VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($db->query($sql_tags) === TRUE) {
    echo "Tabella 'tags' creata con successo<br>";
} else {
    echo "Errore nella creazione della tabella 'tags': " . $db->error . "<br>";
}


// Creazione tabella todo_tag
$sql_todo_tag = "CREATE TABLE todo_tag (
    todo_id INT UNSIGNED,
    tag_id INT UNSIGNED,
    PRIMARY KEY (todo_id, tag_id),
    FOREIGN KEY (todo_id) REFERENCES todos(todo_id),
    FOREIGN KEY (tag_id) REFERENCES tags(tag_id)
)";

if ($db->query($sql_todo_tag) === TRUE) {
    echo "Tabella 'todo_tag' creata con successo<br>";
} else {
    echo "Errore nella creazione della tabella 'todo_tag': " . $db->error . "<br>";
}

// Chiusura della connessione
$db->close();
