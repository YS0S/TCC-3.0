<?php 
session_start(); // necessário para usar $_SESSION

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['CadastroUser'])) {

        if (empty($_POST['Senha'])) {
            die("Erro: Você não pode se registrar sem uma senha!");
        }

        $inscricao    = $_POST['Matricula'] ?? '';
        $name         = $_POST['Nome'] ?? '';
        $cpf          = $_POST['CPF'] ?? '';
        $number       = $_POST['Telefone'] ?? '';
        $email        = $_POST['Email'] ?? '';
        $nascimento   = $_POST['DataNascimento'] ?? '';
        $sexo         = $_POST['sexo'] ?? '';
        $departamento = $_POST['Departamento'] ?? '';
        $date         = date("Y-m-d H:i:s");

        $senha        = password_hash($_POST['Senha'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios 
                (matricula, nome, cpf, telefone, email, data_nascimento, sexo, departamento, senha, criado_em) 
                VALUES 
                ('$inscricao','$name','$cpf','$number','$email','$nascimento','$sexo','$departamento','$senha','$date')";

        if ($con->query($sql) === TRUE) {

            // Tenta enviar e-mail (não bloqueia cadastro em caso de erro)
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'comunicacao@consorciocantareira.info';
                $mail->Password   = 'Sp18C78414!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('comunicacao@consorciocantareira.info', 'TaskSat');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Sua conta foi criada - TaskSat';
                $mail->Body    = "
                    <h2>Bem-vindo ao TaskSat, $name!</h2>
                    <p>Sua conta foi criada com sucesso.</p>
                    <p><strong>Matrícula:</strong> $inscricao</p>
                    <p><strong>Email:</strong> $email</p>
                    <br>
                    <p>Agora você já pode acessar o sistema!</p>
                    <br>
                    <small>TaskSat Automação Interna</small>
                ";

                $mail->send();

            } catch (Exception $e) {
                error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
            }

            // Define a mensagem de sucesso via sessão
            $_SESSION['cadastro_sucesso'] = "Conta cadastrada com sucesso!";
            
            // Redireciona para index.php
            header("Location: /Tasksat/index.php");
            exit();

        } else {
            die("Erro ao cadastrar! " . $con->error);
        }
    }
}
?>
