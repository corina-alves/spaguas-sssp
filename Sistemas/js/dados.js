document.addEventListener("DOMContentLoaded", () => {
  const conteudo = document.getElementById("conteudo");
  const carregando = document.getElementById("carregando");

  fetch("../api_cache.php")
    .then(r => r.json())
    .then(dados => {
      carregando.style.display = "none";
      conteudo.style.display = "block";

      // Exemplo: listar os sistemas com volume útil
      conteudo.innerHTML = dados
        .map(item => `
          <div class="card">
            <strong>${item.Nome}</strong><br>
            Volume útil: ${item.VolUtil}%<br>
            Pluviometria: ${item.PluviometriaDia} mm
          </div>
        `)
        .join("");
    })
    .catch(() => {
      carregando.innerText = "Erro ao carregar os dados. Tente novamente mais tarde.";
    });
});
