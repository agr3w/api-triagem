<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require 'conexao.php';

try {
    $dataInicial = $_GET['data_inicial'] ?? null;
    $dataFinal = $_GET['data_final'] ?? null;

    if ($dataInicial && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicial)) {
        echo json_encode(["success" => false, "message" => "Data inicial invalida."]);
        exit;
    }

    if ($dataFinal && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFinal)) {
        echo json_encode(["success" => false, "message" => "Data final invalida."]);
        exit;
    }

    $filtros = [];
    $params = [];

    if ($dataInicial) {
        $filtros[] = "data >= :data_inicial";
        $params[':data_inicial'] = $dataInicial;
    }

    if ($dataFinal) {
        $filtros[] = "data <= :data_final";
        $params[':data_final'] = $dataFinal;
    }

    $whereSql = empty($filtros) ? "" : " WHERE " . implode(" AND ", $filtros);

    $stats = [];

    $sqlTotal = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN finalizado = true THEN 1 ELSE 0 END) as finalizados,
                    SUM(CASE WHEN finalizado = false THEN 1 ELSE 0 END) as pendentes
                 FROM triagens" . $whereSql;
    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $stats['resumo'] = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    $sqlDefeitos = "SELECT defeito, COUNT(*) as quantidade 
                    FROM triagens 
                    " . $whereSql . "
                    GROUP BY defeito 
                    ORDER BY quantidade DESC 
                    LIMIT 5";
    $stmtDefeitos = $pdo->prepare($sqlDefeitos);
    $stmtDefeitos->execute($params);
    $stats['defeitos'] = $stmtDefeitos->fetchAll(PDO::FETCH_ASSOC);

    $sqlDias = "SELECT data, COUNT(*) as quantidade 
                FROM triagens 
                " . $whereSql . "
                GROUP BY data 
                ORDER BY data ASC 
                LIMIT 7"; 
    $stmtDias = $pdo->prepare($sqlDias);
    $stmtDias->execute($params);
    $stats['dias'] = $stmtDias->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $stats]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao buscar estatísticas: " . $e->getMessage()]);
}
