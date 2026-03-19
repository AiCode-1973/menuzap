<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

// Ações
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    $id = (int)$_POST['pedido_id'];
    $novoStatus = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE pedidos SET status=? WHERE id=?");
    $stmt->execute([$novoStatus, $id]);
    redirect('pedidos.php?msg=sucesso');
}

// Filtro
$statusFiltro = $_GET['status'] ?? 'ativos'; // ativos = não cancelado, não entregue
$mesaFiltro = $_GET['mesa'] ?? '';

$sql = "SELECT p.*, m.numero as mesa_numero 
        FROM pedidos p 
        LEFT JOIN mesas m ON p.mesa_id = m.id 
        WHERE 1=1";
$params = [];

if ($statusFiltro == 'ativos') {
    $sql .= " AND p.status IN ('recebido', 'em_preparo', 'pronto')";
} elseif ($statusFiltro == 'historico') {
    $sql .= " AND p.status IN ('entregue', 'cancelado') AND DATE(p.criado_em) = CURDATE()";
} else {
    $sql .= " AND p.status = ?";
    $params[] = $statusFiltro;
}

if ($mesaFiltro) {
    if ($mesaFiltro == 'avulso') {
        $sql .= " AND p.mesa_id IS NULL";
    } else {
        $sql .= " AND p.mesa_id = ?";
        $params[] = $mesaFiltro;
    }
}

$sql .= " ORDER BY p.criado_em DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Mesas options for filter
$mesasOpt = $pdo->query("SELECT id, numero FROM mesas ORDER BY numero")->fetchAll();

$mapStatus = [
    'recebido' => ['label' => 'Novo', 'color' => 'bg-blue-100 text-blue-800', 'btn' => 'em_preparo', 'btn_lbl' => 'Preparar', 'btn_color' => 'bg-orange-500 hover:bg-orange-600'],
    'em_preparo' => ['label' => 'Preparo', 'color' => 'bg-orange-100 text-orange-800', 'btn' => 'pronto', 'btn_lbl' => 'Pronto', 'btn_color' => 'bg-green-500 hover:bg-green-600'],
    'pronto' => ['label' => 'Pronto', 'color' => 'bg-green-100 text-green-800', 'btn' => 'entregue', 'btn_lbl' => 'Entregar', 'btn_color' => 'bg-gray-500 hover:bg-gray-600'],
    'entregue' => ['label' => 'Entregue', 'color' => 'bg-gray-200 text-gray-800'],
    'cancelado' => ['label' => 'Cancelado', 'color' => 'bg-red-100 text-red-800']
];
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Pedidos (Cozinha/Balcão)</h1>
    <span class="text-sm text-gray-500"><i class="fa-solid fa-rotate mr-1"></i> Auto-refresh em <span id="counter">30</span>s</span>
</div>

<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-wrap gap-4 items-end">
    <form method="GET" class="flex flex-wrap gap-4 items-end w-full">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
            <select name="status" class="border rounded px-3 py-2 text-sm focus:outline-none focus:border-red-500 bg-white">
                <option value="ativos" <?= $statusFiltro == 'ativos' ? 'selected' : '' ?>>Somente Ativos</option>
                <option value="historico" <?= $statusFiltro == 'historico' ? 'selected' : '' ?>>Histórico do Dia</option>
                <option value="recebido" <?= $statusFiltro == 'recebido' ? 'selected' : '' ?>>Novos</option>
                <option value="em_preparo" <?= $statusFiltro == 'em_preparo' ? 'selected' : '' ?>>Em Preparo</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Mesa</label>
            <select name="mesa" class="border rounded px-3 py-2 text-sm focus:outline-none focus:border-red-500 bg-white">
                <option value="">Todas</option>
                <option value="avulso" <?= $mesaFiltro == 'avulso' ? 'selected' : '' ?>>Balcão / Sem Mesa</option>
                <?php foreach($mesasOpt as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $mesaFiltro == $m['id'] ? 'selected' : '' ?>>Mesa <?= htmlspecialchars($m['numero']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
            Filtrar
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php foreach ($pedidos as $p): 
        // Detalhes dos itens
        $stmtIt = $pdo->prepare("SELECT pi.*, pr.nome FROM pedido_itens pi JOIN produtos pr ON pi.produto_id = pr.id WHERE pi.pedido_id = ?");
        $stmtIt->execute([$p['id']]);
        $itens = $stmtIt->fetchAll();
        
        $statusInfo = $mapStatus[$p['status']];
        $time = strtotime($p['criado_em']);
    ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col relative overflow-hidden <?= $p['status'] == 'recebido' ? 'border-blue-300 ring-2 ring-blue-100 ring-offset-2' : '' ?>">
        
        <?php if($p['status'] == 'recebido'): ?>
            <div class="absolute top-0 inset-x-0 h-1 bg-blue-500 animate-pulse"></div>
        <?php endif; ?>
        
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
            <div>
                <span class="text-sm font-bold text-gray-500 block">Pedido #<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></span>
                <span class="text-xs text-gray-400"><i class="fa-regular fa-clock"></i> <?= date('H:i', $time) ?></span>
            </div>
            <div class="text-right">
                <span class="text-lg font-black text-gray-800 tracking-tight block">
                    <?php if($p['mesa_numero']): ?>
                        Mesa <?= htmlspecialchars($p['mesa_numero']) ?>
                    <?php else: ?>
                        Balcão
                    <?php endif; ?>
                </span>
                <?php if($p['cliente_nome']): ?>
                    <span class="text-xs font-medium text-gray-500 block"><?= htmlspecialchars($p['cliente_nome']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="p-4 flex-1 text-sm bg-white">
            <span class="inline-block px-2 py-1 rounded text-xs font-bold mb-3 <?= $statusInfo['color'] ?>"><?= $statusInfo['label'] ?></span>
            
            <ul class="space-y-3 mb-2">
                <?php foreach($itens as $it): ?>
                    <li class="flex flex-col border-b border-gray-50 pb-2">
                        <div class="flex justify-between font-bold text-gray-800">
                            <span><span class="text-primary mr-2"><?= $it['quantidade'] ?>x</span><?= htmlspecialchars($it['nome']) ?></span>
                        </div>
                        <?php if($it['observacao']): ?>
                            <span class="text-xs text-red-600 mt-1 bg-red-50 p-1 rounded inline-block w-full">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i><?= htmlspecialchars($it['observacao']) ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="p-4 bg-gray-50 border-t flex flex-wrap gap-2 justify-between items-center">
            <span class="font-bold text-gray-800 text-sm">R$ <?= number_format($p['total'], 2, ',', '.') ?></span>
            
            <div class="flex gap-2">
                <?php if(in_array($p['status'], ['recebido', 'em_preparo'])): ?>
                <form method="POST" onsubmit="return confirm('Cancelar este pedido?');">
                    <input type="hidden" name="acao" value="status">
                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="status" value="cancelado">
                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold py-1.5 px-3 rounded text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
                <?php endif; ?>
                
                <?php if(isset($statusInfo['btn'])): ?>
                <form method="POST">
                    <input type="hidden" name="acao" value="status">
                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="status" value="<?= $statusInfo['btn'] ?>">
                    <button type="submit" class="<?= $statusInfo['btn_color'] ?> text-white font-bold py-1.5 px-4 rounded text-xs transition-colors border shadow-sm flex items-center gap-1">
                        <?= $statusInfo['btn_lbl'] ?> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        
        <button onclick="window.print()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 print:hidden hidden lg:block" title="Imprimir Comanda">
            <i class="fa-solid fa-print"></i>
        </button>
    </div>
    <?php endforeach; ?>
    <?php if(count($pedidos) == 0): ?>
        <div class="col-span-full text-center text-gray-500 py-16 bg-white rounded-lg border border-dashed border-gray-300">
            <i class="fa-solid fa-mug-hot text-4xl mb-4 text-gray-300"></i>
            <p>Nenhum pedido encontrado nos filtros selecionados.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Auto Refresh 30 seconds
    let timeLeft = 30;
    const counterSpan = document.getElementById('counter');
    
    setInterval(() => {
        timeLeft--;
        if (counterSpan) counterSpan.innerText = timeLeft;
        if (timeLeft <= 0) {
            window.location.reload();
        }
    }, 1000);
</script>

<?php include '../includes/admin_footer.php'; ?>
