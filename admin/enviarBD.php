<?php include "auth.php"; include "../conexao.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Área Administrativa</title>
  <link href="../assets/img/logo/logo.png" rel="icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="crossorigin="anonymous" referrerpolicy="no-referrer" />
   <link href="../assets/css/style.css" rel="stylesheet">
 <link href="../assets/cdn/aos/aos.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/cdn/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/cdn/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/cdn/swiper/swiper-bundle.min.css" rel="stylesheet">
</head>
<body>
  <header id="header" class="header  sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo"><img src="../img/logosssp.png"></div>
             <nav id="navbar" class="navbar ">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="painel.php">Painel</a></li>
                    <li><a href="enviarBD.php">Enviar BD</a></li>
              
                    <li><a href="logout.php">SAIR</a></li>
                </ul>
            </nav>
    </div>
  </header>

    <section class="upload">
        <div class="container">
            <div class="col-lg-6 offset-lg-3">
            <h2>Cadastrar <strong>Boletim Diário</strong></h2>
            <form method="post" enctype="multipart/form-data">
                <label for="name" class="form-label">Nome do Boletim Diário</label>
                <input class="form-control" type="text" name="nome" placeholder="Nome do boletim diário" required aria-label="Dar nome para o aquivo">
                <!-- <input type="text" name="nome" placeholder="Nome do boletim" required><br> -->
                <div class="mb-3">
                <label for="formFile" class="form-label">Envio do aquivo em (pdf)</label>
                <input class="form-control" id="formFile" type="file" name="pdf" accept="application/pdf" required>
                </div>
                <!-- <input type="file" name="pdf" accept="application/pdf" required><br> -->
                <!-- <button type="submit">Enviar</button> -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-3">Enviar</button>
                </div>
            </form>
        </div>
</div>
        <!-- <p><a href="logout.php">Sair</a></p> -->
    </section>
    <?php
    if ($_POST) {
        $nome = $_POST['nome'];
        $arquivo = uniqid() . ".pdf";
        $caminho = "../uploads/" . $arquivo;

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $caminho)) {
            $stmt = $conn->prepare("INSERT INTO boletins (nome, arquivo) VALUES (?, ?)");
            $stmt->bind_param("ss", $nome, $arquivo);
            $stmt->execute();
            // echo "<p>Boletim enviado com sucesso!</p>";
             echo "<p style='color:green'>Boletim enviado com sucesso! Redirecionando para a página principal...</p>";
        echo "<script>setTimeout(() => { window.location.href = 'listagemBD.php'; });</script>";
        } else {
            echo "<p style='color:red'>Erro ao enviar o arquivo.</p>";
        }
    }
    ?>
          <!-- Scripts -->
  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js" integrity="sha512-BwHfrr4c9kmRkLw6iXFdzcdWV/PGkVgiIyIWLLlTSXzWQzxuSg4DiQUCpauz/EWjgk5TYQqX/kvn9pG1NpYfqg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/dist/boxicons.min.js" integrity="sha512-VTptlAlSWKaYE3DbrmwNYTzZg1zO6CtoGxplxlHxObgfLiCcRYDBqzTUWE/0ANUmyfYR7R227ZirV/I4rQsPNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/js/glightbox.min.js" integrity="sha512-RBWI5Qf647bcVhqbEnRoL4KuUT+Liz+oG5jtF+HP05Oa5088M9G0GxG0uoHR9cyq35VbjahcI+Hd1xwY8E1/Kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js" integrity="sha512-Zq2BOxyhvnRFXu0+WE6ojpZLOU2jdnqbrM1hmVdGzyeCa1DgM3X5Q4A/Is9xA1IkbUeDd7755dNNI/PzSf2Pew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" integrity="sha512-Ysw1DcK1P+uYLqprEAzNQJP+J4hTx4t/3X2nbVwszao8wD+9afLjBQYjz7Uk4ADP+Er++mJoScI42ueGtQOzEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
  crossorigin="anonymous"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
