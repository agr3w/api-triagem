<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

require 'conexao.php';

// Recebe os dados do React e transforma num array do PHP (o "true" faz isso)
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    echo json_encode(["success" => false, "message" => "Nenhum dado recebido."]);
    exit;
}

try {
    // 🚦 INICIA A TRANSAÇÃO: A partir daqui, as mudanças ficam "pendentes"
    $pdo->beginTransaction();

    // 1. Prepara para inserir a Triagem principal
    // O comando RETURNING id é um truque mágico do PostgreSQL para já devolver qual foi o ID gerado!
    $sqlTriagem = "INSERT INTO triagens (usuario_id, data, codigo_rastreio, motivo, defeito, numero_chamado, observacoes, link) 
                   VALUES (:usuario_id, CURRENT_DATE, :codigo_rastreio, :motivo, :defeito, :numero_chamado, :observacoes, :link) RETURNING id";
    
    $stmtT = $pdo->prepare($sqlTriagem);
    $stmtT->execute([
        ':usuario_id' => $dados['usuario_id'],
        ':codigo_rastreio' => $dados['codigo_rastreio'],
        ':motivo' => $dados['motivo'],
        ':defeito' => $dados['defeito'],
        ':numero_chamado' => $dados['numero_chamado'] ?? null,
        ':observacoes' => $dados['observacoes'] ?? null,
        ':link' => $dados['link'] ?? null
    ]);

    // Pega o ID da triagem que acabamos de criar
    $idTriagem = $stmtT->fetchColumn();

    // 2. Inserir os Equipamentos (se existirem)
    if (!empty($dados['equipamentos'])) {
        $sqlEquip = "INSERT INTO equipamentos (triagem_id, conteudo, quantidade, mac_address) 
                     VALUES (:triagem_id, :conteudo, :quantidade, :mac_address)";
        $stmtE = $pdo->prepare($sqlEquip);

        // Faz um loop na lista de equipamentos que veio do React
        foreach ($dados['equipamentos'] as $equip) {
            $stmtE->execute([
                ':triagem_id' => $idTriagem, // Usamos o ID gerado ali em cima!
                ':conteudo' => $equip['conteudo'],
                ':quantidade' => $equip['quantidade'],
                ':mac_address' => $equip['mac_address']
            ]);
        }
    }

    // 🟢 CONFIRMA A TRANSAÇÃO: Tudo deu certo, pode salvar de vez no banco!
    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Triagem salva com sucesso!", "id" => $idTriagem]);

} catch (Exception $e) {
    // 🔴 DEU ERRO: Cancela tudo que tentou fazer lá em cima (Rollback)
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Erro ao salvar no banco: " . $e->getMessage()]);
}
?>