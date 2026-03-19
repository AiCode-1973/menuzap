<?php
require '../config.php';
checkGarcom();
include '../includes/admin_header.php';

// O garçom tem uma visão focada apenas em pedidos ativos para gerenciar as mesas e entregas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    $id = (int)$_POST['pedido_id'];
    $novoStatus = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE pedidos SET status=? WHERE id=?");
    $stmt->execute([$novoStatus, $id]);
    redirect('index.php?msg=sucesso');
}

$sql = "SELECT p.*, m.numero as mesa_numero 
        FROM pedidos p 
        LEFT JOIN mesas m ON p.mesa_id = m.id 
        WHERE p.status IN ('recebido', 'em_preparo', 'pronto') 
        ORDER BY p.criado_em DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll();

$mapStatus = [
    'recebido' => ['label' => 'Novo Pedido', 'color' => 'bg-blue-100 text-blue-800', 'btn' => 'em_preparo', 'btn_lbl' => 'Enviar Preparo', 'icon' => 'fa-bell'],
    'em_preparo' => ['label' => 'Cozinha Preparando', 'color' => 'bg-orange-100 text-orange-800', 'btn' => 'pronto', 'btn_lbl' => 'Marcar Pronto', 'icon' => 'fa-fire-burner'],
    'pronto' => ['label' => 'Pronto p/ Entrega', 'color' => 'bg-green-100 text-green-800', 'btn' => 'entregue', 'btn_lbl' => 'Entregar Cliente', 'icon' => 'fa-check'],
];
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Painel do Garçom</h1>
    <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">Mesa/Balcão</span>
</div>

<!-- Mobile Friendly Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($pedidos as $p): 
        $stInfo = $mapStatus[$p['status']];
        $time = date('H:i', strtotime($p['criado_em']));
        
        $stmtIt = $pdo->prepare("SELECT pi.quantidade, pr.nome FROM pedido_itens pi JOIN produtos pr ON pi.produto_id = pr.id WHERE pi.pedido_id = ?");
        $stmtIt->execute([$p['id']]);
        $itens = $stmtIt->fetchAll();
    ?>
    <div class="bg-white rounded-xl shadow-md border-l-4 <?= $p['status'] == 'pronto' ? 'border-green-500' : ($p['status'] == 'recebido' ? 'border-blue-500' : 'border-orange-500') ?> overflow-hidden p-4 relative">
        
        <?php if($p['status'] == 'pronto'): ?>
            <div class="absolute -top-6 -right-6 w-16 h-16 bg-green-500 rotate-45 z-0 shadow-lg"></div>
        <?php endif; ?>

        <div class="flex justify-between items-start mb-2 relative z-10">
            <div>
                <span class="text-3xl font-black text-gray-800 block leading-tight">Mesa <?= htmlspecialchars($p['mesa_numero'] ?: '--') ?></span>
                <span class="text-xs font-bold text-gray-500">Ped. #<?= $p['id'] ?> &bull; <?= $time ?></span>
            </div>
            <span class="text-xs font-bold px-2 py-1 rounded <?= $stInfo['color'] ?> shadow-sm">
                <i class="fa-solid <?= $stInfo['icon'] ?> mr-1"></i> <?= $stInfo['label'] ?>
            </span>
        </div>
        
        <div class="bg-gray-50 p-2 rounded text-sm mb-4 border border-gray-100 shadow-inner">
            <ul class="space-y-1">
                <?php foreach($itens as $it): ?>
                    <li class="font-semibold text-gray-700">
                        <span class="text-red-500 mr-1"><?= $it['quantidade'] ?>x</span><?= htmlspecialchars($it['nome']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <form method="POST">
            <input type="hidden" name="acao" value="status">
            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="status" value="<?= $stInfo['btn'] ?>">
            <button class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-lg shadow-lg transition-transform active:scale-95 text-lg">
                <?= $stInfo['btn_lbl'] ?>
            </button>
        </form>
    </div>
    <?php endforeach; ?>
    <?php if(count($pedidos) == 0): ?>
        <div class="col-span-full py-20 text-center bg-white rounded-xl shadow-sm border border-gray-200">
             <i class="fa-solid fa-mug-hot text-6xl text-gray-300 mb-4 block"></i>
             <p class="text-xl text-gray-500 font-bold">Nenhum pedido ativo no momento.</p>
             <p class="text-sm text-gray-400 mt-2">Atualizando automaticamente em <span id="counter">15</span>s...</p>
        </div>
    <?php endif; ?>
</div>

<script>
    let timeLeft = 15;
    const counterSpan = document.getElementById('counter');
    setInterval(() => {
        timeLeft--;
        if (counterSpan) counterSpan.innerText = timeLeft;
        if (timeLeft <= 0) window.location.reload();
    }, 1000);
</script>

<?php include '../includes/admin_footer.php'; ?>
