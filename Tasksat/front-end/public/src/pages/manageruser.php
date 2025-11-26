<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Informações do Perfil</h1>
    <?php
    session_start();
    echo "Matrícula: " . $_SESSION['Registro'] . "<br>";
    echo "Nome: " . $_SESSION['Name'] . "<br>";
    echo "CPF: " . $_SESSION['Cpf'] . "<br>";
    echo "Telefone: " . $_SESSION['Numero'] . "<br>";
    echo "Data de Nascimento: " . $_SESSION['DataNasc'] . "<br>";
    echo "Sexo: " . $_SESSION['Sexo'] . "<br>";
    echo "Departamento: " . $_SESSION['Departamento'] . "<br>";
    echo "Criado Quando: " . $_SESSION['CriadoQuando'] . "<br>";
    ?>
</body>
</html>