<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao == 'nova') {
        $numero = $_POST['numero'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $qrcode_url = $site_url . '/cardapio.php?mesa=' . urlencode($numero);
        
        $stmt = $pdo->prepare("INSERT INTO mesas (numero, qrcode_url, ativo) VALUES (?, ?, ?)");
        $stmt->execute([$numero, $qrcode_url, $ativo]);
        redirect('mesas.php?msg=sucesso');

    } elseif ($acao == 'editar') {
        $id = (int)$_POST['id'];
        $numero = $_POST['numero'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $qrcode_url = $site_url . '/cardapio.php?mesa=' . urlencode($numero);
        
        $stmt = $pdo->prepare("UPDATE mesas SET numero=?, qrcode_url=?, ativo=? WHERE id=?");
        $stmt->execute([$numero, $qrcode_url, $ativo, $id]);
        redirect('mesas.php?msg=sucesso');

    } elseif ($acao == 'excluir') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM mesas WHERE id=?");
        $stmt->execute([$id]);
        redirect('mesas.php?msg=excluido');
    }
}

$stmt = $pdo->query("SELECT * FROM mesas ORDER BY numero ASC");
$mesas = $stmt->fetchAll();

$editId = $_GET['edit'] ?? null;
$mesaEdit = null;
if ($editId) {
    $stmtEdt = $pdo->prepare("SELECT * FROM mesas WHERE id=?");
    $stmtEdt->execute([$editId]);
    $mesaEdit = $stmtEdt->fetch();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Mesas e QR Codes</h1>
    <?php if (!$editId): ?>
        <button onclick="document.getElementById('modalNova').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-plus mr-2"></i> Nova Mesa
        </button>
    <?php else: ?>
        <a href="mesas.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div id="toastMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 transition-opacity duration-300">
        Ação realizada com sucesso!
    </div>
<?php endif; ?>

<?php if ($editId && $mesaEdit): ?>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 w-full max-w-lg mb-8">
        <h2 class="text-xl font-bold mb-4">Editar Mesa</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= $mesaEdit['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Número/Nome da Mesa</label>
                <input type="text" name="numero" value="<?= htmlspecialchars($mesaEdit['numero']) ?>" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
            </div>
            
            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="ativo" <?= $mesaEdit['ativo'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Mesa Ativa</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                Salvar Alterações
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- MODAL NOVA MESA -->
<div id="modalNova" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm mx-4">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold">Nova Mesa</h2>
            <button onclick="document.getElementById('modalNova').classList.add('hidden')" class="text-gray-500 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="acao" value="nova">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Número/Nome (Ex: 01, Salão 2)</label>
                <input type="text" name="numero" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="Ex: 5">
            </div>
            
            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="ativo" checked class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Mesa Ativa</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
                Cadastrar Mesa
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach($mesas as $mesa): 
        $qrUrl = "https://quickchart.io/qr?text=" . urlencode($mesa['qrcode_url']) . "&size=300&margin=1";
    ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col items-center relative">
        <?php if(!$mesa['ativo']): ?>
            <span class="absolute top-2 left-2 bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">Inativa</span>
        <?php endif; ?>
        
        <h3 class="text-xl font-bold text-gray-800 mb-2">Mesa <?= htmlspecialchars($mesa['numero']) ?></h3>
        
        <div class="bg-gray-100 p-2 rounded-lg mb-4 mt-2">
            <img src="<?= $qrUrl ?>" alt="QR Code Mesa <?= htmlspecialchars($mesa['numero']) ?>" class="w-32 h-32" />
        </div>
        
        <div class="flex w-full gap-2 mt-auto">
            <a href="mesas.php?edit=<?= $mesa['id'] ?>" class="flex-1 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 text-center font-bold py-2 rounded text-sm transition-colors border border-blue-200">
                Editar
            </a>
            <form method="POST" onsubmit="return confirm('Deseja excluir esta mesa? O histórico de pedidos ficará sem a mesa associada.');" class="flex-1">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= $mesa['id'] ?>">
                <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-800 font-bold py-2 rounded text-sm transition-colors border border-red-200">
                    Excluir
                </button>
            </form>
        </div>
        <div class="w-full mt-2">
            <a href="<?= $qrUrl ?>" download="QR_Mesa_<?= htmlspecialchars($mesa['numero']) ?>.png" target="_blank" class="block w-full text-center bg-gray-800 text-white hover:bg-gray-900 font-bold py-2 rounded text-sm transition-colors">
                <i class="fa-solid fa-download mr-1"></i> Baixar QR Code
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(count($mesas) == 0): ?>
        <div class="col-span-full text-center text-gray-500 py-10 bg-white rounded-lg border border-dashed border-gray-300">
            Nenhuma mesa cadastrada.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>
