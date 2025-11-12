
function fazGet(url){
    let requuest = new XMLHttpRequest()
    requuest.open("GET", url, false)
    requuest.send()
    return requuest.responseText
}
function CriarLinha(reservatorios){
    console.log(reservatorios)
linha = document.createElement("tr");
SistemaId = document.createElement("td");
VolumeUtil = document.createElement("td");

SistemaId.innerHTML = reservatorios.SistemaId
VolumeUtil.innerHTML = reservatorios.VolumeUtil

linha.appendChild(SistemaId);
linha.appendChild(VolumeUtil);

return linha;

}

function main(){

    // console.log(fazGet("https://cors-anywhere.herokuapp.com/https://mananciais.sabesp.com.br/api/Mananciais/Boletins/Mananciais/2025-09-12"));
    let data = fazGet("https://cors-anywhere.herokuapp.com/https://mananciais.sabesp.com.br/api/Mananciais/Boletins/Mananciais/2025-09-12");
    let reservatorios = JSON.parse(data);
    let tabela = document.getElementById(tabela)
    reservatorios.forEach(element =>{
        let linha = CriarLinha(element);
        tabela.appendChild(linha);
    });
}
main()