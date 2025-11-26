<?php

$result = $con->query("SELECT * FROM chamados");

if ($result && $result->num_rows > 0) {
    echo "<div class='Container-Central'>";
    while($linha = $result->fetch_assoc()) {

        $foto = !empty($linha["foto"]) ? "front-end/storage/upload/" . $linha["foto"] : "front-end/storage/upload/sem-foto.png";
        $urgencia = !empty($linha['urgencia_definitiva']) ? $linha['urgencia_definitiva'] : $linha['urgencia_user'];
        $hora_fechamento = !empty($linha['hora_fechamento']) ? $linha['hora_fechamento'] : "Em andamento ainda";

        echo "<div class='Card'>";
            echo "<div class='Card-Foto'>";
                echo "<img src='{$foto}' alt='" . htmlspecialchars($linha['descricao']) . "'>";
            echo "</div>";

            echo "<div class='Card-Info'>";
                echo "<p><b>Descrição:</b> {$linha['descricao']}</p>";
                echo "<p><b>Local:</b> {$linha['local']}</p>";
                echo "<p><b>Status:</b> <span class='Status {$linha['status']}'>{$linha['status']}</span></p>";
                echo "<p><b>Urgência:</b> {$urgencia}</p>";
                echo "<p><b>Relatado:</b> {$linha['enviado_por']}</p>";
                echo "<p><b>Verificado por:</b> {$linha['verificado_por']}</p>";
                echo "<p><b>Resposta:</b> {$linha['resposta']}</p>";
                echo "<p><b>Hora de abertura:</b> {$linha['hora_abertura']}</p>";
                echo "<p><b>Hora de fechamento:</b> {$hora_fechamento}</p>";
                echo "<p><b>Última atualização:</b> {$linha['data_atualizacao']}</p>";
            echo "</div>";

            echo "<div class='Card-Acoes'>";
                echo "<a href='/Tasksat/front-end/public/src/pages/acompanhar.php?id={$linha['id']}'><button>Acompanhar</button></a>";
            echo "</div>";

        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p>Nenhum chamado encontrado.</p>";
}
?>

<style>
body {
    background-color: #f5f5f5;
    font-family: Arial, sans-serif;
}

.Container-Central {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
}

.Card {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 15px;
    width: 320px; /* tamanho compacto */
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s;
}

.Card-Foto img {
    max-width: 100%;
    border-radius: 6px;
    margin-bottom: 10px;
}

.Card-Info p {
    margin: 3px 0;
    font-size: 14px;
}

.Status {
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 4px;
    color: #fff;
    font-size: 13px;
}

.Status.Concluído { background-color: #27ae60; }
.Status.Cancelado { background-color: #c0392b; }

.Card-Acoes {
    margin-top: 10px;
    display: flex;
    justify-content: center;
}

.Card-Acoes button {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    background-color: #3498db;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: filter 0.2s;
}

.Card-Acoes button:hover {
    filter: brightness(1.1);
}
</style>
