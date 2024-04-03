<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-theme.css">
    


    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/jquery-ui.structure.css">
    <link rel="stylesheet" href="../css/jquery-ui.theme.css">
    <link rel="stylesheet" href="../css/datatables.css">
    <link rel="stylesheet" href="../css/loading.min.css">
    

    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/bootstrap.js"></script>
    


    <script src="../js/jquery-ui.js"></script>
    <script src="../js/jquery.maskedinput.js"></script>
    <script src="../js/loading.min.js"></script>
    <script src="../js/datatables.js"></script>
    
    <script src="../js/login.js"></script>
    <script src="../usuario/js/user.js"></script>
    <script src="../usuario/js/ajax.js"></script>

    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/select2.min.js"></script>
    

</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">Registrar horas</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Férias / Atestados / Day Off<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../usuario/ferias_atestados.php">Cadastrar</a></li>
                        <li><a href="../usuario/edit_ferias_atestados.php">Editar / Excluir</a></li>
                    </ul>
                </li>

                <?php 
                    //session_start();
                    $id_nivel = $_SESSION["id_nivel"];
                    if($id_nivel == 3){


                ?>   
                
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Funcionário<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../usuario/cadastro_func.php">Liberar funcionário fazer horas extras</a></li>                        
                    </ul>
                </li>

                <?php
                    }

                
                ?>


                <li><a href="../usuario/relatorio.php">Relatórios</a></li>

            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user"
                                                                        aria-hidden="true"></span> <span
                            class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../usuario/edit_info_func.php">Editar informações</a></li>
                        <li><a href="../php/logoff.php">Sair</a></li>

                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">
                    <?php
                        include_once("../php/conexao.php");
                        mysqli_set_charset($con,'utf8');
                        ini_set('default_charset','UTF-8');
                        //session_start();
                        $id_func = $_SESSION["id_func"];
                        $query = ("Select * from funcionario where id = '".$id_func."'")or die(mysqli_error());
                        $query_exec = mysqli_query($con,$query);
                        while ($resultado = mysqli_fetch_array($query_exec)){
                            echo "Bem Vindo ".$resultado['nome'];
                        }

                    ?></a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->


</nav>