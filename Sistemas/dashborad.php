<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Menu - Monitoramento</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: #f5f5f5;
  text-align: center;
  padding-top: 100px;
}
a {
  display: inline-block;
  background: #0074D9;
  color: #fff;
  padding: 12px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}
a:hover {
  background: #005fa3;
}

/* === Tela de carregamento === */
#telaCarregando {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.9);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #333;
}
.spinner {
  border: 6px solid #ddd;
  border-top: 6px solid #0074D9;
  border-radius: 50%;
  width: 50px; height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
</head>
<body>

<h1>Portal de Monitoramento</h1>
<p><a href="dashboard.php" class="link">Ver Dashboard</a></p>
<p><a href="boletim.php" class="link">Ver Boletim Diário</a></p>

<div id="telaCarregando">
  <div class="spinner"></div>
  <div>Carregando dados...</div>
</div>

<script>
// Seleciona todos os links e adiciona o evento
document.querySelectorAll('.link').forEach(link => {
  link.addEventListener('click', e => {
    document.getElementById('telaCarregando').style.display = 'flex';
  });
});
</script>

</body>
</html>
