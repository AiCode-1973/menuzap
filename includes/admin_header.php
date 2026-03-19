<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Verificação de segurança adicional já feita nos arquivos do admin
$perfil = $_SESSION['perfil'] ?? '';
$nome_user = $_SESSION['nome'] ?? 'Usuário';

// Identificar página atual
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - MenuZap</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f3f4f6; }
        /* Transição sidebar mobile */
        #sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="text-gray-800">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Overlay Mobile -->
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white z-30 transform -translate-x-full lg:translate-x-0 lg:static flex flex-col transition-transform duration-300">
            <div class="p-4 bg-gray-950 flex justify-between items-center shadow-md">
                <span class="text-2xl font-bold tracking-wider text-red-500"><i class="fa-solid fa-utensils mr-2 text-white"></i>Menu<span class="text-white">Zap</span></span>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-300 hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto py-4">
                <nav class="space-y-1 px-2">
                    <?php if ($perfil === 'admin'): ?>
                        <a href="index.php" class="<?= $currentPage == 'index.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-chart-line w-6"></i> Dashboard
                        </a>
                        <a href="pedidos.php" class="<?= $currentPage == 'pedidos.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-bell-concierge w-6"></i> Pedidos (Cozinha/Admin)
                        </a>
                        <a href="categorias.php" class="<?= $currentPage == 'categorias.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-layer-group w-6"></i> Categorias
                        </a>
                        <a href="produtos.php" class="<?= $currentPage == 'produtos.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-burger w-6"></i> Produtos
                        </a>
                        <a href="mesas.php" class="<?= $currentPage == 'mesas.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-qrcode w-6"></i> Mesas e QR Codes
                        </a>
                        <a href="configuracoes.php" class="<?= $currentPage == 'configuracoes.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-gear w-6"></i> Configurações
                        </a>
                    <?php else: ?>
                        <!-- Garçom Navbar -->
                        <a href="index.php" class="<?= $currentPage == 'index.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                            <i class="fa-solid fa-bell-concierge w-6"></i> Pedidos Ativos
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <div class="p-4 bg-gray-950 border-t border-gray-800 flex items-center justify-between">
                <div class="text-sm truncate mr-2">
                    <span class="block text-gray-300 font-medium"><?= htmlspecialchars($nome_user) ?></span>
                    <span class="block text-gray-500 text-xs"><?= ucfirst($perfil) ?></span>
                </div>
                <a href="../admin/logout.php" class="text-gray-400 hover:text-red-500" title="Sair">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col lg:w-0">
            <!-- Header Mobile -->
            <header class="lg:hidden bg-white shadow-sm flex items-center justify-between p-4 z-10 relative">
                <button onclick="toggleSidebar()" class="text-gray-600 focus:outline-none focus:text-gray-900">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="text-lg font-bold text-gray-800">Painel</div>
                <div class="w-6"></div> <!-- Espaçador para centralizar título -->
            </header>

            <div class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 py-6">
