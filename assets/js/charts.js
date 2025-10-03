 //criação da função carrega os dados da API

function CarregarChuva(){
    fetch("https://cors-anywhere.herokuapp.com/https://cth.daee.sp.gov.br/sibh/api/v1/measurements/grouped?station_prefix_ids%5B%5D=33219&start_date=2024-09-18T19%3A47%3A40.062Z&end_date=2024-09-19T19%3A47%3A40.062Z&group_type=minutes")
    .then(function (response){
        response.json().then(function(chuva){
            console.log(chuva);

            chuva = chuva.json
            chuva = chuva.map(l => ({x: new Date(l.date).valueOf(), y: l.value}))
        
            console.log(chuva);

            Highcharts.chart('chuva', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Gráfico de Chuva'
                },
            
                 xAxis: {
                  type: "datetime"
                },
    
                tooltip: {
                    headerFormat: '',
                                    
                },
            
                series: [{
                    name: 'Chuva',
                    data: chuva
             
                }]
            });
            
        });      
        
    });

}

 function CarregarNvel() {
    fetch("https://cors-anywhere.herokuapp.com/https://cth.daee.sp.gov.br/sibh/api/v1/measurements/grouped?format=json&start_date=2024-09-19%2003%3A00&end_date=2024-09-20%2002%3A59&group_type=none&station_prefix_ids%5B%5D=16")
        .then(function (response) {
            response.json().then(function (dados) {
                console.log(dados);
                dados = dados.json

                dados = dados.map(z => ({ x: new Date(z.date).valueOf(), y: z.value }))
                console.log(dados);

                //Criação de gráficos
                Highcharts.chart('nivel', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: 'Gráfico de Nível',
                        align: 'left'
                    },
                    subtitle: {
                        text:
                            'Source: <a target="_blank" ' +
                            'href="https://www.indexmundi.com/agriculture/?commodity=corn">indexmundi</a>',
                        align: 'left'
                    },
                    xAxis: {
                        // categories: ['UGHRI1', 'UGHRI2', 'UGHRI3', 'UGHRI4', 'UGHRI5', 'UGHRI6'],
                        // crosshair: true,
                        // accessibility: {
                        //     description: 'Countries'
                        // }
                        type: "datetime"
                    },

                    tooltip: {
                        valueSuffix: ' ',
                        shared: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: [
                        {
                            name: 'Nível ',
                            data: dados
                        },

                    ]
                });
            });
        });

}


//window.onload = carrega primeiro a pagina com outros scripts.
window.onload = () => {

    CarregarNvel()
    CarregarChuva()
}
