<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

// Criar pasta de uploads se não existir
if (!is_dir('../uploads')) {
    mkdir('../uploads', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    // Tratamento de Upload
    $foto = null;
    $foto_atual = $_POST['foto_atual'] ?? null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $permitidos) && $_FILES['foto']['size'] <= 2097152) { // Max 2MB
            $novo_nome = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $novo_nome)) {
                $foto = $novo_nome;
                if ($foto_atual && file_exists('../uploads/' . $foto_atual)) {
                    unlink('../uploads/' . $foto_atual); // Apaga anterior
                }
            }
        }
    } else {
        $foto = $foto_atual;
    }

    if ($acao == 'nova') {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'] ?? '';
        $preco = str_replace(',', '.', $_POST['preco']);
        $categoria_id = (int)$_POST['categoria_id'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $esgotado = isset($_POST['esgotado']) ? 1 : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO produtos (categoria_id, nome, descricao, preco, foto, destaque, esgotado, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$categoria_id, $nome, $descricao, $preco, $foto, $destaque, $esgotado, $ativo]);
        redirect('produtos.php?msg=sucesso');

    } elseif ($acao == 'editar') {
        $id = (int)$_POST['id'];
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'] ?? '';
        $preco = str_replace(',', '.', $_POST['preco']);
        $categoria_id = (int)$_POST['categoria_id'];
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $esgotado = isset($_POST['esgotado']) ? 1 : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE produtos SET categoria_id=?, nome=?, descricao=?, preco=?, foto=?, destaque=?, esgotado=?, ativo=? WHERE id=?");
        $stmt->execute([$categoria_id, $nome, $descricao, $preco, $foto, $destaque, $esgotado, $ativo, $id]);
        redirect('produtos.php?msg=sucesso');

    } elseif ($acao == 'excluir') {
        $id = (int)$_POST['id'];
        $stmtFoto = $pdo->prepare("SELECT foto FROM produtos WHERE id=?");
        $stmtFoto->execute([$id]);
        $fotoDel = $stmtFoto->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=?");
        if ($stmt->execute([$id]) && $fotoDel && file_exists('../uploads/' . $fotoDel)) {
            unlink('../uploads/' . $fotoDel);
        }
        redirect('produtos.php?msg=excluido');
    }
}

// Bucar dados
$stmtCat = $pdo->query("SELECT id, nome FROM categorias ORDER BY ordem ASC");
$categorias = $stmtCat->fetchAll();

$stmtProd = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
$produtos = $stmtProd->fetchAll();

$editId = $_GET['edit'] ?? null;
$prodEdit = null;
if ($editId) {
    $stmtEdt = $pdo->prepare("SELECT * FROM produtos WHERE id=?");
    $stmtEdt->execute([$editId]);
    $prodEdit = $stmtEdt->fetch();
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Produtos</h1>
    <?php if (!$editId): ?>
        <button onclick="document.getElementById('modalNova').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-plus mr-2"></i> Novo Produto
        </button>
    <?php else: ?>
        <a href="produtos.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div id="toastMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 transition-opacity duration-300">
        Ação realizada com sucesso!
    </div>
<?php endif; ?>

<?php if ($editId && $prodEdit): ?>
    <!-- Editar Form -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 w-full mb-8">
        <h2 class="text-xl font-bold mb-4">Editar Produto</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= $prodEdit['id'] ?>">
            <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($prodEdit['foto'] ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($prodEdit['nome']) ?>" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Preço (Ex: 15,90)</label>
                    <input type="text" name="preco" value="<?= number_format($prodEdit['preco'], 2, ',', '') ?>" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Categoria</label>
                    <select name="categoria_id" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                        <?php foreach($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $prodEdit['categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Foto (Max 2MB - deixe em branco para manter)</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" onchange="previewImagem(event, 'preview-edit')" class="w-full text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                <textarea name="descricao" rows="3" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500"><?= htmlspecialchars($prodEdit['descricao']) ?></textarea>
            </div>
            
            <div class="flex flex-wrap gap-6 mb-6 pt-2">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="destaque" <?= $prodEdit['destaque'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Destaque</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="esgotado" <?= $prodEdit['esgotado'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Esgotado</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="ativo" <?= $prodEdit['ativo'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Ativo no cardápio</span>
                </label>
            </div>
            
            <div class="flex items-center gap-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">Salvar Alterações</button>
                <div id="preview-edit" class="w-16 h-16 bg-gray-200 rounded overflow-hidden">
                    <?php if($prodEdit['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($prodEdit['foto']) ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>


<!-- Nova Form Modal -->
<div id="modalNova" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold">Novo Produto</h2>
            <button onclick="document.getElementById('modalNova').classList.add('hidden')" class="text-gray-500 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="nova">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
                    <input type="text" name="nome" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="Ex: X-Salada">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Preço (Ex: 15,90)</label>
                    <input type="text" name="preco" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="0,00">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Categoria</label>
                    <select name="categoria_id" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                        <option value="">Selecione...</option>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Foto (Max 2MB)</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" onchange="previewImagem(event, 'preview-novo')" class="w-full text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                <textarea name="descricao" rows="3" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="Ingredientes e detalhes"></textarea>
            </div>
            
            <div class="flex flex-wrap gap-6 mb-6 pt-2">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="destaque" class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Destaque</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="esgotado" class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Esgotado</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="ativo" checked class="form-checkbox h-5 w-5 text-red-600 rounded">
                    <span class="ml-2 text-gray-700 font-bold">Ativo</span>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div id="preview-novo" class="w-16 h-16 bg-gray-100 rounded border border-dashed border-gray-300 overflow-hidden flex items-center justify-center text-xs text-gray-400">Sem Imagem</div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow">Cadastrar Produto</button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm text-left align-middle">
            <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                <tr>
                    <th class="px-6 py-4 w-16">Img</th>
                    <th class="px-6 py-4">Nome</th>
                    <th class="px-6 py-4">Categoria</th>
                    <th class="px-6 py-4">Preço</th>
                    <th class="px-6 py-4 text-center">Badges</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(count($produtos) > 0): ?>
                    <?php foreach ($produtos as $p): ?>
                    <tr class="hover:bg-gray-50 <?= $p['esgotado'] ? 'opacity-70' : '' ?>">
                        <td class="px-6 py-4">
                            <?php if($p['foto']): ?>
                                <img src="../uploads/<?= htmlspecialchars($p['foto']) ?>" class="w-10 h-10 object-cover rounded-md shadow-sm">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-gray-200 rounded-md flex items-center justify-center text-gray-400"><i class="fa-solid fa-utensils"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-800">
                            <?= htmlspecialchars($p['nome']) ?>
                            <br><span class="text-xs text-gray-400 font-normal line-clamp-1 max-w-xs block"><?= htmlspecialchars($p['descricao']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['categoria_nome']) ?></td>
                        <td class="px-6 py-4 font-bold text-gray-800">R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($p['destaque']): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-0.5 rounded mr-1">Destaque</span>
                            <?php endif; ?>
                            <?php if ($p['esgotado']): ?>
                                <span class="bg-gray-200 text-gray-800 text-xs font-bold px-2 py-0.5 rounded">Esgotado</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($p['ativo']): ?>
                                <span class="text-green-500"><i class="fa-solid fa-check-circle" title="Ativo"></i></span>
                            <?php else: ?>
                                <span class="text-red-500"><i class="fa-solid fa-xmark-circle" title="Inativo"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 flex items-center justify-center gap-2 mt-2">
                            <a href="produtos.php?edit=<?= $p['id'] ?>" class="text-blue-500 hover:text-blue-700 p-2"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" onsubmit="return confirm('Excluir produto definitivamente?');" class="inline">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 p-2"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Nenhum produto cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function previewImagem(event, containerId) {
    var output = document.getElementById(containerId);
    if(event.target.files.length > 0) {
        var src = URL.createObjectURL(event.target.files[0]);
        output.innerHTML = '<img src="' + src + '" class="w-full h-full object-cover rounded">';
    } else {
        output.innerHTML = 'Sem Imagem';
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>
