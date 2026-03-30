<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = $dados['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID da triagem não fornecido."]);
    exit;
}

try {
    $sql = "DELETE FROM triagens WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    echo json_encode(["success" => true, "message" => "Triagem excluída com sucesso!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao excluir: " . $e->getMessage()]);
}
?>