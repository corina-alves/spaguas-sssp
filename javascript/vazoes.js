
const sistemas = dados.map(d => d.Sistema);
const vazaoDia = dados.map(d => d.Vazao_dia);
const vazao7d = dados.map(d => d.Vazao_7d);
const vazaoMes = dados.map(d => d.Vazao_mes);
const vazaoMesRef = dados.map(d => d.Vazao_mes_ref);
const vazaoClima = dados.map(d => d.Vazao_media_clima);

const ctx = document.getElementById('graficoVazao').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: sistemas,
        datasets: [
            {
                label: 'Vazão do dia (m³/s)',
                data: vazaoDia,
                backgroundColor: '#1f77b4'
            },
            {
                label: 'Média 7 dias (m³/s)',
                data: vazao7d,
                backgroundColor: '#2ca02c'
            },
            {
                label: 'Vazão mês <?= $data_base->format('Y') ?> (m³/s)',
                data: vazaoMes,
                backgroundColor: '#9467bd'
            },
            {
                label: 'Vazão mês <?= $ano_ref ?> (m³/s)',
                data: vazaoMesRef,
                backgroundColor: '#ff7f0e'
            },
            {
                label: 'Média climatológica (m³/s)',
                data: vazaoClima,
                backgroundColor: '#17becf'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            },
            title: {
                display: true,
                text: 'Comparativo de Vazões — Sistemas Produtores (m³/s)'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Sistemas'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Vazão (m³/s)'
                }
            }
        }
    }
});
