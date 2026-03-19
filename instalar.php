<?php
require 'config.php';

try {
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    echo "<h1>Banco de dados criado com sucesso!</h1><p>Você já pode acessar o sistema: <a href='index.php'>Acessar</a></p>";
} catch (PDOException $e) {
    echo "<h1>Erro ao criar banco de dados:</h1> <p>" . $e->getMessage() . "</p>";
}
