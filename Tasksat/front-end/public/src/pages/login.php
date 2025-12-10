<?php
    ob_start(); // Inicia o buffer de saída para evitar problemas com headers

    // CORREÇÃO DO INCLUDE
    include($_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php');

    session_start();

    // LÓGICA DE LOGIN INTEGRADA NA PÁGINA
    $error_message = null;
    if (isset($_POST['LoginUser'])) {
        $email = $_POST['Email'];
        $senha = $_POST['Senha'];

        $sql = "SELECT matricula, nome, cpf, telefone, data_nascimento, sexo, departamento, senha, criado_em, tipo FROM usuarios WHERE email = '$email'";
        $result = $con->query($sql);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($senha, $row['senha'])) {
                $_SESSION['Registro'] = $row['matricula'];
                $_SESSION['Name'] = $row['nome'];
                $_SESSION['Cpf'] = $row['cpf'];
                $_SESSION['Numero'] = $row['telefone'];
                $_SESSION['DataNasc'] = $row['data_nascimento'];
                $_SESSION['Sexo'] = $row['sexo'];
                $_SESSION['Departamento'] = $row['departamento'];
                $_SESSION['CriadoQuando'] = $row['criado_em'];
                $_SESSION['Cargo'] = $row['tipo'];
                header("Location:/Tasksat/index.php");
                exit;
            } else {
                $error_message = 'Senha incorreta!';
            }
        } else {
            $error_message = 'Email não encontrado!';
        }
    }

    // CORREÇÃO DE TODAS AS NAVBARS
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta aí!</title>
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
        /* Animação suave para o popup aparecer e desaparecer */
        #error-popup {
            transition: opacity 0.5s ease-in-out;
        }
        #error-popup.fade-out {
            opacity: 0;
        }
    </style>
</head>
<body>
    <!-- POPUP DE ERRO, SE HOUVER -->
    <?php if ($error_message): ?>
        <div id="error-popup" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 text-center max-w-sm w-full">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <script>
            setTimeout(function() {
                var popup = document.getElementById('error-popup');
                if (popup) {
                    popup.classList.add('fade-out');
                    setTimeout(function() {
                        popup.style.display = 'none';
                    }, 500); // Tempo para a transição de fade-out
                }
            }, 2000);
        </script>
    <?php endif; ?>

    <div class="grid h-screen w-full bg-gradient-to-tr from-verde-50 to-verde-50 via-verde-150 pt-10">
        <div class="p-2 grid m-8 mt-0 size-fit justify-self-center w-4/5 sm:w-2/5">
            <div class="grid p-3 gap-5 bg-white rounded-2xl place-content-center pt-7">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                    <input type="email" name="Email" id="Email" class="border-b-2 border-verde-250" placeholder="E-mail" required>
                    <br><br>

                    <input type="password" name="Senha" id="Senha" class="border-b-2 border-verde-250" placeholder="Senha" required>
                    <br><br>

                    <button type="submit" class="bg-verde-150 p-3 w-full text-center font-bold rounded-lg shadow-black shadow-sm hover:shadow-inner hover:shadow-black" name="LoginUser">
                        Log-in
                    </button>

                    <br><br>
                    <div class="text-center">
                        <a href="#" class="text-verde-250">Criar conta</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
