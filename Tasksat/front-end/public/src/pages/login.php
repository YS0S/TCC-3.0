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
</head>
<body>
    <?php 
        // CORREÇÃO DO INCLUDE
        include($_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php');

        session_start();

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

    <div class="grid h-screen w-full bg-gradient-to-tr from-verde-50 to-verde-50 via-verde-150 pt-10">
        <div class="p-2 grid m-8 mt-0 size-fit justify-self-center w-4/5 sm:w-2/5">
            <div class="grid p-3 gap-5 bg-white rounded-2xl place-content-center pt-7">
                <form method="POST" action="/Tasksat/back-end/actions/login.action.php" enctype="multipart/form-data">
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
