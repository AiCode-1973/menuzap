<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

// Ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao == 'nova') {
        $nome = $_POST['nome'];
        $icone = $_POST['icone'] ?? '';
        $ordem = (int)$_POST['ordem'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, icone, ordem, ativo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $icone, $ordem, $ativo]);
        redirect('categorias.php?msg=sucesso');
    } elseif ($acao == 'editar') {
        $id = (int)$_POST['id'];
        $nome = $_POST['nome'];
        $icone = $_POST['icone'] ?? '';
        $ordem = (int)$_POST['ordem'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE categorias SET nome=?, icone=?, ordem=?, ativo=? WHERE id=?");
        $stmt->execute([$nome, $icone, $ordem, $ativo, $id]);
        redirect('categorias.php?msg=sucesso');
    } elseif ($acao == 'excluir') {
        $id = (int)$_POST['id'];
        // Cascade vai apagar os produtos atrelados no BD. Mas vamos permitir via interface
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id=?");
        $stmt->execute([$id]);
        redirect('categorias.php?msg=excluido');
    }
}

// Listagem
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC, id ASC");
$categorias = $stmt->fetchAll();

// Modal Edit Variables
$editId = $_GET['edit'] ?? null;
$catEdit = null;
if ($editId) {
    $stmtEdt = $pdo->prepare("SELECT * FROM categorias WHERE id=?");
    $stmtEdt->execute([$editId]);
    $catEdit = $stmtEdt->fetch();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Categorias</h1>
    <?php if (!$editId): ?>
        <button onclick="document.getElementById('modalNova').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-plus mr-2"></i> Nova Categoria
        </button>
    <?php else: ?>
        <a href="categorias.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div id="toastMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 transition-opacity duration-300">
        Ação realizada com sucesso!
    </div>
<?php endif; ?>

<?php if ($editId && $catEdit): ?>
    <!-- Editar -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 w-full max-w-lg mb-8">
        <h2 class="text-xl font-bold mb-4">Editar Categoria</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= $catEdit['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($catEdit['nome']) ?>" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Ícone (Classes FontAwesome, ex: fa-solid fa-pizza-slice)</label>
                <input type="text" name="icone" value="<?= htmlspecialchars($catEdit['icone']) ?>" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                <p class="text-xs text-gray-500 mt-1">Veja ícones em fontawesome.com</p>
            </div>
            
            <div class="flex gap-4 mb-4">
                <div class="w-1/2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Ordem</label>
                    <input type="number" name="ordem" value="<?= $catEdit['ordem'] ?>" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                </div>
                <div class="w-1/2 flex items-center pt-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" <?= $catEdit['ativo'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded">
                        <span class="ml-2 text-gray-700 font-bold">Ativa no cardápio</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                Salvar Alterações
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Formulário Nova (Modal simulado via overlay) -->
<div id="modalNova" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold">Nova Categoria</h2>
            <button onclick="document.getElementById('modalNova').classList.add('hidden')" class="text-gray-500 hover:text-red-500">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="acao" value="nova">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
                <input type="text" name="nome" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="Ex: Entradas">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Ícone (Classes FontAwesome)</label>
                <input type="text" name="icone" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="fEx: a-solid fa-pizza-slice">
            </div>
            
            <div class="flex gap-4 mb-4">
                <div class="w-1/2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Ordem</label>
                    <input type="number" name="ordem" value="0" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                </div>
                <div class="w-1/2 flex items-center pt-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" checked class="form-checkbox h-5 w-5 text-red-600 rounded">
                        <span class="ml-2 text-gray-700 font-bold">Ativa</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
                Cadastrar Categoria
            </button>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Ícone</th>
                    <th class="px-6 py-4">Nome</th>
                    <th class="px-6 py-4">Ordem</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(count($categorias) > 0): ?>
                    <?php foreach ($categorias as $cat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">#<?= $cat['id'] ?></td>
                        <td class="px-6 py-4 text-gray-400 text-lg"><i class="<?= htmlspecialchars($cat['icone']) ?>"></i></td>
                        <td class="px-6 py-4 font-bold text-gray-800"><?= htmlspecialchars($cat['nome']) ?></td>
                        <td class="px-6 py-4 text-gray-500"><?= $cat['ordem'] ?></td>
                        <td class="px-6 py-4">
                            <?php if ($cat['ativo']): ?>
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full">Ativa</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded-full">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 flex items-center justify-center gap-2">
                            <a href="categorias.php?edit=<?= $cat['id'] ?>" class="text-blue-500 hover:text-blue-700 p-2" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" onsubmit="return confirm('ATENÇÃO: Isso excluirá os produtos desta categoria também! Continuar?');" class="inline">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 p-2" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhuma categoria cadastrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
