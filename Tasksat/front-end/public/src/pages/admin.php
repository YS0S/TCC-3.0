<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

// Valida conexão
if (!$con) {
    die("Erro de conexão com o banco de dados.");
}

// Busca usuários
$resultUsers = $con->query("SELECT id, matricula, nome, email, tipo FROM usuarios ORDER BY criado_em DESC");

// Busca chamados
$resultChamados = $con->query("
    SELECT id, descricao, local, status, hora_abertura, hora_fechamento, enviado_por, data_atualizacao
    FROM chamados
    ORDER BY hora_abertura DESC
");

if (!$resultUsers || !$resultChamados) {
    die("Erro ao buscar dados no banco de dados.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
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
        .success { background: #16a34a; }
        .error { background: #dc2626; }
        @keyframes fade {
            0% { opacity: 0; transform: translateY(-10px); }
            10%,90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-10px); }
        }
    </style>
</head>
<body class="grid gap-6">

    <!-- 🔄 NOVA NAVBAR DO SEGUNDO CÓDIGO -->
    <?php 
        $base = $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/front-end/public/src/components/navbar/';
        
        switch ($_SESSION['Cargo'] ?? 'guest') {
            case 'admin':
                include($base . 'navbarAdmin.php');
                break;
            case 'supervisor':
                include($base . 'navbarSupervisor.php');
                break;
            case 'normal':
                include($base . 'navbarLogged.php');
                break;
            default:
                include($base . 'navbarGuest.php');
                break;
        }
    ?>

    <header class="w-full h-fit p-6 text-center bg-verde-150">
        <h1 class="text-3xl font-bold text-verde-250">Painel Administrativo</h1>
        <p class="text-verde-250 font-bold">Gerencie usuários e chamados</p>
    </header>

    <!-- Usuários -->
    <section class="w-full">
        <h2 class="w-full bg-verde-50 font-bold text-verde-250 p-2">Usuários</h2>
        <input type="text" placeholder="Pesquisar usuário..." onkeyup="filterTable('userTable', this.value)" class="border-2 rounded-md border-verde-250 w-64 p-2 mt-2 ml-2">
        <table id="userTable" class="ml-2 mt-2">
            <thead>
                <tr>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Matrícula</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Nome</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Email</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Tipo</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Ações</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Gerenciar</th>
                </tr>
            </thead>
            <tbody class="m-4">
                <?php while ($u = $resultUsers->fetch_assoc()): ?>
                    <tr>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($u['matricula']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($u['nome']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= $u['tipo'] === 'admin' ? 'Administrador' : 'Usuário' ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250">
                            <?php
                                if ($u['tipo'] === 'admin') {
                                    echo "Administrador";
                                } elseif ($u['tipo'] === 'supervisor') {
                                    echo "Supervisor";
                                } else {
                                    echo "Normal";
                                }
                            ?>
                        </td>
                        <td>
                            <?php if ($u['tipo'] === 'normal'): ?>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'supervisor')">Promover Supervisor</button>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'admin')">Promover Admin</button>
                            <?php elseif ($u['tipo'] === 'supervisor'): ?>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'normal')">Rebaixar p/ Normal</button>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'admin')">Promover Admin</button>
                            <?php elseif ($u['tipo'] === 'admin'): ?>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'supervisor')">Rebaixar p/ Supervisor</button>
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'normal')">Rebaixar p/ Normal</button>
                            <?php endif; ?>
                            <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarUsuario(<?= $u['id'] ?>, 'remover')">Remover</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <!-- Chamados -->
    <section>
        <h2 class="w-full bg-verde-50 font-bold text-verde-250 p-2">Chamados</h2>
        <input type="text" placeholder="Pesquisar chamado..." onkeyup="filterTable('chamadoTable', this.value)" class="border-2 rounded-md border-verde-250 w-64 p-2 mt-2 ml-2">
        <table id="chamadoTable" class="ml-2 mt-2">
            <thead>
                <tr>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">ID</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Descrição</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Local</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Status</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Abertura</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Atualização</th>
                    <th class="bg-verde-50 font-bold text-verde-250 border-2 border-white p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $resultChamados->fetch_assoc()): ?>
                    <tr>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= $c['id'] ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($c['descricao']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($c['local']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= htmlspecialchars($c['status']) ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= $c['hora_abertura'] ?></td>
                        <td class="font-semibold text-verde-200 border-2 border-verde-250"><?= $c['data_atualizacao'] ?></td>
                        <td>
                            <a href="detail_print.php?id=<?= $c['id'] ?>">
                                <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner">Acompanhar</button>
                            </a>
                            <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250 shadow-sm hover:shadow-inner" onclick="alterarChamado(<?= $c['id'] ?>, 'remover')">Remover</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <div id="toast-container"></div>

    <script>
    function showToast(msg, type="success") {
        const c = document.getElementById("toast-container");
        const t = document.createElement("div");
        t.className = "toast " + type;
        t.innerText = msg;
        c.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    function filterTable(tableId, value) {
        const rows = document.querySelectorAll(`#${tableId} tbody tr`);
        value = value.toLowerCase();
        rows.forEach(r => {
            r.style.display = r.innerText.toLowerCase().includes(value) ? "" : "none";
        });
    }

    function alterarUsuario(id, acao) {
        if (!confirm("Confirma a ação: " + acao + "?")) return;
        acao = acao.toLowerCase();
        fetch("/Tasksat/back-end/Controls/acoesusuarios.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `id=${id}&acao=${acao}`
        })
        .then(r => r.text())
        .then(r => {
            if (r.trim() === "ok") {
                showToast("Ação concluída com sucesso!", "success");
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast("Erro: " + r, "error");
            }
        });
    }

    function alterarChamado(id, acao) {
        if (!confirm("Confirma a ação: " + acao + "?")) return;
        fetch("/Tasksat/back-end/Controls/acoesChamados.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `id=${id}&acao=${acao}`
        })
        .then(r => r.text())
        .then(r => {
            if (r.trim() === "ok") {
                showToast("Chamado atualizado com sucesso!", "success");
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast("Erro: " + r, "error");
            }
        });
    }
    </script>
</body>
</html>
