<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

$hoje = date('Y-m-d');

// Total Pedidos Dia
$stmtPd = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE DATE(criado_em) = ? AND status != 'cancelado'");
$stmtPd->execute([$hoje]);
$total_pedidos = $stmtPd->fetchColumn();

// Faturamento Dia
$stmtFat = $pdo->prepare("SELECT SUM(total) FROM pedidos WHERE DATE(criado_em) = ? AND status != 'cancelado'");
$stmtFat->execute([$hoje]);
$faturamento = $stmtFat->fetchColumn() ?: 0;

// Mesas ativas (simplificando: mesas com pedidos ativos não cancelados e não entregues hoje)
$stmtMesas = $pdo->query("SELECT COUNT(DISTINCT mesa_id) FROM pedidos WHERE status NOT IN ('entregue', 'cancelado')");
$mesas_ativas = $stmtMesas->fetchColumn();

// Produtos mais vendidos
$stmtMais = $pdo->query("
    SELECT p.nome, SUM(pi.quantidade) as total_vendido
    FROM pedido_itens pi
    JOIN produtos p ON pi.produto_id = p.id
    JOIN pedidos pd ON pi.pedido_id = pd.id
    WHERE pd.status != 'cancelado'
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 5
");
$produtos_mais_vendidos = $stmtMais->fetchAll();
?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Visão Geral</h1>

<!-- Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Pedidos (Hoje)</p>
                <h3 class="text-3xl font-bold text-gray-800"><?= $total_pedidos ?></h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-full text-blue-500">
                <i class="fa-solid fa-receipt text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Faturamento (Hoje)</p>
                <h3 class="text-3xl font-bold text-gray-800">R$ <?= number_format($faturamento, 2, ',', '.') ?></h3>
            </div>
            <div class="p-3 bg-green-50 rounded-full text-green-500">
                <i class="fa-solid fa-money-bill-wave text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Mesas Ocupadas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?= $mesas_ativas ?></h3>
            </div>
            <div class="p-3 bg-orange-50 rounded-full text-orange-500">
                <i class="fa-solid fa-chair text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Produtos Mais Pedidos -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8 border border-gray-100">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800">Top 5 Produtos Mais Vendidos</h3>
    </div>
    <div class="p-0">
        <table class="w-full whitespace-nowrap text-sm text-left">
            <tbody class="divide-y divide-gray-100">
                <?php if(count($produtos_mais_vendidos) > 0): ?>
                    <?php foreach($produtos_mais_vendidos as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="px-6 py-4 text-gray-500 w-32text-right"><span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded ml-2"><?= $p['total_vendido'] ?> un</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-gray-500">Nenhum produto vendido ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
