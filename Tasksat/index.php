<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

// Função de escape
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Buscar categorias
$queryCategoria = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$resultCategoria = $con->query($queryCategoria);

// Buscar áreas
$queryArea = "SELECT id, nome FROM area ORDER BY nome ASC";
$resultArea = $con->query($queryArea);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta aí!</title> 

    <!-- BOOTSTRAP -->
    <script src="bootstrap5/jquery-3.5.1.min.js" defer></script>
    <link rel="stylesheet" href="bootstrap5/bootstrap.min.css">
    <script src="bootstrap5/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="bootstrap5/bootstrap-icons.css">

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'verde': { DEFAULT: '#669f2a', 50: '#E0FFC0', 70: '#A2EC16', 100: '#b0de7f', 150: '#A9CC68', 200: '#93d411', 250: '#4F6E15', 300: '#467614' },
              'vermelho': { DEFAULT: '#d64919', 100: '#e66d42', 150: '#E72C22', 200: '#903923', 250: '#870B00' }
            }
          }
        }
      }
    </script>
</head>
<body>

<?php
// NAVBAR
$navbarPath = $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/front-end/public/src/components/navbar/';
switch ($_SESSION['Cargo'] ?? 'guest') {
    case 'admin':      include $navbarPath . 'navbarAdmin.php'; break;
    case 'supervisor': include $navbarPath . 'navbarSupervisor.php'; break;
    case 'normal':     include $navbarPath . 'navbarLogged.php'; break;
    default:           include $navbarPath . 'navbarGuest.php'; break;
}

// Mensagem de cadastro com sucesso
if (isset($_SESSION['cadastro_sucesso'])) {
    echo "<div id='toastSuccess' class='fixed top-4 left-1/2 transform -translate-x-1/2 bg-verde-150 text-white p-3 rounded-md shadow-md shadow-verde-250 z-50'>
            {$_SESSION['cadastro_sucesso']}
          </div>";
    unset($_SESSION['cadastro_sucesso']); // limpa a mensagem
}
?>

<form method="POST" 
      action="/Tasksat/back-end/actions/occurence.action.php" 
      enctype="multipart/form-data" 
      class="grid min-h-screen w-full bg-gradient-to-t from-verde-150 pt-10">

  <div class="p-2 grid sm:grid-cols-3 m-8 mt-0 size-fit sm:w-3/4 justify-self-center">

    <!-- FOTO -->
    <div class="grid p-3 gap-5 bg-verde-150 rounded-2xl max-sm:rounded-b-none sm:rounded-r-none place-content-center">
        <div class="w-64 h-64 border-2 border-verde-250 bg-white rounded-md bg-center bg-no-repeat bg-contain" id="Imagem"></div>
        <input id="Foto" type="file" name="Foto" onchange="upload()" accept="image/*" class="uploadFoto" style="display:none;" />
        <label for="Foto" class="font-bold bg-verde p-3 shadow-black shadow-sm rounded-lg hover:bg-verde-150 hover:shadow-inner hover:shadow-black w-fit justify-self-center">
            Selecionar arquivo
        </label>
        <script>
            function upload() {
                const fileInput = document.querySelector('.uploadFoto');
                const file = fileInput.files[0];
                if (!file.type.includes('image')) return alert('Selecione uma imagem');
                if (file.size > 10000000) return alert('Selecione até 10MB');

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    document.querySelector('#Imagem').style.backgroundImage = `url(${e.target.result})`;
                }
            }
        </script>
    </div>

    <!-- FORM CAMPOS -->
    <div class="grid bg-verde p-3 place-content-start rounded-2xl max-sm:rounded-t-none sm:rounded-l-none sm:col-span-2 gap-3">
        <div>
            <label for="Categoria" class="ml-1 font-semibold">Categoria</label>
            <select name="Categoria" id="Categoria" class="rounded-md p-2 w-full shadow-md bg-verde-50" required>
                <option value=""></option>
                <option value="1">Elétrico</option>
                <option value="2">Hidráulico</option>
                <option value="3">Estrutural</option>
                <option value="4">Mobiliário</option>
                <option value="5">Equipamentos</option>
                <option value="6">Outro</option>
            </select>
        </div>

        <div>
            <label for="Area" class="ml-1 font-semibold">Área</label>
            <select name="Area" id="Area" class="bg-verde-50 shadow-md rounded-md p-2 w-full" required>
                <option value=""></option>
                <?php
                if ($resultArea && $resultArea->num_rows > 0) {
                    while ($row = $resultArea->fetch_assoc()) {
                        echo '<option value="' . esc($row['id']) . '">' . esc($row['nome']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div>
            <label for="Local" class="ml-1 font-semibold">Local</label>
            <select name="Local" id="Local" class="bg-verde-50 shadow-md rounded-md p-2 w-full" required>
                <option value=""></option>
            </select>
        </div>

        <div>
            <label for="Descricao" class="ml-1 font-semibold">Descrição</label>
            <textarea name="Descricao" id="Descricao" class="bg-verde-50 shadow-md rounded-md p-2 w-full font-normal h-42"
                      style="resize:none;" placeholder="Insira uma breve descrição do problema" required></textarea>
        </div>

        <div class="font-semibold">
            <p>Urgência</p>
            <label><input type="radio" name="Urgencia" value="Leve"> Leve</label><br>
            <label><input type="radio" name="Urgencia" value="Mediana"> Moderada</label><br>
            <label><input type="radio" name="Urgencia" value="Extrema"> Extrema</label>
        </div>

        <?= esc($_SESSION['Name'] ?? '') ?>

        <button type="submit" class="bg-verde-300 p-3 font-bold rounded-lg w-fit justify-self-center shadow-black shadow-sm hover:shadow-inner hover:shadow-black hover:bg-verde" 
                name="CadastroOccurence">Abrir Chamado</button>
    </div>
  </div>
</form>

<script>
document.getElementById("Area").addEventListener("change", function () {
    const areaId = this.value;
    fetch('/Tasksat/back-end/actions/getLocaisByArea.php?area=' + areaId)
        .then(response => response.json())
        .then(data => {
            let localSelect = document.getElementById("Local");
            localSelect.innerHTML = '<option value="">Selecione o local</option>';
            data.forEach(local => {
                localSelect.innerHTML += `<option value="${local.id}">${local.nome}</option>`;
            });
        });
});

// Faz o toast desaparecer após 3 segundos
window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toastSuccess');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = "opacity 0.5s";
            toast.style.opacity = "0";
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
</script>

</body>
</html>
