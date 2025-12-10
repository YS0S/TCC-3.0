<?php
ob_start(); // Buffer de saída para evitar problemas com headers

require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';
session_start();

if (!isset($_GET['id'])) {
    die("ID do chamado não informado.");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM chamados WHERE id = $id";
$result = $con->query($sql);
if ($result->num_rows == 0) {
    die("Chamado não encontrado.");
}
$chamado = $result->fetch_assoc();

$sqlHist = "SELECT * FROM chamado_historico WHERE chamado_id = $id ORDER BY data_alteracao DESC";
$resultHist = $con->query($sqlHist);
$historico = [];
if ($resultHist) {
    while ($row = $resultHist->fetch_assoc()) {
        $historico[] = $row;
    }
}

// LÓGICA DE ATUALIZAÇÃO INTEGRADA NA PÁGINA
$success_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $chamado_id = intval($_POST['id']);
    $urgencia_definitiva = $_POST['urgencia_definitiva'] ?? '';
    $status = $_POST['status'] ?? '';
    $resposta = $_POST['resposta'] ?? '';

    // Atualizar o chamado
    $update_sql = "UPDATE chamados SET urgencia_definitiva = ?, status = ?, resposta = ? WHERE id = ?";
    $stmt = $con->prepare($update_sql);
    $stmt->bind_param("sssi", $urgencia_definitiva, $status, $resposta, $chamado_id);
    
    if ($stmt->execute()) {
        // Registrar no histórico (exemplo simplificado; ajuste conforme necessário)
        // Aqui você pode adicionar lógica para registrar mudanças no histórico
        $success_message = 'Atualizado com sucesso!';
    } else {
        // Se erro, pode definir uma mensagem de erro, mas focando no sucesso
        $success_message = 'Erro ao atualizar!';
    }
    $stmt->close();
}

function esc($v) {
    return htmlspecialchars($v ?? '');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Chamado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'verde': {
                DEFAULT: '#669f2a',
                50: '#E0FFC0',
                70: '#A2EC16',
                100 : '#b0de7f',
                150: '#A9CC68',
                200 : '#93d411',
                250: '#4F6E15',
                300 : '#467614',
              },
              'vermelho': {
                DEFAULT: '#d64919',
                100: '#e66d42',
                150: '#E72C22',
                200: '#903923',
                250: '#870B00',
              }
            }
          }
        }
      }
    </script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        textarea { min-height: 120px; resize: vertical; }
        #required::after { content: " *"; color: #e74c3c; }
        .full-width { flex: 1 1 100%; }
        .urgent { border-left: 4px solid #467614; padding-left: 10px; }
        .footer { margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 20px; }
        .historico { margin-top: 30px; border-top: 2px solid #467614; padding-top: 20px; }
        .historico h2 { margin-bottom: 15px; color: #4F6E15; }
        .historico-item { background:#f9f9f9; padding:10px; border-radius:5px; margin-bottom:10px; border-left: 4px solid #4F6E15; }
        .historico-item span { font-weight:bold; }
        @media (max-width:768px) { .form-group { flex:1 1 100%; } }
        /* Animação suave para o popup aparecer e desaparecer */
        #success-popup {
            transition: opacity 0.5s ease-in-out;
        }
        #success-popup.fade-out {
            opacity: 0;
        }
    </style>
</head>

<body class="bg-gradient-to-tr from-verde-50 to-verde-50 via-verde-150 grid">

    <!-- POPUP DE SUCESSO, SE HOUVER -->
    <?php if ($success_message): ?>
        <div id="success-popup" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-verde-150 text-white px-6 py-4 rounded-lg shadow-lg z-50 text-center max-w-sm w-full">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <script>
            setTimeout(function() {
                var popup = document.getElementById('success-popup');
                if (popup) {
                    popup.classList.add('fade-out');
                    setTimeout(function() {
                        popup.style.display = 'none';
                    }, 500); // Tempo para a transição de fade-out
                }
            }, 2000);
        </script>
    <?php endif; ?>

<div class="w-4/5 h-fit bg-white rounded-lg p-6 grid self-center justify-self-center gap-6 m-4">
    
    <h1 class="text-center text-verde-250 text-3xl font-bold">Editar Chamado</h1>

    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id); ?>" id="chamado-form">
        <input type="hidden" name="id" value="<?= esc($chamado['id']) ?>">

        <div class="grid grid-cols-2 w-full gap-4">
            <div class="grid gap-2">
                <label for="enviado_por" class="font-bold text-verde-250 text-lg" id="required">Enviado por</label>
                <input type="text" value="<?= esc($chamado['enviado_por']) ?>" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" readonly>
            </div>

            <div class="grid gap-2">
                <label for="local" class="font-bold text-verde-250 text-lg">Local</label>
                <input type="text" value="<?= esc($chamado['local']) ?>" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" readonly>
            </div>

            <div class="grid gap-2">
                <label for="categoria" class="font-bold text-verde-250 text-lg">Categoria</label>
                <input type="text" value="<?= esc($chamado['categoria']) ?>" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" readonly>
            </div>

            <div class="grid gap-2">
                <label for="urgencia_user" id="required" class="font-bold text-verde-250 text-lg">Urgência (usuário)</label>
                <input type="text" value="<?= esc($chamado['urgencia_user']) ?>" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" readonly>
            </div>

            <div class="grid gap-2 col-span-2">
                <label for="descricao" id="required" class="font-bold text-verde-250 text-lg">Descrição do problema</label>
                <input type="text" value="<?= esc($chamado['descricao']) ?>" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" readonly>
            </div>
        </div>

        <div class="mt-3">
            <label for="foto" class="font-bold text-verde-250 text-lg">Foto atual:</label><br>

            <?php if (!empty($chamado['foto'])): ?>
                <img 
                    src="<?= '/Tasksat/front-end/public/storage/upload/' . rawurlencode($chamado['foto']) ?>" 
                    alt="Foto do chamado" 
                    width="250" 
                    style="margin-bottom:10px; border:1px solid #ddd; border-radius:4px;"
                >
            <?php else: ?>
                <p class="font-bold text-verde-250 text-lg">Nenhuma foto enviada.</p>
            <?php endif; ?>

            <!-- ATUALIZAR FOTO REMOVIDO -->
        </div>

        <br>

        <div class="grid grid-cols-2 w-full gap-4 mb-4">
            <div class="form-group">
                <label for="urgencia_definitiva" class="font-bold text-verde-250 text-lg">Urgência Definitiva</label>
                <select id="urgencia_definitiva" name="urgencia_definitiva">
                    <option value="">Aguardando análise</option>
                    <option value="Leve" <?= $chamado['urgencia_definitiva'] == 'Leve' ? 'selected' : '' ?>>Leve</option>
                    <option value="Mediana" <?= $chamado['urgencia_definitiva'] == 'Mediana' ? 'selected' : '' ?>>Mediana</option>
                    <option value="Extrema" <?= $chamado['urgencia_definitiva'] == 'Extrema' ? 'selected' : '' ?>>Extrema</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status" class="font-bold text-verde-250 text-lg">Status</label>
                <select id="status" name="status">
                    <?php
                    $statuses = ['Em análise','Em andamento','Pausado','Cancelado','Concluído'];
                    foreach ($statuses as $s) {
                        $sel = ($chamado['status']==$s) ? 'selected' : '';
                        echo "<option value='$s' $sel>$s</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="grid w-full">
            <div class="form-group full-width">
                <label for="resposta" class="font-bold text-verde-250 text-lg">Resposta Técnica</label>
                <textarea id="resposta" name="resposta" class="p-3 w-full bg-white border-1 border-verde-250 rounded-md" style="resize: none;"><?= esc($chamado['resposta']) ?></textarea>
            </div>
        </div>

        <div class="footer">
            <button type="submit" class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner">Atualizar</button>
        </div>
    </form>

    <div class="historico">
        <h2 class="font-bold text-verde-250 text-lg">Histórico de Alterações</h2>

        <?php if (count($historico) > 0): ?>
            <?php foreach ($historico as $h): ?>
                <div class="historico-item">
                    <p><span>Campo:</span> <?= esc($h['campo_alterado']) ?></p>
                    <p><span>Valor antigo:</span> <?= esc($h['valor_antigo']) ?></p>
                    <p><span>Valor novo:</span> <?= esc($h['valor_novo']) ?></p>
                    <p><span>Alterado por:</span> <?= esc($h['alterado_por']) ?></p>
                    <p><span>Data:</span> <?= esc($h['data_alteracao']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="font-bold text-lg">Nenhuma alteração registrada.</p>
        <?php endif; ?>
    </div>

    <a href="/Tasksat/index.php">
        <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner w-fit">Voltar</button>
    </a>
    
</div>

<script>
    // Removido o event listener, pois agora processamos na mesma página
</script>

</body>
</html>
