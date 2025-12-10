<?php
session_start();

/* ==========================================================
   CONEXÃO COM O BANCO
========================================================== */
$con = new mysqli("localhost", "root", "", "sakila-01");
if ($con->connect_error) {
    die("Erro de conexão: " . $con->connect_error);
}

$msg = "";

/* ==========================================================
   INCLUSÃO DO CONFIG.PHP
========================================================== */
include __DIR__ . '/../../../../back-end/src/config/config.php';

/* ==========================================================
   CAMINHO DAS NAVBARS
========================================================== */
$navbarPath = __DIR__ . '/../components/navbar/';

$navbarFile = '';
switch ($_SESSION['Cargo'] ?? 'guest') {
    case 'admin':
        $navbarFile = $navbarPath . 'navbarAdmin.php';
        break;
    case 'supervisor':
        $navbarFile = $navbarPath . 'navbarSupervisor.php';
        break;
    case 'normal':
        $navbarFile = $navbarPath . 'navbarLogged.php';
        break;
    default:
        $navbarFile = $navbarPath . 'navbarGuest.php';
        break;
}

if (!file_exists($navbarFile)) {
    die("Erro: arquivo da navbar não encontrado: $navbarFile");
}
include $navbarFile;

/* ==========================================================
   AÇÕES — LOCAL
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao_local'])) {
        $acao_local = $_POST['acao_local'];
        $local_nome = trim($_POST['local_nome'] ?? '');

        if ($local_nome !== '') {
            $local_nome_esc = $con->real_escape_string($local_nome);

            if ($acao_local === 'adicionar') {
                // Adiciona SEM área (se quiser obrigar área, posso implementar)
                $sql = "INSERT INTO local (nome, area_id) VALUES ('$local_nome_esc', 0)";
                $msg = $con->query($sql) ?
                    "Local adicionado com sucesso!" :
                    "Erro ao adicionar local: " . $con->error;

            } elseif ($acao_local === 'remover') {
                $sql = "DELETE FROM local WHERE nome='$local_nome_esc'";
                $msg = $con->query($sql) ?
                    "Local removido com sucesso!" :
                    "Erro ao remover local: " . $con->error;
            }

        } else {
            $msg = "O campo local não pode estar vazio!";
        }
    }

    /* ==========================================================
       AÇÕES — ÁREA
    ========================================================== */
    if (isset($_POST['acao_area'])) {
        $acao_area = $_POST['acao_area'];
        $area_nome = trim($_POST['area_nome'] ?? '');

        if ($area_nome !== '') {
            $area_nome_esc = $con->real_escape_string($area_nome);

            if ($acao_area === 'adicionar') {
                $sql = "INSERT INTO area (nome) VALUES ('$area_nome_esc')";
                $msg = $con->query($sql) ?
                    "Área adicionada com sucesso!" :
                    "Erro ao adicionar área: " . $con->error;

            } elseif ($acao_area === 'remover') {
                $sql = "DELETE FROM area WHERE nome='$area_nome_esc'";
                $msg = $con->query($sql) ?
                    "Área removida com sucesso!" :
                    "Erro ao remover área: " . $con->error;
            }

        } else {
            $msg = "O campo área não pode estar vazio!";
        }
    }

    /* ==========================================================
       AÇÕES — CATEGORIA
    ========================================================== */
    if (isset($_POST['acao_categoria'])) {
        $acao_categoria = $_POST['acao_categoria'];
        $categoria_nome = trim($_POST['categoria_nome'] ?? '');

        if ($categoria_nome !== '') {
            $categoria_nome_esc = $con->real_escape_string($categoria_nome);

            if ($acao_categoria === 'adicionar') {
                $sql = "INSERT INTO categorias (nome) VALUES ('$categoria_nome_esc')";
                $msg = $con->query($sql) ?
                    "Categoria adicionada com sucesso!" :
                    "Erro ao adicionar categoria: " . $con->error;

            } elseif ($acao_categoria === 'remover') {
                $sql = "DELETE FROM categorias WHERE nome='$categoria_nome_esc'";
                $msg = $con->query($sql) ?
                    "Categoria removida com sucesso!" :
                    "Erro ao remover categoria: " . $con->error;
            }

        } else {
            $msg = "O campo categoria não pode estar vazio!";
        }
    }
}

/* ==========================================================
   BUSCAS — ÁREA E CATEGORIA
========================================================== */
$resultAreas = $con->query("SELECT id, nome FROM area ORDER BY nome ASC");
$areas = $resultAreas->fetch_all(MYSQLI_ASSOC);

$resultCategorias = $con->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
$categorias = $resultCategorias->fetch_all(MYSQLI_ASSOC);

/* ==========================================================
   LISTA DE LOCAIS COM FILTRO POR ÁREA
========================================================== */
$filtro_area = $_GET['filtro_area'] ?? '';

if ($filtro_area !== '') {
    $stmt = $con->prepare("
        SELECT id, nome
        FROM local
        WHERE area_id = ?
        ORDER BY nome ASC
    ");
    $stmt->bind_param("i", $filtro_area);
    $stmt->execute();
    $resultLocais = $stmt->get_result();
} else {
    $resultLocais = $con->query("
        SELECT id, nome
        FROM local
        ORDER BY nome ASC
    ");
}

$locais = $resultLocais->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Alerta aí!</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: Arial;}
    fieldset { margin-bottom: 20px; padding: 12px; }
    table { border-collapse: collapse; width: 450px; }
    th, td { padding: 6px; border: 1px solid #467614; }
</style>
</head>
<body class="grid min-w-full min-h-screen gap-6">

<header class="w-full h-fit p-6 text-center bg-verde-150">
    <h1 class="text-3xl font-bold text-verde-250">Painel de Supervisão</h1>
    <p class="text-verde-250 font-bold">Gerencie áreas, categorias e locais</p>
</header>

<?php if ($msg): ?>
<p><strong><?= htmlspecialchars($msg) ?></strong></p>
<?php endif; ?>

<!-- LOCAIS -->
<section>
<h2 class="w-full bg-verde-50 font-bold text-verde-250 p-2">Locais</h2>
<div class="grid grid-cols-2 gap-3 p-4">

<fieldset class="border-2 border-verde-250 gap-3 h-fit">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Adicionar Local</legend>
<form method="POST">
    <input type="hidden" name="acao_local" value="adicionar">
    <input type="text" name="local_nome" placeholder="Nome do local" class="border-2 border-verde-250 rounded-md p-2" required>
    <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Adicionar</button>
</form>
</fieldset>

<fieldset class="border-2 border-verde-250 gap-3">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Locais Cadastrados</legend>

<!-- 🔍 FILTRO POR ÁREA -->
<form method="GET" class="ml-2 mt-2 mb-4">
    <label class="font-bold text-verde-250">Filtrar por área:</label>
    <select name="filtro_area" class="border-2 border-verde-250 rounded-md p-2">
        <option value="">Todas as áreas</option>

        <?php foreach($areas as $a): ?>
            <option value="<?= $a['id'] ?>"
                <?= ($filtro_area == $a['id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($a['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">
        Filtrar
    </button>
</form>

<table class="ml-2 mt-2">
<tr><th>Nome</th><th>Ações</th></tr>
<?php foreach($locais as $l): ?>
<tr>
    <td><?= htmlspecialchars($l['nome']) ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="acao_local" value="remover">
            <input type="hidden" name="local_nome" value="<?= htmlspecialchars($l['nome']) ?>">
            <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Remover</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

</fieldset>
</div>
</section>

<!-- ÁREAS -->
<section>
<h2 class="w-full bg-verde-50 font-bold text-verde-250 p-2">Áreas</h2>
<div class="grid grid-cols-2 gap-3 p-4">

<fieldset class="border-2 border-verde-250 gap-3 h-fit">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Adicionar Área</legend>
<form method="POST">
    <input type="hidden" name="acao_area" value="adicionar">
    <input type="text" name="area_nome" placeholder="Nome da área" class="border-2 border-verde-250 rounded-md p-2" required>
    <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Adicionar</button>
</form>
</fieldset>

<fieldset class="border-2 border-verde-250 gap-3">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Áreas Cadastradas</legend>
<table class="ml-2 mt-2">
<tr><th>Nome</th><th>Ações</th></tr>
<?php foreach($areas as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['nome']) ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="acao_area" value="remover">
            <input type="hidden" name="area_nome" value="<?= htmlspecialchars($a['nome']) ?>">
            <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Remover</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
</fieldset>

</div>
</section>

<!-- CATEGORIAS -->
<section>
<h2 class="w-full bg-verde-50 font-bold text-verde-250 p-2">Categorias</h2>
<div class="grid grid-cols-2 gap-3 p-4">

<fieldset class="border-2 border-verde-250 gap-3 h-fit">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Adicionar Categoria</legend>
<form method="POST">
    <input type="hidden" name="acao_categoria" value="adicionar">
    <input type="text" name="categoria_nome" placeholder="Nome da categoria" class="border-2 border-verde-250 rounded-md p-2" required>
    <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Adicionar</button>
</form>
</fieldset>

<fieldset class="border-2 border-verde-250 gap-3">
<legend class="font-bold text-xl text-verde-250 p-1 bg-white">Categorias Cadastradas</legend>
<table class="ml-2 mt-2">
<tr><th>Nome</th><th>Ações</th></tr>
<?php foreach($categorias as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['nome']) ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="acao_categoria" value="remover">
            <input type="hidden" name="categoria_nome" value="<?= htmlspecialchars($c['nome']) ?>">
            <button class="p-2 bg-verde-150 font-bold rounded-md text-verde-250">Remover</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
</fieldset>

</div>
</section>

</body>
</html>
