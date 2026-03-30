<?php
// Liberando o CORS para o React conseguir conversar com o PHP
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// Puxa as configurações do banco que fizemos no arquivo conexao.php
require 'conexao.php'; 

// Lê o pacote (JSON) que o React enviou
$dados = json_decode(file_get_contents("php://input"));

// Verifica se enviaram email e senha mesmo
if (!isset($dados->email) || !isset($dados->senha)) {
    echo json_encode(["success" => false, "message" => "Dados incompletos"]);
    exit;
}

$email = trim($dados->email);
$senha = $dados->senha;

try {
    // Busca o usuário no banco de dados
    // OBS: Usamos :email e :senha para segurança (evitar SQL Injection)
    $query = "SELECT id, nome, role FROM usuarios WHERE email = :email AND senha = :senha";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->execute();

    // Tenta pegar a linha resultante
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Achou o usuário! Devolve para o React com sucesso
        echo json_encode(["success" => true, "user" => $usuario]);
    } else {
        // Não achou, ou a senha tá errada
        echo json_encode(["success" => false, "message" => "Credenciais inválidas."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados."]);
}
?>