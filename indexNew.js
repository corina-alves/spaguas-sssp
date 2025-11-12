async function carregarDados() {
  try {
    const resp = await fetch(apiUrl, {
      headers: {
        "x-requested-with": "XMLHttpRequest",
        "origin": window.location.origin
      }
    });

    if (!resp.ok) {
      const txt = await resp.text();
      throw new Error(`Erro ao carregar API: HTTP ${resp.status} ${resp.statusText} — ${txt}`);
    }

    const dados = await resp.json();

    // alguns endpoints retornam {results: [...]}, outros já retornam array
    const lista = Array.isArray(dados) ? dados : dados.results;

    if (!lista || !Array.isArray(lista)) {
      throw new Error("Formato inesperado da API, não veio lista de medições.");
    }

    // contadores
    let forte = 0, moderada = 0, fraca = 0, sem = 0;

    const tabela = document.getElementById("tabelaChuva");
    tabela.innerHTML = "";

    lista.forEach(item => {
      const valor = item.value ? parseFloat(item.value) : 0;
      const municipio = item.station?.municipality || "-";
      const estacao = item.station?.name || "-";
      const dataHora = item.measurement_date || "-";

      // classifica chuva
      if (valor >= 50) forte++;
      else if (valor >= 20) moderada++;
      else if (valor > 0) fraca++;
      else sem++;

      // monta tabela
      const row = `
        <tr>
          <td>${municipio}</td>
          <td>${valor.toFixed(1)}</td>
          <td>${dataHora}</td>
          <td>${estacao}</td>
        </tr>
      `;
      tabela.insertAdjacentHTML("beforeend", row);
    });

    // atualiza totais
    document.getElementById("forte").textContent = forte;
    document.getElementById("moderada").textContent = moderada;
    document.getElementById("fraca").textContent = fraca;
    document.getElementById("sem").textContent = sem;

  } catch (erro) {
    console.error(erro);
    document.getElementById("tabelaChuva").innerHTML =
      `<tr><td colspan="4" style="color:red;">${erro.message}</td></tr>`;
  }
}
