<?php
    require_once __DIR__ .'/../src/config/config.php';
    session_start();

        if (isset($_POST['LoginUser'])) {
            $email = $_POST ['Email'];
            $senha = $_POST ['Senha'];

            $sql = "SELECT matricula, nome, cpf, telefone, data_nascimento, sexo, departamento, senha, criado_em, tipo FROM usuarios WHERE email = '$email'";
            $result = $con->query($sql);

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if(password_verify($senha, $row ['senha'])) {
                    $_SESSION ['Registro'] = $row ['matricula'];
                    $_SESSION ['Name'] = $row ['nome'];
                    $_SESSION ['Cpf'] = $row ['cpf'];
                    $_SESSION ['Numero'] = $row ['telefone'];
                    $_SESSION ['DataNasc'] = $row ['data_nascimento'];
                    $_SESSION ['Sexo'] = $row ['sexo'];
                    $_SESSION ['Departamento'] = $row ['departamento'];
                    $_SESSION ['CriadoQuando'] = $row ['criado_em'];
                    $_SESSION ['Cargo'] = $row ['tipo'];
                    header("Location:/Tasksat/index.php");
                    exit;
                } else {
                    echo 'Senha incorreta!';
                }
                } else{
                    echo 'Email não encontrado!';
            }
            }
        