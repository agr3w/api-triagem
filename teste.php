<?php
// Como o React roda na porta 5173 e o PHP na porta 80, o navegador bloqueia a comunicação por segurança (erro de CORS).
// O asterisco (*) diz: "Aceite requisições de qualquer lugar".
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Criamos um array simples no PHP
$resposta = [
    "status" => "sucesso",
    "mensagem" => "Olá, React! Aqui é o PHP falando do servidor Apache.",
    "tecnologia" => "PHP 8 + React"
];

// Convertemos o array do PHP para o formato JSON que o React entende
echo json_encode($resposta);
?>