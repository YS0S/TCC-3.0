<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Tasksat/back-end/src/config/config.php';

if (!isset($_GET['area'])) {
    echo json_encode([]);
    exit;
}

$areaId = intval($_GET['area']);

$query = "SELECT id, nome FROM local WHERE area_id = $areaId ORDER BY nome ASC";
$result = $con->query($query);

$locais = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $locais[] = $row;
    }
}

echo json_encode($locais);
