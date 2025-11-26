<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';
session_start();
$tipoUsuario = $_SESSION['Cargo'] ?? 'normal';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Chamado não especificado.");
}

// Pega o chamado do banco
$result = $con->query("SELECT * FROM chamados WHERE id = $id LIMIT 1");
if (!$result || $result->num_rows == 0) {
    die("Chamado não encontrado.");
}
$chamado = $result->fetch_assoc();

// Caminho da imagem
$foto = "/Tasksat/front-end/storage/upload/" . (!empty($chamado["foto"]) ? $chamado["foto"] : "sem-foto.png");

// Data atual para o documento
$dataAtual = date('d/m/Y H:i:s');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Chamado Nº <?= $id ?> - Sistema de Manutenção Escolar</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Estilos gerais para um ar de documento oficial */
        @page {
            size: A4;
            margin: 2cm;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        
        .document {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border: 2px solid #000;
            box-shadow: none; /* Removido para impressão limpa */
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header .subtitle {
            font-size: 14pt;
            font-style: italic;
            margin: 0;
        }
        
        .header .info {
            font-size: 10pt;
            margin-top: 10px;
            color: #333;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .foto {
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f9f9f9;
        }
        
        .foto img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 0; /* Bordas retas para formalidade */
            border: 1px solid #000;
        }
        
        .foto-caption {
            font-size: 10pt;
            font-style: italic;
            margin-top: 5px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid #000;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 12px 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: right;
            width: 30%;
            font-size: 11pt;
        }
        
        td {
            font-size: 11pt;
        }
        
        .status-urgencia {
            font-weight: bold;
        }
        
        .footer {
            border-top: 2px solid #000;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        
        .buttons {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }
        
        .btn {
            padding: 10px 20px;
            background: #fff;
            color: #000;
            border: 1px solid #000;
            border-radius: 0; /* Bordas retas para formalidade */
            cursor: pointer;
            font-weight: bold;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
            transition: none; /* Sem transições para impressão */
        }
        
        .btn:hover {
            background: #f0f0f0;
        }
        
        /* Estilos para impressão/PDF */
        @media print {
            body { padding: 0; background: #fff; }
            .document { border: none; box-shadow: none; padding: 20px; }
            .buttons { display: none; } /* Esconde botões na impressão */
            .foto { page-break-inside: avoid; }
            table { page-break-inside: avoid; }
        }
        
        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .document { padding: 15px; }
            table { font-size: 10pt; }
            th, td { padding: 8px 4px; }
        }
    </style>
</head>
<body>

<div class="document" id="document">
    <!-- Cabeçalho Formal -->
    <div class="header">
        <h1>Relatório de Chamado</h1>
        <p class="subtitle">Sistema de Controle de Manutenção Escolar</p>
        <p class="info">
            Nº do Chamado: <?= $id ?> | Data de Geração: <?= $dataAtual ?> | Status: <?= htmlspecialchars($chamado['status']) ?>
        </p>
    </div>

    <div class="content">
        <!-- Imagem do Chamado -->
        <div class="foto">
            <img src="<?= htmlspecialchars($foto) ?>" alt="Foto do problema relatado">
            <p class="foto-caption">Imagem ilustrativa do chamado (se aplicável)</p>
        </div>

        <!-- Tabela de Detalhes como Formulário Oficial -->
        <table>
            <tr>
                <th>Descrição do Problema:</th>
                <td><?= nl2br(htmlspecialchars($chamado['descricao'])) ?></td>
            </tr>
            <tr>
                <th>Local do Ocorrência:</th>
                <td><?= htmlspecialchars($chamado['local']) ?></td>
            </tr>
            <tr>
                <th>Status Atual:</th>
                <td class="status-urgencia"><?= htmlspecialchars($chamado['status']) ?></td>
            </tr>
            <tr>
                <th>Nível de Urgência:</th>
                <td class="status-urgencia"><?= htmlspecialchars($chamado['urgencia_definitiva'] ?: $chamado['urgencia_user']) ?></td>
            </tr>
            <tr>
                <th>Relatado Por:</th>
                <td><?= htmlspecialchars($chamado['enviado_por']) ?></td>
            </tr>
            <tr>
                <th>Verificado Por:</th>
                <td><?= htmlspecialchars($chamado['verificado_por'] ?: 'Pendente') ?></td>
            </tr>
            <tr>
                <th>Resposta/Observações:</th>
                <td><?= nl2br(htmlspecialchars($chamado['resposta'] ?: 'Nenhuma resposta registrada ainda.')) ?></td>
            </tr>
            <tr>
                <th>Data/Hora de Abertura:</th>
                <td><?= htmlspecialchars($chamado['hora_abertura']) ?></td>
            </tr>
            <tr>
                <th>Data/Hora de Fechamento:</th>
                <td><?= htmlspecialchars($chamado['hora_fechamento'] ?: 'Em andamento') ?></td>
            </tr>
            <tr>
                <th>Última Atualização:</th>
                <td><?= htmlspecialchars($chamado['data_atualizacao']) ?></td>
            </tr>
        </table>
    </div>

    <!-- Rodapé Formal -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo Sistema de Controle de Chamados.</p>
        <p>Para mais informações, contate o setor de manutenção da escola.</p>
        <p>&copy; <?= date('Y') ?> - IFRJ Campus Pinheiral - Todos os direitos reservados.</p>
    </div>
</div>

<!-- Botões de Ação (visíveis apenas na tela) -->
<div class="buttons">
    <button class="btn" onclick="gerarPDF()">Gerar PDF</button>
    <a href="javascript:history.back()" class="btn">Voltar</a>
</div>

<script>
function gerarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4'); // portrait, pontos, A4

    const elementHTML = document.getElementById('document');

    html2canvas(elementHTML, { scale: 2, useCORS: true }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');

        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        // Ajusta a imagem para caber em uma única página A4
        let imgWidth = pageWidth - 40; // deixa pequenas margens
        let imgHeight = (canvas.height * imgWidth) / canvas.width;

        // Se a altura exceder a página, escala proporcionalmente
        if (imgHeight > pageHeight - 40) {
            imgHeight = pageHeight - 40;
            imgWidth = (canvas.width * imgHeight) / canvas.height;
        }

        // Centraliza a imagem
        const xOffset = (pageWidth - imgWidth) / 2;
        const yOffset = (pageHeight - imgHeight) / 2;

        doc.addImage(imgData, 'PNG', xOffset, yOffset, imgWidth, imgHeight);
        doc.save('chamado_<?= $id ?>.pdf');
    });
}
</script>

</body>
</html>