<?php
$envPath = __DIR__ . '/.env';

if (is_file($envPath)) {
    $envVars = parse_ini_file($envPath, false, INI_SCANNER_RAW);

    if (is_array($envVars)) {
        foreach ($envVars as $key => $value) {
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$porta = getenv('DB_PORT') ?: '5432';
$banco = getenv('DB_NAME') ?: 'vendpago_triagem';
$usuario = getenv('DB_USER') ?: 'postgres';
$senha = getenv('DB_PASSWORD') ?: '';
$appEnv = getenv('APP_ENV') ?: 'production';

try {
    // Tentando conectar ao banco
    $pdo = new PDO("pgsql:host=$host;port=$porta;dbname=$banco", $usuario, $senha);

    // Configura o PDO para jogar os erros na tela caso algo dê errado
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conectado ao PostgreSQL com sucesso!";
} catch (PDOException $erro) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');

    $message = 'Erro ao conectar no banco de dados.';
    if ($appEnv === 'development') {
        $message .= ' ' . $erro->getMessage();
    }

    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
?>