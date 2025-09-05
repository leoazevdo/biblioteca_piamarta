<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistema Bibliotecário</title>
    <!-- Bootstrap -->
    <link href="arquivos/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="arquivos/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="arquivos/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="arquivos/vendors/animate.css/animate.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="arquivos/build/css/custom.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link href="arquivos/scripts/sweetalert/sweetalert2.min.css" rel="stylesheet">
     <!-- Css Piamarta -->
    <link href="arquivos/build/css/meu-tema.css" rel="stylesheet">
  </head>

  <body class="login">
  <div class="login_wrapper">
      <section class="login_content">
        <form id="loginForm">
          <h1 class="text-center">Biblioteca</h1>
          <h3 class="text-center">Piamarta Montese</h3>
          <div>
            <input type="text" id="login" class="form-control" placeholder="Login" required="">
          </div>
          <button type="submit" class="btn btn-primary btn-block submit entrar">Entrar</button>
        </form>
      </section>
    </div>
  </div>
</body>
   
    <!-- jQuery -->
    <script src="arquivos/scripts/jquery-3.4.1.js"></script>
    <!-- SweetAlert JS -->
    <script src="arquivos/scripts/sweetalert/sweetalert.js"></script>

    <script>
    $(function(){
        $(".entrar").click(function(e){
            e.preventDefault();
            var btn = $(this);
            var login = $("#login").val();
            
            

            $.ajax({
                url: 'data/verifica.php',
                type: 'POST',
                data: { login: login },
                success: function(response) {
                  //alert(response)  
                    
                    if(response == "error") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message || 'Login incorreto!'
                        });
                        btn.html('ACESSAR');
                       
                    } else {
                        window.location.href = response;
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Ocorreu um erro. Tente novamente.'
                    });
                    btn.html('ACESSAR');
                }
            });
        });
    })

  </script>
    


  </body>
</html>
