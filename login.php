<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.css">
     <link rel="stylesheet" href="css/login_page.css">
  </head>
  <body>

  <div class="container">
      <div class="col-md-4 col-md-offset-4">
          <section>
              <div class="panel panel-default top caja">
                  <div class="panel-body">
                        <div class="text-center">
                          <img src="img/logo.jpg" class="img-fluid mx-auto" width="100px">
                        </div>
                      <h3 class="text-center">SiS-Proj</h3>
                      <h4 class="text-center">Sistema para Arquitetos de Gestão de Projetos</h4>
                       </br>
                      <form id="form_login" action="POST">
                          <div class="input-group input-group-lg">
                              <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user" aria-hidden="true"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></i></span>
                              <input type="text" id="input_login" class="form-control" placeholder="Digite seu login" aria-describedby="sizing-addon1" required>
                          </div>
                          <br>
                          <div class="input-group input-group-lg">
                              <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key" aria-hidden="true"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></i></span>
                              <input type="password" id="input_senha" class="form-control" placeholder="Digite sua senha" aria-describedby="sizing-addon1" required>
                          </div>
                          <br>
                          <button type="submit" id="btn_enviar" class="btn btn-lg btn-primary btn-block">Acessar</button>
                        </br>
                      </form>
                  </div>
              </div>
          </section>
      </div>

  </div>



  <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
   <script src="js/login.js"></script>
  </body>
</html>


