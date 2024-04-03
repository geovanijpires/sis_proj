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
    <link rel="stylesheet" href="../css/jquery-ui.multidatespicker.css">
    <link rel="stylesheet" href="../css/loading.min.css">
    <link rel="stylesheet" href="../css/chosen.css">
    <link rel="stylesheet" href="../css/bootstrap-chosen.css">

    <style>
    .custom-combobox {
        position: relative;
        display: inline-block;
    }
    .custom-combobox-toggle {
        position: absolute;
        top: 0;
        bottom: 0;
        margin-left: -1px;
        padding: 0;
    }
    .custom-combobox-input {
        margin: 0;
        padding: 5px 10px;
    }
  </style>

    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/bootstrap.js"></script>
    <script src="../js/bootstrap-autocomplete.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/datatables.js"></script>
    <script src="../js/jquery-ui.multidatespicker.js"></script>
    <script src="../js/loading.min.js"></script>
    <script src="../js/jquery.maskedinput.js"></script>
    <script src="../js/chosen.jquery.js"></script>


    <script src="../js/login.js"></script>
    <script src="../gerente/js/ger.js"></script>
    <script src="../gerente/js/ajax.js"></script>

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
                        <li><a href="../gerente/ferias_atestados.php">Cadastrar</a></li>
                        <li><a href="../gerente/edit_ferias_atestados.php">Editar / Excluir</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Projeto<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../gerente/cadastro_proj.php">Cadastrar / Editar / Excluir / Finalizar / Reativar</a></li>
                        <li><a href="../gerente/cadastro_tipo_proj.php">Cadastrar / Editar / Excluir tipo de projeto</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Etapas<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../gerente/cadastro_etapa.php">Cadastrar / Editar / Excluir etapa</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="../gerente/cadastro_subetapa.php">Cadastrar / Editar / Excluir subetapa</a></li>

                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Horas administrativas<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../gerente/cadastro_adm.php">Cadastrar / Editar / Excluir</a></li>

                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Funcionário<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../gerente/cadastro_func.php">Cadastrar Funcionário</a></li>
                        <li><a href="../gerente/cadastro_cargo.php">Cadastrar Cargo</a></li>
                        <li><a href="../gerente/dias_uteis.php">Definir dias úteis de trabalho</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">Relatórios<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="../gerente/relatorio_proj.php">Projeto / Administrativo</a></li>
                        <li><a href="../gerente/relatorio_func.php">Funcionário</a></li>
                        <li><a href="../gerente/relatorio_media.php">Média por tipo de projeto</a></li>
                        <li><a href="../gerente/relatorio_resumo.php">Resumo dos resultados</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">

                <li class="dropdown">

                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user"
                                                                        aria-hidden="true"></span> <span
                            class="caret"></span></a>
                    <ul class="dropdown-menu">

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
