<?php
include_once("conexao.php");
//$con = mysqli_connect("localhost","root","","obvio");

$login = $_POST['usuario'];
$senha = $_POST['senha'];

$senha_cript = sha1($senha);

$query = mysqli_query($con, "select login,password from funcionario where login='$login' and password='$senha_cript'")or die(mysqli_error());

$find_user = mysqli_fetch_row($query);

    if ($find_user != 0) {
        $query_info_user = mysqli_query($con, "select * from funcionario where login='$login' and password='$senha_cript'")or die(mysqli_error());
        $resultado = mysqli_fetch_array($query_info_user);

        session_start();
        $_SESSION["login"] = $login;
        $_SESSION["id_func"] = $resultado["id"];
        $_SESSION["horas_diarias"] = $resultado["horas_diarias"];
        $_SESSION["id_nivel"] = $resultado["id_nivel"];
        $_SESSION["hora_extra"] = $resultado["hora_extra"];
        echo $resultado["id_nivel"];

    }else {
        echo 'não encontrou';
    }
?>