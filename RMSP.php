<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Mapa dos Reservatórios</title>
  
  <style>
    body {
      margin: 0;
      background: #f0f0f0;
      display: flex;
      justify-content: center;
    }

    .mapa-container {
      position: relative;
      width: 1100px; /* tamanho ajustado à imagem */
    }

    .mapa-container img {
      width: 100%;
      display: block;
    }

    /* estilo dos cards */
    .card {
      position: absolute;
      background: transparent;
      border-radius: 8px;
      padding: 5px 12px;
      font-family: Arial, sans-serif;
      font-size: 14px;
      color: #333;
      /* box-shadow: 0 2px 6px rgba(0,0,0,0.2); */
      min-width: 140px;
    }

    .card h3 {
      margin: 0;
      font-size: 15px;
      font-weight: bold;
    }

    /* ---- posições dos cards ---- */
    .cotia { top: 156px; left: 130px; }
    .cantareira { top: 60px; left: 475px; }
    .paraiba { top: 140px; left: 830px; }
    .sao-lourenco { top: 340px; left: 40px; }
    .guarapiranga { top: 560px; left: 300px; }
    .billings { top: 555px; left: 500px; }
    .rio-grande { top: 480px; left: 570px; }
    .alto-tiete { top: 470px; left: 750px; }
    .rio-claro { top: 410px; left: 950px; }
  </style>
</head>
<body>
   <!--?php include "navbar.php";?--> 
   
  <div class="mapa-container">
    <img src="Clipboard_09-10-2025_01.png" alt="Mapa dos Reservatórios">

    <div class="card cotia">
      <!-- <h3>Cotia</h3> -->
      Volume: <strong>55,4%</strong> <br>
      Vazão afluente: <strong>1,93 m³/s </strong><br>
      Vazão defluente: <strong>2,40 m³/s</strong>
    </div>

    <div class="card cantareira">
      <!-- <h3>Cantareira</h3> -->
      Volume: <strong>32,3%</strong> <br>
      Vazão afluente: <strong>14,35 m³/s</strong> <br>
      Vazão defluente: <strong>42,64 m³/s</strong>
    </div>

    <div class="card paraiba">
      <!-- <h3>Paraíba do Sul</h3> -->
      Volume:<strong> -</strong> <br>
      Vazão afluente: <strong>-</strong> <br>
      Vazão defluente: <strong>-</strong>
    </div>

    <div class="card sao-lourenco">
      <!-- <h3>São Lourenço</h3> -->
      Vazão afluente: <strong>12,89 m³/s</strong> <br>
      Vazão defluente: <strong>-</strong>
    </div>

    <div class="card guarapiranga">
      <!-- <h3>Guarapiranga</h3> -->
      Volume: <strong>50,5% </strong><br>
      Vazão afluente: <strong>9,48 m³/s</strong> <br>
      Vazão defluente: <strong>14,77 m³/s</strong>
    </div>

    <div class="card billings">
      <!-- <h3>Billings</h3> -->
      Volume: <strong>-</strong> <br>
      Vazão afluente: <strong>-</strong> <br>
      Vazão defluente: <strong>-</strong>
    </div>

    <div class="card rio-grande">
      <!-- <h3>Rio Grande</h3> -->
      Volume: <strong>55,6%</strong> <br>
      Vazão afluente: <strong>1,43 m³/s</strong> <br>
      Vazão defluente:<strong> 5,39 m³/s</strong>
    </div>

    <div class="card alto-tiete">
      <!-- <h3>Alto Tietê</h3> -->
      Volume: <strong>27,6%</strong> <br>
      Vazão afluente:<strong> 4,06 m³/s </strong><br>
      Vazão defluente: <strong>16,12 m³/s</strong>
    </div>

    <div class="card rio-claro">
      <!-- <h3>Rio Claro</h3> -->
      Volume: <strong>21,8%</strong> <br>
      Vazão afluente: <strong>2,31 m³/s</strong> <br>
      Vazão defluente: <strong>2,24 m³/s</strong>
    </div>
  </div>
</body>
</html>
