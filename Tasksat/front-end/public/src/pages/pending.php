<?php
// pending.php - mesclado: funcionalidade original + layout/estética solicitada
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

// tipo de usuário
$tipoUsuario = $_SESSION['Cargo'] ?? 'normal';

// --- CONTAGEM (mantive sua query original) ---
$contagens = [
    'todos' => 0,
    'finalizados' => 0,
    'cancelados' => 0,
    'concluidos' => 0,
    'aguardando_analise' => 0,
    'aguardando_resolucao' => 0
];

$resultCounts = $con->query("
    SELECT 
        COUNT(*) as total,
        SUM(status='Cancelado') as cancelados,
        SUM(status='Concluído') as concluidos,
        SUM(status IN ('Concluído','Cancelado')) as finalizados,
        SUM(verificado_por IS NULL OR verificado_por='') as aguardando_analise,
        SUM(status IN ('Pausado','Em andamento')) as aguardando_resolucao
    FROM chamados
");

if ($resultCounts && $row = $resultCounts->fetch_assoc()) {
    $contagens['todos'] = (int)$row['total'];
    $contagens['cancelados'] = (int)$row['cancelados'];
    $contagens['concluidos'] = (int)$row['concluidos'];
    $contagens['finalizados'] = (int)$row['finalizados'];
    $contagens['aguardando_analise'] = (int)$row['aguardando_analise'];
    $contagens['aguardando_resolucao'] = (int)$row['aguardando_resolucao'];
}

// filtros vindos da URL (mantive sua lógica)
$filtro = $_GET['filtro'] ?? 'todos';
$subfiltro = $_GET['subfiltro'] ?? 'todos';
$data_abertura = $_GET['data_abertura'] ?? '';

$where = "";

// Filtro principal (igual ao seu)
switch ($filtro) {
    case 'finalizados':
        switch ($subfiltro) {
            case 'cancelados':
                $where = "WHERE status='Cancelado'";
                break;
            case 'concluidos':
                $where = "WHERE status='Concluído'";
                break;
            default:
                $where = "WHERE status IN ('Concluído','Cancelado')";
        }
        break;

    case 'aguardando_analise':
        $where = "WHERE (verificado_por IS NULL OR verificado_por='')";
        break;

    case 'aguardando_resolucao':
        $where = "WHERE status IN ('Pausado','Em andamento')";
        break;

    default:
        $where = "WHERE 1=1";
}

// filtro por data
if (!empty($data_abertura)) {
    $data_abertura = $con->real_escape_string($data_abertura);
    $where .= " AND DATE(hora_abertura) = '$data_abertura'";
}

// Query final (mantive sua ordenação)
$result = $con->query("SELECT * FROM chamados $where ORDER BY hora_abertura DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Chamados - Tasksat</title>

    <!-- Tailwind -->
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
                            100: '#b0de7f',
                            150: '#A9CC68',
                            200: '#93d411',
                            250: '#4F6E15',
                            300: '#467614',
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

    <!-- libs para export (mantive suas libs) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" defer></script>

    <style>
        .filter-btn {
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
        }
    </style>
</head>

<body>

    <?php
    $navbarBase = $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/front-end/public/src/components/navbar/';
    switch ($_SESSION['Cargo'] ?? 'guest') {
        case 'admin':
            include $navbarBase . 'navbarAdmin.php';
            break;
        case 'supervisor':
            include $navbarBase . 'navbarSupervisor.php';
            break;
        case 'normal':
            include $navbarBase . 'navbarLogged.php';
            break;
        default:
            include $navbarBase . 'navbarGuest.php';
            break;
    }
    ?>

    <div class="flex flex-col min-w-full min-h-screen items-center bg-gradient-to-tr from-verde-50 to-verde-50 via-verde-150 p-8 gap-6">

        <!-- TOPO -->
        <div class="flex flex-col w-4/5 bg-verde-50 h-fit p-3 gap-3 rounded-lg sm:flex-row sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-start">
                <button id="ativar-selecao"
                    class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner w-full sm:w-fit">
                    Selecionar
                </button>

                <button id="baixar-selecionados" style="display:none;"
                    class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner w-full sm:w-fit">
                    Baixar Selecionados
                </button>
            </div>

            <div>
                <a href="/Tasksat/index.php">
                    <button
                        class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner w-full sm:w-fit">
                        Voltar
                    </button>
                </a>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="flex flex-col w-4/5 bg-verde-50 h-fit p-3 gap-3 rounded-lg">
            <h1 class="font-bold text-verde-250 p-2">Filtros</h1>

            <div>
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <input class="hidden" type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                    <input class="hidden" type="hidden" name="subfiltro" value="<?= htmlspecialchars($subfiltro) ?>">

                    <label
                        class="bg-verde-100 font-bold text-verde-250 rounded-md p-2 border-verde-250 border-1">Filtrar
                        por data de abertura:
                        <input type="date" name="data_abertura"
                            value="<?= htmlspecialchars($data_abertura) ?>">
                    </label>

                    <button
                        class="w-full sm:w-fit p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner"
                        type="submit">
                        Filtrar
                    </button>

                    <a href="?filtro=<?= urlencode($filtro) ?>&subfiltro=<?= urlencode($subfiltro) ?>">
                        <button
                            class="w-full sm:w-fit h-full sm:h-fit p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-verde-250 shadow-sm hover:shadow-verde-250 hover:shadow-inner"
                            type="button">
                            Limpar
                        </button>
                    </a>
                </form>

                <!-- BOTÕES DE FILTRO ARRUMADOS -->
                <div class="flex flex-col sm:flex-row gap-3 mt-3">

                    <!-- TODOS -->
                    <a href="?filtro=todos">
                        <button
                            class="w-full sm:w-fit p-2 bg-verde-150 font-bold rounded-md text-verde-250 border-2 border-verde-250 hover:bg-verde-250 hover:text-white <?= $filtro == 'todos' ? 'active' : '' ?>">
                            Todos (<?= $contagens['todos'] ?>)
                        </button>
                    </a>

                    <!-- DROPDOWN FINALIZADOS -->
                    <div class="dropdown">
                        <button
                            class="w-full sm:w-fit <?= $filtro == 'finalizados' ? 'active' : '' ?>">
                            Finalizados (<?= $contagens['finalizados'] ?>)
                        </button>

                        <div class="dropdown-content">
                            <a href="?filtro=finalizados&subfiltro=todos">
                                Todos (<?= $contagens['finalizados'] ?>)
                            </a>

                            <a href="?filtro=finalizados&subfiltro=cancelados">
                                Cancelados (<?= $contagens['cancelados'] ?>)
                            </a>

                            <a href="?filtro=finalizados&subfiltro=concluidos">
                                Concluídos (<?= $contagens['concluidos'] ?>)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- OUTROS FILTROS -->
                <div class="flex flex-col sm:flex-row gap-3 mt-3">

                    <a href="?filtro=aguardando_analise">
                        <button
                            class="w-full sm:w-fit p-2 bg-verde-150 font-bold rounded-md text-verde-250 border-2 border-verde-250 hover:bg-verde-250 hover:text-white <?= $filtro == 'aguardando_analise' ? 'active' : '' ?>">
                            Aguardando Análise (<?= $contagens['aguardando_analise'] ?>)
                        </button>
                    </a>

                    <a href="?filtro=aguardando_resolucao">
                        <button
                            class="w-full sm:w-fit p-2 bg-verde-150 font-bold rounded-md text-verde-250 border-2 border-verde-250 hover:bg-verde-250 hover:text-white <?= $filtro == 'aguardando_resolucao' ? 'active' : '' ?>">
                            Aguardando Resolução (<?= $contagens['aguardando_resolucao'] ?>)
                        </button>
                    </a>

                </div>
            </div>
        </div>

        <section class="bg-verde-50 bg-opacity-90 rounded-lg p-4 shadow-md w-4/5">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                <?php
                if ($result && $result->num_rows > 0) {
                    while ($linha = $result->fetch_assoc()) {
                        $id = (int)$linha["id"];
                        $statusAtual = $linha['status'];
                        $foto = "/Tasksat/front-end/storage/upload/" . (!empty($linha["foto"]) ? $linha["foto"] : "sem-foto.png");
                ?>

                        <article class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden transform hover:-translate-y-1 transition"
                            data-id="<?= $id ?>">

                            <div class="relative">
                                <input type="checkbox" class="select-chamado absolute top-2 left-2 z-10 hidden"
                                    value="<?= $id ?>">
                                <img src="<?= htmlspecialchars($foto) ?>"
                                    alt="<?= htmlspecialchars($linha['descricao']) ?>"
                                    class="w-full h-40 object-cover">
                            </div>

                            <div class="p-3">
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li><strong>ID:</strong> <?= $id ?></li>
                                    <li><strong>Descrição:</strong> <?= htmlspecialchars($linha["descricao"]) ?></li>
                                    <li><strong>Local:</strong> <?= htmlspecialchars($linha["local"]) ?></li>
                                    <li><strong>Status:</strong> <span
                                            class="font-semibold text-verde-300"><?= htmlspecialchars($statusAtual) ?></span>
                                    </li>
                                    <li><strong>Urgência:</strong>
                                        <?= htmlspecialchars($linha['urgencia_definitiva'] ?: $linha['urgencia_user']) ?>
                                    </li>
                                    <li><strong>Relatado por:</strong> <?= htmlspecialchars($linha["enviado_por"]) ?></li>
                                    <li><strong>Verificado por:</strong>
                                        <?= htmlspecialchars($linha["verificado_por"] ?: '-') ?>
                                    </li>
                                    <li><strong>Última atualização:</strong>
                                        <?= htmlspecialchars($linha["data_atualizacao"]) ?>
                                    </li>
                                </ul>

                                <div class="flex gap-2 mt-3">

                                    <a href="/Tasksat/front-end/public/src/pages/detail.php?id=<?= $id ?>"
                                        class="flex-1 text-center py-2 px-2 bg-white border border-verde-150 rounded text-sm font-semibold hover:bg-verde-50">
                                        Acompanhar
                                    </a>

                                    <?php if ($tipoUsuario == 'supervisor' && !in_array($statusAtual, ['Cancelado', 'Concluído'])): ?>
                                        <a href="/Tasksat/front-end/public/src/pages/edit.php?id=<?= $id ?>"
                                            class="flex-1 text-center py-2 px-2 bg-white border border-verde-150 rounded text-sm font-semibold hover:bg-verde-50">
                                            Editar
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($tipoUsuario == 'admin'): ?>
                                        <a href="/Tasksat/front-end/public/src/pages/edit.php?id=<?= $id ?>"
                                            class="flex-1 text-center py-2 px-2 bg-white border border-verde-150 rounded text-sm font-semibold hover:bg-verde-50">
                                            Editar
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </article>

                <?php
                    }
                } else {
                    echo '<p class="col-span-full text-center text-gray-500 py-8">Nenhum chamado encontrado.</p>';
                }
                ?>

            </div>
        </section>

    </div>

    <!-- LISTAGEM DOS CHAMADOS -->


    <style>
        .dropdown button {
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-weight: bold;
            background: #A9CC68;
            color: #4F6E15;
            border-width: 2px;
            border-color: #4F6E15;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown button:hover {
            background-color: #4F6E15;
            color: white;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #A9CC68;
            min-width: 160px;
            box-shadow: #4F6E15;
            z-index: 1;
            border-radius: 6px;
            padding: 5px 0;
            color: #4F6E15;
            font-weight: bold;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: #4F6E15;
            padding: 6px 12px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            color: black;
        }
    </style>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ativar seleção
            const ativar = document.getElementById('ativar-selecao');
            const baixar = document.getElementById('baixar-selecionados');

            ativar.addEventListener('click', () => {
                document.querySelectorAll('.select-chamado').forEach(cb => cb.classList.remove('hidden'));
                baixar.classList.remove('hidden');
            });

            // exportar selecionados
            baixar.addEventListener('click', () => {
                const selecionados = Array.from(document.querySelectorAll('.select-chamado:checked'))
                    .map(cb => cb.value);

                if (selecionados.length === 0) {
                    alert('Selecione pelo menos um chamado.');
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('p', 'pt', 'a4');
                let pageIndex = 0;

                (function process(i) {
                    if (i >= selecionados.length) {
                        doc.save('chamados_selecionados.pdf');

                        document.querySelectorAll('.select-chamado')
                            .forEach(cb => cb.classList.add('hidden'));
                        baixar.classList.add('hidden');

                        return;
                    }

                    const id = selecionados[i];

                    fetch(`/Tasksat/front-end/public/src/pages/detail_print.php?id=${id}&ajax=1`)
                        .then(r => r.text())
                        .then(html => {
                            const wrap = document.createElement('div');
                            wrap.style.width = '800px';
                            wrap.style.padding = '30px';
                            wrap.innerHTML = html;
                            document.body.appendChild(wrap);

                            html2canvas(wrap, {
                                scale: 2,
                                useCORS: true
                            }).then(canvas => {

                                const imgData = canvas.toDataURL('image/png');
                                const pageWidth = doc.internal.pageSize.getWidth();
                                const pageHeight = doc.internal.pageSize.getHeight();

                                let imgWidth = pageWidth - 40;
                                let imgHeight = (canvas.height * imgWidth) / canvas.width;

                                if (imgHeight > pageHeight - 40) {
                                    imgHeight = pageHeight - 40;
                                    imgWidth = (canvas.width * imgHeight) / canvas.height;
                                }

                                const x = (pageWidth - imgWidth) / 2;
                                const y = (pageHeight - imgHeight) / 2;

                                if (pageIndex > 0) doc.addPage();

                                doc.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
                                pageIndex++;

                                document.body.removeChild(wrap);
                                process(i + 1);

                            }).catch(err => {
                                console.error('Erro html2canvas', err);
                                document.body.removeChild(wrap);
                                process(i + 1);
                            });

                        }).catch(err => {
                            console.error('Erro fetch detail_print', err);
                            process(i + 1);
                        });

                })(0);

            });
        });
    </script>

</body>

</html>