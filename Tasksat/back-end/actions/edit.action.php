<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';
session_start();

// Pega o id (tanto POST quanto GET)
$id = $_POST['id'] ?? $_GET['id'] ?? null;

if ($id === null) {
    echo "ID não informado!";
    exit;
}

// Valores enviados pelo formulário
$urgency  = $_POST['urgencia_definitiva'] ?? null;
$response = $_POST['resposta'] ?? null;
$status   = $_POST['status'] ?? null; 
$verified = $_SESSION['Name'] ?? 'Desconhecido';

$fileName = '';
$hora_fechamento = null;
if ($status === "Concluído" || $status === "Cancelado") {
    $hora_fechamento = date('Y-m-d H:i:s');
}

// Pega os dados antigos do chamado
$sqlOld = "SELECT * FROM chamados WHERE id = ?";
$stmtOld = $con->prepare($sqlOld);
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$resultOld = $stmtOld->get_result();
if ($resultOld->num_rows == 0) {
    echo "Chamado não encontrado!";
    exit;
}
$oldData = $resultOld->fetch_assoc();
$stmtOld->close();

// Função para registrar histórico
function registrarHistorico($con, $chamado_id, $campo, $valor_antigo, $valor_novo, $alterado_por) {
    if ($valor_antigo != $valor_novo) {
        $sqlHist = "INSERT INTO chamado_historico (chamado_id, alterado_por, campo_alterado, valor_antigo, valor_novo, data_alteracao)
                    VALUES (?, ?, ?, ?, ?, NOW())";
        $stmtHist = $con->prepare($sqlHist);
        $stmtHist->bind_param("issss", $chamado_id, $alterado_por, $campo, $valor_antigo, $valor_novo);
        $stmtHist->execute();
        $stmtHist->close();
    }
}

// Registrar alterações individuais
registrarHistorico($con, $id, "urgencia_definitiva", $oldData['urgencia_definitiva'], $urgency, $verified);
registrarHistorico($con, $id, "resposta", $oldData['resposta'], $response, $verified);
registrarHistorico($con, $id, "status", $oldData['status'], $status, $verified);
registrarHistorico($con, $id, "verificado_por", $oldData['verificado_por'], $verified, $verified);

// Se houver foto nova, registrar histórico e preparar atualização
$updatePhoto = false;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/front-end/public/storage/upload/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $originalName = basename($_FILES['foto']['name']);
    $fileName = uniqid() . '_' . $originalName;
    $destPath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destPath)) {
        echo "Erro ao salvar a foto!";
        exit;
    }

    registrarHistorico($con, $id, "foto", $oldData['foto'], $fileName, $verified);
    $updatePhoto = true;
}

// Monta query de atualização
if ($updatePhoto) {
    $sqlUpdate = "UPDATE chamados 
                  SET resposta = ?, urgencia_definitiva = ?, verificado_por = ?, status = ?, foto = ?, hora_fechamento = ? 
                  WHERE id = ?";
    $stmt = $con->prepare($sqlUpdate);
    $stmt->bind_param("ssssssi", $response, $urgency, $verified, $status, $fileName, $hora_fechamento, $id);
} else {
    $sqlUpdate = "UPDATE chamados 
                  SET resposta = ?, urgencia_definitiva = ?, verificado_por = ?, status = ?, hora_fechamento = ? 
                  WHERE id = ?";
    $stmt = $con->prepare($sqlUpdate);
    $stmt->bind_param("sssssi", $response, $urgency, $verified, $status, $hora_fechamento, $id);
}

// Executa atualização
if ($stmt->execute()) {
    echo "Chamado atualizado com sucesso! <a href='/Tasksat/index.php'>Voltar</a>";
} else {
    echo "Erro ao atualizar chamado: " . $stmt->error;
}

$stmt->close();
$con->close();
