<?php

  session_start();

  if (isset($_SESSION["login"])){
      
      if ($_SESSION["id_nivel"] == 1){
          header('Location: gerente/index.php');
      }else if($_SESSION["id_nivel"] == 2 || $_SESSION["id_nivel"] == 3){
          header('Location: usuario/index.php');
      }else {
          header('Location: login.php');
      }

  }else { 

  	header('Location: login.php');
    exit;
  }

?>