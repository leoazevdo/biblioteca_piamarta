<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistema Bibliotecário</title>
    <!-- Bootstrap -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
  <style>
    /* garante 100% de altura para os elementos pais */
    html, body { height: 100%; margin: 0; }

    /* faz a linha ocupar a viewport inteira */
    .full-height { min-height: 100vh; }

    /* coluna esquerda: imagem como background cobrindo tudo */
    .bg-image {
      min-height: 100vh;
      background-image: url('./img/capafront.png'); /* troque aqui */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    /* coluna do login: centraliza vertical e horizontalmente */
    .login-column {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* largura do formulário */
    .login-wrapper {
       width: 90%; 
       max-width: 400px; 
       margin-bottom: 25px;
       
      }
       

    .loginbutton {
      width: 100%;
      
    }

    .mb-3 {
      margin-bottom: 20px;
    }

    .titulo-login {
  font-weight: 700;           /* negrito elegante */
  font-size: 4rem;            /* tamanho equilibrado */
  color: #2c3e50;             /* azul petróleo sofisticado */
  margin-bottom: 0rem;      /* bem próximo do subtítulo */
}

.subtitulo-login {
  font-weight: 400;           /* mais leve */
  font-size: 2rem;
  color: #6c757d;             /* cinza moderno (bootstrap gray) */
  margin-bottom: 1.5rem;      /* espaço antes dos inputs */
}

  </style>
</head>

<body>

  <div class="container-fluid p-0">
    <div class="row g-0 full-height">

      <!-- Coluna esquerda: imagem (aparece só em md+) -->
      <div class="col-md-8 d-none d-md-block bg-image"></div>

      <!-- Coluna direita: login (ocupa 100vh e centraliza) -->
      <div class="col-md-4 col-12 login-column">
        <div class="login-wrapper">
          <form id="loginForm">
            <h1 class=" text-center titulo-login mb-1">Biblioteca</h1>
            <h3 class=" text-center subtitulo-login mb-4">Piamarta Montese</h3>

            <div class="mb-3">
              <input type="text" id="login" class="form-control mb-3" placeholder="Login" required>
            </div>

            <button  type="submit" class="btn btn-primary loginbutton entrar">Entrar</button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
