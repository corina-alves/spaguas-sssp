<?php
session_start();
include "../conexao.php";

if ($_POST) {
    $usuario = $_POST['usuario'];
    $senha = sha1($_POST['senha']);

    $stmt = $conn->prepare("SELECT * FROM admin WHERE usuario = ? AND senha = ?");
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $stmt->store_result(); // necessário para usar num_rows

    if ($stmt->num_rows > 0) {
        $_SESSION['logado'] = true;
        header("Location: painel.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/cdn/aos/aos.css" rel="stylesheet">
  <link href="assets/cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/cdn/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/cdn/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/cdn/swiper/swiper-bundle.min.css" rel="stylesheet">
 
  
</head>
<body>

<div class="container mt-5">
  <h1 class="text-center mb-4">Área Administrativa</h1>
  <p class="text-center">Faça login para acessar o painel administrativo.</p>
<form method="post">
    <!--?php if (isset($erro)) echo "<p style='color:red'>$erro</p>"; ?-->
    <div class="col-md-4 offset-md-4 telaLogin">
  <div class="mb-3">
    <label for="usuario" class="form-label">Usuário</label>
    <input type="text" name="usuario" placeholder="Usuário" class="form-control" id="usuario" aria-describedby="usuario" required>
  </div>
  <div class="mb-3">
    <label for="senha" class="form-label">Senha</label>
    <input type="password" name="senha" placeholder="Senha" class="form-control" id="password" required>
  </div>

  <button type="submit" class="btn btn-primary">Entrar</button>
  <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
  </div>
</form>
</div>


    <!-- Scripts -->
  <script src="assets/cdn/aos/aos.js"></script>
  <script src="assets/cdn/glightbox/js/glightbox.min.js"></script>
  <script src="assets/cdn/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/cdn/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>
</html>
