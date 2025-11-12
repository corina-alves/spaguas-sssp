<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("America/Sao_Paulo");

// ===========================
// CONFIGURAÇÕES
// ===========================
$url = "https://cth.daee.sp.gov.br/sibh/api/v2/measurements/now?station_type_id=1&hours=3&show_all=true&serializer=complete&public=true";
$data = @file_get_contents($url);

if (!$data) {
    echo "<p style='color:red'>❌ Não foi possível obter dados da API.</p>";
    exit;
}

$json = json_decode($data, true);
if (!isset($json["measurements"])) {
    echo "<p style='color:red'>❌ Estrutura de dados inválida.</p>";
    exit;
}

// ===========================
// AGRUPAR POR UGRHI
// ===========================
$ugrhiData = [];
foreach ($json["measurements"] as $m) {
    $ugrhi = $m["ugrhi_name"] ?? "Não definido";
    $valor = $m["value"] ?? 0;

    if (!isset($ugrhiData[$ugrhi])) {
        $ugrhiData[$ugrhi] = [
            "count" => 0,
            "soma" => 0,
            "min" => $m["min_value"] ?? null,
            "max" => $m["max_value"] ?? null
        ];
    }

    $ugrhiData[$ugrhi]["count"]++;
    $ugrhiData[$ugrhi]["soma"] += $valor;

    if ($m["min_value"] < $ugrhiData[$ugrhi]["min"]) $ugrhiData[$ugrhi]["min"] = $m["min_value"];
    if ($m["max_value"] > $ugrhiData[$ugrhi]["max"]) $ugrhiData[$ugrhi]["max"] = $m["max_value"];
}

// ===========================
// FILTRO SELEÇÃO
// ===========================
$selectedUGRHI = $_GET['ugrhi'] ?? "all";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Monitoramento por UGRHI</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 20px; color: #333; }
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-top: 20px; }
.card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 20px; }
.card h3 { color: #0066cc; margin-bottom: 15px; }
.metricas { display: flex; justify-content: space-around; text-align: center; margin-top: 10px; }
.valor { font-size: 22px; font-weight: bold; color: #007bff; }
.label { font-size: 14px; color: #555; }
.barra { height: 12px; border-radius: 6px; background: #ddd; overflow: hidden; margin-top: 8px; }
.barra span { display: block; height: 100%; background: #00b4ff; }
</style>
</head>
<body>

<h2>Monitoramento de Chuva por UGRHI</h2>

<form method="GET">
    <label for="ugrhi">Filtrar por UGRHI:</label>
    <select name="ugrhi" id="ugrhi" onchange="this.form.submit()">
        <option value="all">Todas</option>
        <?php foreach ($ugrhiData as $ugrhi => $info): ?>
            <option value="<?= $ugrhi ?>" <?= $selectedUGRHI == $ugrhi ? "selected" : "" ?>><?= $ugrhi ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="grid">
<?php
foreach ($ugrhiData as $ugrhi => $info) {
    if ($selectedUGRHI != "all" && $ugrhi != $selectedUGRHI) continue;
    $media = $info["soma"] / max($info["count"], 1);
    $volume = rand(15, 90); // simula % do volume
    echo "
    <div class='card'>
        <h3>$ugrhi</h3>
        <div class='metricas'>
            <div>
                <div class='valor'>{$volume}%</div>
                <div class='label'>Volume</div>
                <div class='barra'><span style='width:{$volume}%;'></span></div>
            </div>
            <div>
                <div class='valor'>" . number_format($media, 1, ',', '.') . " mm</div>
                <div class='label'>Chuva média</div>
                <div class='barra'><span style='width:" . min($media, 100) . "%;'></span></div>
            </div>
        </div>
    </div>
    ";
}
?>
</div>

<h3>Gráfico de Chuva por UGRHI</h3>
<canvas id="barraChart" style="max-width:800px; margin-bottom:40px;"></canvas>
<canvas id="pizzaChart" style="max-width:500px;"></canvas>

<script>
const labels = <?= json_encode(array_keys($ugrhiData)) ?>;
const mediaValues = <?= json_encode(array_map(fn($i) => round($i["soma"] / max($i["count"], 1), 1), $ugrhiData)) ?>;

// Gráfico de barras
new Chart(document.getElementById('barraChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Chuva média (mm)',
            data: mediaValues,
            backgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Gráfico de pizza
new Chart(document.getElementById('pizzaChart'), {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            label: 'Participação no total de chuva',
            data: mediaValues,
            backgroundColor: labels.map(() => `hsl(${Math.random()*360},70%,50%)`)
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
