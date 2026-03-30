<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require 'conexao.php';

try {
    // Busca as triagens já trazendo o nome do operador da tabela de usuários (LEFT JOIN)
    $sql = "SELECT t.*, u.nome as operador_nome 
            FROM triagens t 
            LEFT JOIN usuarios u ON t.usuario_id = u.id 
            ORDER BY t.criado_em DESC";
    
    $stmt = $pdo->query($sql);
    $triagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se tiver triagens, busca os equipamentos de todas elas de uma vez só
    if (count($triagens) > 0) {
        // Pega todos os IDs das triagens que achar
        $ids = array_column($triagens, 'id');
        $in = str_repeat('?,', count($ids) - 1) . '?'; // Cria as interrogações para a query
        
        $sqlEq = "SELECT * FROM equipamentos WHERE triagem_id IN ($in)";
        $stmtEq = $pdo->prepare($sqlEq);
        $stmtEq->execute($ids);
        $equipamentos = $stmtEq->fetchAll(PDO::FETCH_ASSOC);

        // Agrupa os equipamentos pela triagem correspondente
        $equipamentosPorTriagem = [];
        foreach ($equipamentos as $eq) {
            $equipamentosPorTriagem[$eq['triagem_id']][] = $eq;
        }

        // Devolve os equipamentos para dentro de cada pacote de triagem
        foreach ($triagens as &$t) {
            $t['equipamentos'] = $equipamentosPorTriagem[$t['id']] ?? [];
        }
    }

    // Retorna tudo bonitinho em JSON para o React
    echo json_encode(["success" => true, "data" => $triagens]);

} catch (Exception $e) {
    // Para produção, mudaremos essa mensagem para esconder o erro do banco
    echo json_encode(["success" => false, "message" => "Erro ao buscar dados: " . $e->getMessage()]);
}
?>