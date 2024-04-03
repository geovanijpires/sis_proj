<?php

session_start();

 if (isset($_SESSION["login"])){
	 if(session_destroy()){
		 header('Location: ../login.php');
	 }else {
		 echo 'Erro ao fazer logoff, contate o administrador';
	 }

} else {

	 header('Location: ../login.php');
}


?>