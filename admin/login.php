<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['perfil'] = $usuario['perfil'];
        $_SESSION['nome'] = $usuario['nome'];
        
        if ($usuario['perfil'] == 'admin') {
            redirect('../admin/index.php');
        } else {
            redirect('../garcom/index.php');
        }
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Area Restrita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">MenuZap</h1>
        <p class="text-gray-500 text-sm">Acesso Restrito</p>
    </div>
    
    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 text-sm" role="alert">
            <p><?= $erro ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
            <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-500" placeholder="admin@admin.com">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
            <input type="password" name="senha" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-500" placeholder="admin123">
        </div>
        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors flex justify-center items-center gap-2">
            <i class="fa-solid fa-right-to-bracket"></i> Entrar
        </button>
    </form>
</div>

</body>
</html>
