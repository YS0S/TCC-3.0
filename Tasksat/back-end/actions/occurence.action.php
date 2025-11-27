<?php
require_once __DIR__ .'/../src/config/config.php'; // deve definir $con (mysqli)
session_start();

// Se quiser depurar, ative temporariamente:
// error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['CadastroOccurence'])) {

    // pegar valores com fallback (evita undefined index)
    $date = date("Y-m-d H:i:s");
    $desc = isset($_POST['Descricao']) ? trim($_POST['Descricao']) : '';
    $loc = isset($_POST['Local']) ? trim($_POST['Local']) : '';
    $cate = isset($_POST['Categoria']) ? trim($_POST['Categoria']) : '';
    $urgency = isset($_POST['Urgencia']) ? trim($_POST['Urgencia']) : '';

    // Evita undefined index em $_SESSION['Name']
    // Se a aplicação exige que o usuário esteja logado, você pode forçar um redirect se não tiver nome.
    $sent = isset($_SESSION['Name']) ? $_SESSION['Name'] : null;
    if ($sent === null) {
        // comportamento: ou atribui um valor padrão, ou interrompe e redireciona para login
        // aqui vou redirecionar de volta com mensagem (pode adaptar)
        // header("Location: /Tasksat/login.php");
        // exit;
        $sent = 'Usuário desconhecido';
    }

    // garante que a pasta existe
    $uploadDir = __DIR__ . '/../../front-end/storage/upload/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            // falha ao criar pasta
            die("Não foi possível criar diretório de upload.");
        }
    }

    $Photoname = null;
    // tratar upload apenas se existir
    if (isset($_FILES['Foto']) && is_array($_FILES['Foto'])) {
        $file = $_FILES['Foto'];

        if ($file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
            // opcional: sanitizar nome do arquivo para evitar problemas
            $Photoname = basename($file['name']);
            // evitar sobrescrever arquivos com mesmo nome: adicionar timestamp
            $Photoname = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $Photoname);
            $uploadFile = $uploadDir . $Photoname;

            if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
                // se mover falhar, definir Photoname null ou tratar
                $Photoname = null;
            }
        } else {
            // nenhum arquivo enviado ou erro no upload
            $Photoname = null;
        }
    }

    // prepared statement para inserir dados com segurança
    $sql = "INSERT INTO chamados (hora_abertura, descricao, local, categoria, enviado_por, urgencia_user, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $con->prepare($sql)) {
        // bind_param: s = string, use 's' para todos se colunas forem texto
        $fotoParam = $Photoname; // pode ser null
        $stmt->bind_param('sssssss', $date, $desc, $loc, $cate, $sent, $urgency, $fotoParam);

        if ($stmt->execute()) {
            // sucesso
            $stmt->close();
            // redireciona para index sem imprimir nada antes (header funciona)
            header("Location: /Tasksat/index.php");
            exit;
        } else {
            // erro ao executar
            $err = $stmt->error;
            $stmt->close();
            die("Erro ao registrar chamado: " . htmlspecialchars($err));
        }
    } else {
        die("Erro na preparação da query: " . htmlspecialchars($con->error));
    }
}

// se não for POST ou não estava setado, redireciona
header("Location: /Tasksat/index.php");
exit;
