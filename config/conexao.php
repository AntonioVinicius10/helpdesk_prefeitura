<?php
// config/conexao.php

$host = 'localhost';
$db   = 'helpdesk_prefeitura';
$user = 'root'; 
$pass = '';    
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna dados como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa Prepared Statements reais do MySQL
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}