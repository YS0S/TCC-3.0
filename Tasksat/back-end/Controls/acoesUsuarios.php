<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

if (!$con) {
    die("Erro de conexão com o banco de dados.");
}

$id = intval($_POST['id']);
$acao = strtolower(trim($_POST['acao'])); // força minúscula e remove espaços

$tiposValidos = ['normal', 'supervisor', 'admin'];

if ($acao === 'remover') {
    $sql = "DELETE FROM usuarios WHERE id = $id";
} elseif (in_array($acao, $tiposValidos)) {
    $sql = "UPDATE usuarios SET tipo = '$acao' WHERE id = $id";
} else {
    echo "Ação inválida: '$acao'";
    exit();
}

if ($con->query($sql)) {
    echo "ok";
} else {
    echo "Erro no banco: " . $con->error;
}

