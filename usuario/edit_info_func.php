<?php

include_once("../php/conexao.php");
mysqli_set_charset($con,'utf8');
ini_set('default_charset','UTF-8');
session_start();
if (isset($_SESSION["login"])){
    if($_SESSION["id_nivel"] == 2 ||$_SESSION["id_nivel"] == 3) {
        include('../layout/cabecalho_user.php');
        ?>

        <div class="container">
            <?php
            $id_func = $_SESSION["id_func"];
            $query = mysqli_query($con,"select id, login,nome from funcionario where id = '$id_func'");
            $result = mysqli_fetch_array($query);
            $id = $result['id'];
            $login = $result['login'];
            $nome = $result['nome'];

            ?>


        <div id="div_edit_func"  class="form-group">
            <input type="hidden" class="form-control" value="<?php echo $id; ?>" id="id_func_edit">
            <div class="form-group">
                <label for="login_func">Login </label>
                <input type="text" class="form-control" value="<?php echo $login; ?>" id="login_func_edit">
            </div>
            <div class="form-group">
                <label for="senha_func">Senha</label>
                <input type="password" class="form-control" value="" id="senha_func_edit" placeholder="Deixe em branco caso queira manter a senha já cadastrada" >
            </div>
            <div class="form-group">
                <label for="senha_func_confirm">Confirmação de senha</label>
                <input type="password" class="form-control" value="" id="senha_func_confirm_edit" placeholder="Deixe em branco caso queira manter a senha já cadastrada">
            </div>
            <div class="form-group">
                <label for="start_date">Nome</label>
                <input type="text" class="form-control" value="<?php echo $nome; ?>" id="nome_func_edit" >
            </div>

            <div class="form-group">
                <button type="button" id="btn_edit_func" class="btn btn-lg btn-success">Atualizar</button>
            </div>


        </div>
        </div>

        <?php
        include('../layout/rodape.php');
    }else {
        header('Location: ../login.php');
    }
}else {
    header('Location: ../login.php');
}

?>