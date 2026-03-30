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
    // Monta a query dinamicamente baseada no que o React enviar para alterar
    $campos = [];
    $valores = [':id' => $id];

    if (isset($dados['finalizado'])) {
        $campos[] = "finalizado = :finalizado";
        // Converte booleano do JS para o formato do Postgres (true/false string)
        $valores[':finalizado'] = $dados['finalizado'] ? 'true' : 'false'; 
    }
    if (isset($dados['observacoes'])) {
        $campos[] = "observacoes = :observacoes";
        $valores[':observacoes'] = $dados['observacoes'];
    }
    if (isset($dados['motivo'])) {
        $campos[] = "motivo = :motivo";
        $valores[':motivo'] = $dados['motivo'];
    }
    if (isset($dados['defeito'])) {
        $campos[] = "defeito = :defeito";
        $valores[':defeito'] = $dados['defeito'];
    }
    if (array_key_exists('numero_chamado', $dados)) {
        $campos[] = "numero_chamado = :numero_chamado";
        $valores[':numero_chamado'] = $dados['numero_chamado'];
    }

    if (empty($campos)) {
        echo json_encode(["success" => false, "message" => "Nenhum campo enviado para atualizar."]);
        exit;
    }

    $sql = "UPDATE triagens SET " . implode(", ", $campos) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(["success" => true, "message" => "Triagem atualizada com sucesso!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar: " . $e->getMessage()]);
}
?>