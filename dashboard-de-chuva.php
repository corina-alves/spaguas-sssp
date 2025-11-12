<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header("Content-Type: text/html; charset=utf-8");

// CONFIGURAÇÕES
$cacheFile = __DIR__ . '/cache.json';
$cacheTime = 60; // 1 hora
$apiUrl = "https://cth.daee.sp.gov.br/sibh/api/v2/measurements/now?station_type_id=1&hours=3&show_all=true&serializer=complete&public=true";

// FUNÇÃO PARA BUSCAR API
function fetchApi($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// VERIFICAR CACHE
if(file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime){
    $data = json_decode(file_get_contents($cacheFile), true);
} else {
    $data = fetchApi($apiUrl);
    if($data){
        file_put_contents($cacheFile, json_encode($data));
    } else if(file_exists($cacheFile)){
        $data = json_decode(file_get_contents($cacheFile), true);
    } else {
        $data = ['measurements'=>[]];
    }
}

// AGRUPAR DADOS POR UGRHI
$ugrhiMap = [];
$ugrhiCoords = [];

if(isset($data['measurements'])){
    foreach($data['measurements'] as $item){
        $ugrhi = $item['ugrhi_name'] ?? "";
        if(!isset($ugrhiMap[$ugrhi])) $ugrhiMap[$ugrhi] = [];
        $ugrhiMap[$ugrhi][] = $item['value'] ?? 0;

        if(!isset($ugrhiCoords[$ugrhi])){
            $ugrhiCoords[$ugrhi] = [
                'lat' => floatval($item['latitude'] ?? -23.5),
                'lng' => floatval($item['longitude'] ?? -46.6)
            ];
        }
    }
}

// ARRAYS PARA JS
$ugrhiLabels = array_keys($ugrhiMap);
$mediaValues = array_map(function($vals){
    return round(array_sum($vals)/count($vals));
}, $ugrhiMap);

// INDICADORES RÁPIDOS
$totalUGRHI = count($ugrhiLabels);
$chuvaMedia = $mediaValues ? round(array_sum($mediaValues)/count($mediaValues)) : 0;
$volumeMaximo = $mediaValues ? max($mediaValues) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard Monitoramento de Chuva</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f5f7fa; }
header { background:#0066cc; color:white; padding:15px; text-align:center; font-size:1.5em; display:flex; justify-content:space-between; align-items:center; }
.dashboard { display:grid; grid-template-columns: repeat(2, 1fr); gap:20px; padding:20px; }
.card { background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:20px; }
.card h3 { margin-bottom:15px; color:#0066cc; }
#map { height:300px; border-radius:12px; }
button#updateBtn { background:#00b4ff; color:white; border:none; border-radius:8px; padding:8px 15px; cursor:pointer; }
button#updateBtn:hover { background:#0099e0; }
</style>
</head>
<body>

<header>
    Dashboard de Monitoramento de Chuva por UGRHI
    <button id="updateBtn">Atualizar Agora</button>
</header>

<div class="dashboard">
    <div class="card">
        <h3>Chuva Média por UGRHI</h3>
        <canvas id="barChart"></canvas>
    </div>
    <div class="card">
        <h3>Participação de UGRHIs</h3>
        <canvas id="pieChart"></canvas>
    </div>
    <div class="card">
        <h3>Mapa de Chuvas</h3>
        <div id="map"></div>
    </div>
    <div class="card">
        <h3>Indicadores Rápidos</h3>
        <p>Total de UGRHIs: <?php echo $totalUGRHI; ?></p>
        <p>Chuva média: <?php echo $chuvaMedia; ?> mm</p>
        <p>Volume máximo: <?php echo $volumeMaximo; ?> mm</p>
    </div>
</div>

<script>
// Dados iniciais do PHP para JS
let ugrhiLabels = <?php echo json_encode($ugrhiLabels); ?>;
let mediaValues = <?php echo json_encode($mediaValues); ?>;
let ugrhiCoords = <?php echo json_encode($ugrhiCoords); ?>;

// ----------------------
// CRIAR GRÁFICOS GLOBAIS
// ----------------------
const barChart = new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ugrhiLabels,
        datasets: [{
            label: 'Chuva média (mm)',
            data: mediaValues,
            backgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { type: 'linear', beginAtZero: true, title: { display: true, text: 'Chuva (mm)' } },
            x: { title: { display: true, text: 'UGRHI' } }
        }
    }
});

const pieChart = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ugrhiLabels,
        datasets: [{
            data: mediaValues,
            backgroundColor: [
                '#007bff','#00b4ff','#00d1b2','#ff9900','#ff4444',
                '#cc00cc','#00cc66','#ffcc00','#0099ff','#ff6600'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});

// ----------------------
// MAPA LEAFLET
// ----------------------
const map = L.map('map').setView([-23.5,-46.6], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

function updateMap(){
    map.eachLayer(layer => {
        if(layer instanceof L.Circle) map.removeLayer(layer);
    });
    ugrhiLabels.forEach(label => {
        const coords = ugrhiCoords[label];
        const value = mediaValues[ugrhiLabels.indexOf(label)];
        L.circle([coords.lat, coords.lng], {
            radius: value*1000,
            color: '#007bff',
            fillOpacity: 0.3
        }).addTo(map)
          .bindPopup(`${label}: ${value} mm`);
    });
}

updateMap(); // Inicial

// ----------------------
// BOTÃO "ATUALIZAR AGORA"
// ----------------------
document.getElementById('updateBtn').addEventListener('click', () => {
    fetch('fetch_data.php')
    .then(res => res.json())
    .then(data => {
        const ugrhiMap = {};
        const ugrhiCoordsNew = {};

        data.measurements.forEach(item => {
            const ugrhi = item.ugrhi_name || "Indefinido";
            if(!ugrhiMap[ugrhi]) ugrhiMap[ugrhi] = [];
            ugrhiMap[ugrhi].push(item.value);

            if(!ugrhiCoordsNew[ugrhi]){
                ugrhiCoordsNew[ugrhi] = {
                    lat: parseFloat(item.latitude) || -23.5,
                    lng: parseFloat(item.longitude) || -46.6
                };
            }
        });

        ugrhiLabels = Object.keys(ugrhiMap);
        mediaValues = ugrhiLabels.map(l => Math.round(ugrhiMap[l].reduce((a,b)=>a+b,0)/ugrhiMap[l].length));
        ugrhiCoords = ugrhiCoordsNew;

        // Atualizar gráficos
        barChart.data.labels = ugrhiLabels;
        barChart.data.datasets[0].data = mediaValues;
        barChart.update();

        pieChart.data.labels = ugrhiLabels;
        pieChart.data.datasets[0].data = mediaValues;
        pieChart.update();

        // Atualizar mapa
        updateMap();

        // Atualizar indicadores
        document.querySelector('.card p:nth-child(1)').innerText = `Total de UGRHIs: ${ugrhiLabels.length}`;
        const mediaTotal = Math.round(mediaValues.reduce((a,b)=>a+b,0)/mediaValues.length);
        document.querySelector('.card p:nth-child(2)').innerText = `Chuva média: ${mediaTotal} mm`;
        const maxVolume = Math.max(...mediaValues);
        document.querySelector('.card p:nth-child(3)').innerText = `Volume máximo: ${maxVolume} mm`;
    })
    .catch(err => console.error('Erro ao atualizar dados:', err));
});
</script>

</body>
</html>
