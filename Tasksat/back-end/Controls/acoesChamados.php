<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

if (!$con) {
    die("Erro de conexão com o banco de dados.");
}

$id = intval($_POST['id']);
$acao = strtolower(trim($_POST['acao'])); // força minúscula e remove espaços

// Ações permitidas
$acoesValidas = ['remover', 'fechar', 'reabrir'];

if (!in_array($acao, $acoesValidas)) {
    echo "Ação inválida: '$acao'";
    exit();
}

switch ($acao) {
    case 'remover':
        $sql = "DELETE FROM chamados WHERE id = $id";
        break;
    case 'fechar':
        $sql = "UPDATE chamados SET status = 'Fechado', hora_fechamento = NOW(), data_atualizacao = NOW() WHERE id = $id";
        break;
    case 'reabrir':
        $sql = "UPDATE chamados SET status = 'Aberto', hora_fechamento = NULL, data_atualizacao = NOW() WHERE id = $id";
        break;
}

if ($con->query($sql)) {
    echo "ok";
} else {
    echo "Erro no banco: " . $con->error;
}

