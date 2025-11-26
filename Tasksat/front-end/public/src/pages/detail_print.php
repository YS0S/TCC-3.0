<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';
$id = $_GET['id'] ?? 0;

$result = $con->query("SELECT * FROM chamados WHERE id = ".intval($id));
$chamado = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Chamado #<?= $chamado['id'] ?></title>
<style>
    /* Forçar tamanho A4 */
    body {
        margin: 0;
        padding: 0;
        background: #ccc;
    }
    .pagina-a4 {
        width: 794px;   /* A4 em px (96dpi) */
        height: 1123px; /* A4 em px (96dpi) */
        margin: auto;
        background: #fff;
        padding: 30px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }
    h2 { text-align:center; margin-bottom:20px; }
    ul { list-style:none; padding:0; font-size:14px; }
    li { margin-bottom:6px; }
    img { max-width:100%; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>
<div class="pagina-a4">
    <h2>Detalhes do Chamado #<?= $chamado['id'] ?></h2>
    <?php
    $foto = "/Tasksat/front-end/storage/upload/" . (!empty($chamado["foto"]) ? $chamado["foto"] : "sem-foto.png");
    echo "<img src='".htmlspecialchars($foto)."' alt='Foto do Chamado'>";
    ?>
    <ul>
        <li><b>Descrição:</b> <?= htmlspecialchars($chamado['descricao']) ?></li>
        <li><b>Local:</b> <?= htmlspecialchars($chamado['local']) ?></li>
        <li><b>Status:</b> <?= htmlspecialchars($chamado['status']) ?></li>
        <li><b>Urgência:</b> <?= htmlspecialchars($chamado['urgencia_definitiva'] ?: $chamado['urgencia_user']) ?></li>
        <li><b>Relatado por:</b> <?= htmlspecialchars($chamado['enviado_por']) ?></li>
        <li><b>Verificado por:</b> <?= htmlspecialchars($chamado['verificado_por'] ?: '-') ?></li>
        <li><b>Resposta:</b> <?= htmlspecialchars($chamado['resposta'] ?: '-') ?></li>
        <li><b>Hora abertura:</b> <?= htmlspecialchars($chamado['hora_abertura']) ?></li>
        <li><b>Hora fechamento:</b> <?= htmlspecialchars($chamado['hora_fechamento'] ?: 'Em andamento ainda') ?></li>
        <li><b>Última atualização:</b> <?= htmlspecialchars($chamado['data_atualizacao']) ?></li>
    </ul>
</div>
</body>
</html>