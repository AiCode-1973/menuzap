<?php
require 'config.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mesa_num = isset($_GET['mesa']) ? $_GET['mesa'] : '';

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die("<div class='container mx-auto p-4'>Pedido não encontrado.</div>");
}

// Map status
$statusMap = [
    'recebido' => ['label' => 'Recebido', 'icon' => 'fa-clock', 'color' => 'text-blue-500'],
    'em_preparo' => ['label' => 'Em Preparo', 'icon' => 'fa-fire-burner', 'color' => 'text-orange-500'],
    'pronto' => ['label' => 'Pronto', 'icon' => 'fa-bell', 'color' => 'text-green-500'],
    'entregue' => ['label' => 'Entregue', 'icon' => 'fa-check-circle', 'color' => 'text-gray-500'],
    'cancelado' => ['label' => 'Cancelado', 'icon' => 'fa-xmark-circle', 'color' => 'text-red-500'],
];

$st = $statusMap[$pedido['status']] ?? $statusMap['recebido'];
?>

<div class="container mx-auto px-4 mt-10 max-w-md">
    <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-primary">
        <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Pedido Enviado!</h1>
        <p class="text-gray-600 mb-4">Obrigado! Seu pedido já está na nossa cozinha.</p>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-6 text-left border border-gray-100">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">Número do Pedido</span>
                <span class="font-bold text-lg text-primary">#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            
            <?php if ($mesa_num): ?>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">Mesa</span>
                <span class="font-bold text-gray-800"><?= htmlspecialchars($mesa_num) ?></span>
            </div>
            <?php endif; ?>
            
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">Total</span>
                <span class="font-bold text-gray-800">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
            </div>
            
            <hr class="my-3">
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Status Atual</span>
                <span class="font-bold <?= $st['color'] ?> flex items-center gap-2">
                    <i class="fa-solid <?= $st['icon'] ?>"></i>
                    <?= $st['label'] ?>
                </span>
            </div>
        </div>
        
        <a href="cardapio.php<?= $mesa_num ? '?mesa='.htmlspecialchars($mesa_num) : '' ?>" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded transition-colors mb-3">
            Voltar ao Cardápio
        </a>
        <button onclick="window.location.reload()" class="block w-full border border-primary text-primary hover:bg-primary hover:text-white font-semibold py-2 rounded transition-colors text-sm">
            <i class="fa-solid fa-rotate-right mr-1"></i> Atualizar Status
        </button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
