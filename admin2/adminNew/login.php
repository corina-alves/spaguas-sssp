<?php
session_start();
include('../conexao.php');
// Mostrar formulário e processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($usuario === '' || $senha === '') {
        $erro = 'Usuário e senha são obrigatórios.';
    } else {
        $sql = 'SELECT id, usuario, senha FROM admin WHERE usuario = ? LIMIT 1';
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $erro = 'Erro interno: ' . $conn->error;
        } else {
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                // verificar senha - suporta password_hash
                if (password_verify($senha, $row['senha'])) {
                    $_SESSION['user'] = $row['usuario'];
                    $_SESSION['user_id'] = $row['id'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $erro = 'Usuário ou senha inválidos.';
                }
            } else {
                $erro = 'Usuário ou senha inválidos.';
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - SSSP</title>
</head>
<body>
<h2>Login</h2>
<?php if (!empty($erro)) echo '<p style="color:red">'.htmlspecialchars($erro).'</p>'; ?>
<form method="post" action="">
    <label>Usuário<br><input type="text" name="usuario"></label><br>
    <label>Senha<br><input type="password" name="senha"></label><br>
    <button type="submit">Entrar</button>
</form>
</body>
</html>