<?php
include "../conexao.php";

$id = $_GET['id'] ?? '';

$sql = "SELECT * FROM up_boletim_Hidro WHERE id = $id";
$result = $conn->query($sql);
$dados = $result->fetch_assoc();

if (isset($_POST['atualizar'])) {
    $data_upload = $_POST['data_upload'];
    $arquivo = $_FILES['arquivo']['name'];

if (!empty($arquivo)) {
    // Caminho da pasta
    $uploadDir = '../up_boletim_Hidro/';

    // Arquivo antigo
    $arquivoAntigo = $uploadDir . $dados['arquivo'];

    // Se existir arquivo antigo, remove
    if (file_exists($arquivoAntigo) && is_file($arquivoAntigo)) {
        unlink($arquivoAntigo);
    }

    // Move o novo arquivo
    move_uploaded_file($_FILES['arquivo']['tmp_name'], $uploadDir . $arquivo);

    // Atualiza no banco
    $sqlUpdate = "UPDATE up_boletim_Hidro SET data_upload='$data_upload', arquivo='$arquivo' WHERE id=$id";
} else {
    $sqlUpdate = "UPDATE up_boletim_Hidro SET data_upload='$data_upload' WHERE id=$id";
}

    if ($conn->query($sqlUpdate) === TRUE) {
        header("Location: listagemBoletimHidrologico.php"); // Voltar pra listagem
        exit();
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<title>Editar boletim SPI</title>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta name="description" content="Boletins diários e mensais de pluviais e estiagem de monitoramento da rede hidrologica do Estado de São Paulo.">

  <title>Sala de Situação do estado de São paulo - Sobre</title>
  <meta content="radares, satelites e redes telemétricos" name="description">
  <meta content="" name="keywords">
  <link href="../assets/img/logo/logo.png" rel="icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="crossorigin="anonymous" referrerpolicy="no-referrer" />
   <link href="../assets/css/style.css" rel="stylesheet">
 <link href="../assets/cdn/aos/aos.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/cdn/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/cdn/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/cdn/swiper/swiper-bundle.min.css" rel="stylesheet">


  <script>
    
    window.addEventListener("scroll", function() {
        var menu = document.getElementById("header");
        if (window.scrollY > 50) {
            menu.classList.add("scrolled");
        } else {
            menu.classList.remove("scrolled");
        }
    });
    
    </script>
    
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
            <h2><strong>Editar Boletim SPI</strong></h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Data de Publicação:</label>
                        <input type="date" name="data_upload" class="form-control" value="<?= $dados['data_upload'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Arquivo (PDF):</label>
                        <input type="file" name="arquivo" class="form-control">
                        <p>Arquivo atual: <a href="../up_boletim_Hidro/<?= $dados['arquivo'] ?>" target="_blank"><?= $dados['arquivo'] ?></a></p>
                    </div>
                    <button type="submit" name="atualizar" class="btn btn-success">Salvar Alterações</button>
                    <a href="listagemBoletimHidrologico.php" class="btn btn-secondary">Voltar</a>
                </form>
        </div>
    </div>
</section>
              <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js" integrity="sha512-BwHfrr4c9kmRkLw6iXFdzcdWV/PGkVgiIyIWLLlTSXzWQzxuSg4DiQUCpauz/EWjgk5TYQqX/kvn9pG1NpYfqg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/dist/boxicons.min.js" integrity="sha512-VTptlAlSWKaYE3DbrmwNYTzZg1zO6CtoGxplxlHxObgfLiCcRYDBqzTUWE/0ANUmyfYR7R227ZirV/I4rQsPNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/js/glightbox.min.js" integrity="sha512-RBWI5Qf647bcVhqbEnRoL4KuUT+Liz+oG5jtF+HP05Oa5088M9G0GxG0uoHR9cyq35VbjahcI+Hd1xwY8E1/Kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js" integrity="sha512-Zq2BOxyhvnRFXu0+WE6ojpZLOU2jdnqbrM1hmVdGzyeCa1DgM3X5Q4A/Is9xA1IkbUeDd7755dNNI/PzSf2Pew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" integrity="sha512-Ysw1DcK1P+uYLqprEAzNQJP+J4hTx4t/3X2nbVwszao8wD+9afLjBQYjz7Uk4ADP+Er++mJoScI42ueGtQOzEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>
</html>
