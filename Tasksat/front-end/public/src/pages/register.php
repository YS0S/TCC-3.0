<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TaskSat</title>
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
</head>
<body>
<?php 
    include($_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php');
    session_start();
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

<div class="flex min-w-full min-h-screen bg-gradient-to-tr from-verde-50 to-verde-50 via-verde-150 justify-center pt-10">
    <div class="grid p-6 bg-white size-fit rounded-2xl shadow-md shadow-verde-250 gap-3">
        <form id="cadastro" method="POST" action="/Tasksat/back-end/actions/register.action.php" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- MATRÍCULA -->
            <div>
                <input type="text" name="Matricula" id="Matricula" class="border-b-2 border-verde-250 w-full" placeholder="Matrícula" required>
            </div>

            <!-- NOME -->
            <div>
                <input type="text" name="Nome" id="Nome" class="border-b-2 border-verde-250 w-full" placeholder="Nome" required>
            </div>

            <!-- CPF -->
            <div>
                <input type="text" name="CPF" id="CPF" class="border-b-2 border-verde-250 w-full" placeholder="CPF (somente números)" required>
            </div>

            <!-- TELEFONE -->
            <div>
                <input type="tel" name="Telefone" id="Telefone" class="border-b-2 border-verde-250 w-full" placeholder="Telefone" required>
            </div>

            <!-- EMAIL -->
            <div>
                <input type="email" name="Email" id="Email" class="border-b-2 border-verde-250 w-full" placeholder="Email" required>
            </div>

            <!-- DATA DE NASCIMENTO -->
            <div>
                <input type="date" name="DataNascimento" id="DataNascimento" class="border-b-2 border-verde-250 w-full" required>
            </div>

            <!-- SEXO -->
            <div>
                <select name="sexo" id="sexo" class="border-b-2 border-verde-250 w-full" required>
                    <option value="">Sexo</option>
                    <option value="M">Masculino</option>
                    <option value="F">Feminino</option>
                    <option value="O">Outro</option>
                </select>
            </div>

            <!-- DEPARTAMENTO -->
            <div>
                <input type="text" name="Departamento" id="Departamento" class="border-b-2 border-verde-250 w-full" placeholder="Departamento" required>
            </div>

            <!-- SENHA -->
            <div>
                <input type="password" name="Senha" id="Senha" class="border-b-2 border-verde-250 w-full" placeholder="Senha" required>
            </div>

            <!-- CONFIRMAR SENHA -->
            <div>
                <input type="password" id="CSenha" class="border-b-2 border-verde-250 w-full" placeholder="Confirmar senha" required>
            </div>

            <!-- BOTÃO -->
            <div class="col-span-2 place-content-center">
                <button type="submit" class="bg-verde-150 p-3 text-center font-bold rounded-lg shadow-black shadow-sm hover:shadow-inner hover:shadow-black w-full" name="CadastroUser">
                    Criar conta
                </button>
                <p class="text-verde-250 mt-2 text-center">
                    A conta é somente para funcionários da prefeitura do campus
                </p>
            </div>
        </form>
    </div>
</div>

<script>
    // Máscara de CPF
    document.getElementById('CPF').addEventListener('input', function(e) {
        var value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    });

    // Confirmação de senha
    const password = document.getElementById("Senha");
    const confirm_password = document.getElementById("CSenha");
    const form = document.getElementById("cadastro");

    function validatePassword() {
        if(password.value !== confirm_password.value) {
            confirm_password.setCustomValidity("As senhas não coincidem");
        } else {
            confirm_password.setCustomValidity('');
        }
    }

    password.addEventListener("change", validatePassword);
    confirm_password.addEventListener("keyup", validatePassword);

    form.addEventListener("submit", function(e) {
        if(password.value !== confirm_password.value) {
            e.preventDefault();
            confirm_password.reportValidity();
        }
    });
</script>

</body>
</html>
