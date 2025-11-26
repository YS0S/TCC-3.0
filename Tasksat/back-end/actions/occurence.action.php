<?php 
    require_once __DIR__ .'/../src/config/config.php';
    session_start();

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['CadastroOccurence'])) {

            $date = date("Y-m-d H:i:s");
            $desc = $_POST['Descricao'];
            $loc = $_POST['Local'];
            $cate = $_POST['Categoria'];
            $sent = $_SESSION ['Name'];
            $urgency = $_POST ['Urgencia'];

            // garante que a pasta existe
            $uploadDir = __DIR__ . '/../../front-end/storage/upload/';

            // garante que a pasta existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadFile = $uploadDir . basename($_FILES['Foto']['name']);
            $Photoname = basename($_FILES['Foto']['name']);

            if (move_uploaded_file($_FILES['Foto']['tmp_name'], $uploadFile)) {
                echo "Upload realizado com sucesso!";
            } else {
                echo "Erro ao mover o arquivo.";
            }

            $sql = "INSERT INTO chamados (hora_abertura, descricao, local, categoria, enviado_por, urgencia_user, foto)  VALUES ('$date','$desc', '$loc', '$cate', '$sent','$urgency','$Photoname')";

            if ($con->query($sql) === TRUE) {
                echo "Chamado registrado com sucesso!";
            } else {
                echo "Erro ao registrar!" . $con->error;
            }
        }
    }

    echo "<meta http-equiv='Refresh' content='0;URL=/Tasksat/index.php'>";