<?php

$host = 'localhost';
$bd = 'sakila-01';
$user = 'root';
$password = '';

$con = mysqli_connect($host, $user, $password);
mysqli_select_db($con, $bd);

if ($con) {
    echo '';
} else {
    echo 'Sem conexão!';
}


