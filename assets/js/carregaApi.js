window.onload = function(){
    carregarTabelaDado()
}
function carregarTabelaDado() {
    fetch("https://cors-anywhere.herokuapp.com/https://cth.daee.sp.gov.br/sibh/api/v1/measurements/last_hours_events?hours=24&show_all=true")
      .then(function(response) {
          response.json().then(function(dados) {
              console.log(dados);
              let medicoes = dados.json;
              let maiores = medicoes.sort((a, b) => b.value - a.value).slice(0, 8);
              let tabela = document.getElementById("tabela_eventos");
              let tbody = tabela.getElementsByTagName("tbody")[0];
             
              tbody.innerHTML = null;
              
              maiores.forEach(maior => {
                 
                  let tr = document.createElement("tr");
        
                  // Preencher linha tabela
                  tr.innerHTML = "<td> <strong>" + maior.city +
                      "</td> </strong><td>" + maior.value.toFixed(1) +
                      "</td><td>" + " - " +
                      "</td><td>" + " - " +
                      "</td>";
                  // Anexar linha no corpo da tabela
                  tbody.appendChild(tr);
              });

              if (maiores.length == 0) {
                  let tr = document.createElement("tr");

                  tr.innerHTML = "<td colspan='4'>Sem Dados</td>";

                  tbody.appendChild(tr);
              }
          });
      });

}

function carregaPrevisao(){
    let latLngs = [
            { text: '-23.031009, -45.566196', name: 'Taubaté' },
            { text: '-21.172541, -47.812758', name: 'Ribeirão Preto' },
            { text: '-22.738662, -47.645608', name: 'Piracicaba' },
            { text: '-23.624199, -46.676390', name: 'São Paulo' },
    
            { text: '-20.819188, -49.378521', name: 'São José do Rio Preto' },
            { text: '-22.219694, -49.950266', name: 'Marília' },
            { text: '-24.187076, -46.801280', name: 'Itanhaém' },
            { text: '-24.496514, -47.846099', name: 'Registro' },
    
        ];

        latLngs.forEach(latLng=>{
            fetch("https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/" + latLng.text + "?unitGroup=metric&key=F25Q6KUDD73CMRSWWS8US97X6&contentType=json")
            .then(response => response.json())
            .then(previsao =>{
                console.log(previsao);

                let climaAgora = previsao.currentConditons;
                let data = previsao.days[1];
                let chuva = data.precip || 0;

                let previsaoTempo = document.createElement("div");
                previsaoTempo.classList.add('card');

                previsaoTempo.innerHTML = `
                <div class='row'>
                <div class='col'>
                Agora: ${climaAgora.temp} º
                <strong> ${latLng.name} </strong><br/>
                            <i class="fa-solid fa-arrow-down" style="color:#7d9bfc;"></i> ${data.tempmin} <i class="fa-solid fa-arrow-up" style="color:#f36951;"></i> ${data.tempmax} °C <br/>
                            Chuva: ${chuva} mm
                            </div>
                            </div>`;
                let prev = document.getElementById("previsao")
                
        })
}