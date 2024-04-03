<?php
//Conexao ao banco
	if (!mysqli_connect("localhost","root","","sis_proj")) {
		echo "Erro ao conectar com o servidor";
	}
	else {
		$con = mysqli_connect("localhost","root","","sis_proj");
	}
	
?>
