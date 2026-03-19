<?php
require '../config.php';
checkAdmin();
include '../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? 'salvar';
    
    if ($acao == 'excluir_logo') {
        $stmt = $pdo->query("SELECT logo FROM restaurante LIMIT 1");
        $logoDel = $stmt->fetchColumn();
        if ($logoDel && file_exists('../uploads/' . $logoDel)) { unlink('../uploads/' . $logoDel); }
        $pdo->query("UPDATE restaurante SET logo = NULL WHERE id=1");
        redirect('configuracoes.php?msg=sucesso');
    }
    
    if ($acao == 'excluir_banner') {
        $stmt = $pdo->query("SELECT banner FROM restaurante LIMIT 1");
        $bannerDel = $stmt->fetchColumn();
        if ($bannerDel && file_exists('../uploads/' . $bannerDel)) { unlink('../uploads/' . $bannerDel); }
        $pdo->query("UPDATE restaurante SET banner = NULL WHERE id=1");
        redirect('configuracoes.php?msg=sucesso');
    }

    if ($acao == 'salvar') {
        $nome = trim($_POST['nome']);
        $cor_primaria = $_POST['cor_primaria'];
        $cor_secundaria = $_POST['cor_secundaria'];
        $horario = trim($_POST['horario_funcionamento']);
        $mensagem = trim($_POST['mensagem_boasvindas']);
        $ocultar_nome = isset($_POST['ocultar_nome']) ? 1 : 0;
        $ocultar_mensagem = isset($_POST['ocultar_mensagem']) ? 1 : 0;
        $ocultar_logo = isset($_POST['ocultar_logo']) ? 1 : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        // Tratamento Logo
        $logo_atual = $_POST['logo_atual'] ?? null;
        $logo = $logo_atual;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['logo']['size'] <= 2097152) {
                $novo_nome = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/' . $novo_nome)) {
                    $logo = $novo_nome;
                    if ($logo_atual && file_exists('../uploads/' . $logo_atual)) {
                        unlink('../uploads/' . $logo_atual);
                    }
                }
            }
        }
        
        // Tratamento Banner
        $banner_atual = $_POST['banner_atual'] ?? null;
        $banner = $banner_atual;
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['banner']['size'] <= 3145728) {
                $novo_nome = 'banner_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/' . $novo_nome)) {
                    $banner = $novo_nome;
                    if ($banner_atual && file_exists('../uploads/' . $banner_atual)) {
                        unlink('../uploads/' . $banner_atual);
                    }
                }
            }
        }
        
        $stmt = $pdo->prepare("UPDATE restaurante SET nome=?, cor_primaria=?, cor_secundaria=?, horario_funcionamento=?, mensagem_boasvindas=?, ativo=?, logo=?, banner=?, ocultar_nome=?, ocultar_mensagem=?, ocultar_logo=? WHERE id=1");
        $stmt->execute([$nome, $cor_primaria, $cor_secundaria, $horario, $mensagem, $ativo, $logo, $banner, $ocultar_nome, $ocultar_mensagem, $ocultar_logo]);
        redirect('configuracoes.php?msg=sucesso');
    }
}

$stmt = $pdo->query("SELECT * FROM restaurante LIMIT 1");
$rest = $stmt->fetch();
if (!$rest) {
    // Should not happen if SQL inserted default row. fallback:
    $rest = ['nome'=>'Restaurante','cor_primaria'=>'#EF4444','cor_secundaria'=>'#DC2626','horario_funcionamento'=>'','mensagem_boasvindas'=>'','ativo'=>1,'logo'=>null];
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Configurações Gerais</h1>
    <p class="text-gray-500 text-sm mt-1">Personalize a aparência e comportamento do seu cardápio digital.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div id="toastMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 transition-opacity duration-300">
        Configurações salvas com sucesso!
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
    <form method="POST" enctype="multipart/form-data" class="p-6">
        <input type="hidden" name="logo_atual" value="<?= htmlspecialchars($rest['logo'] ?? '') ?>">
        <input type="hidden" name="banner_atual" value="<?= htmlspecialchars($rest['banner'] ?? '') ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-6">
            
            <!-- Coluna 1: Info Base -->
            <div class="lg:col-span-2 space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome do Restaurante</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($rest['nome']) ?>" required class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Horário de Funcionamento</label>
                    <input type="text" name="horario_funcionamento" value="<?= htmlspecialchars($rest['horario_funcionamento']) ?>" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500" placeholder="Ex: Terça a Domingo das 18h às 23h">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mensagem de Boas-vindas</label>
                    <textarea name="mensagem_boasvindas" rows="3" class="w-full border rounded p-2 text-sm focus:outline-none focus:border-red-500"><?= htmlspecialchars($rest['mensagem_boasvindas']) ?></textarea>
                </div>
            </div>
            
            <!-- Coluna 2: Logo e Cores -->
            <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-center mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Logomarca (Max 2MB)</label>
                    <div class="w-24 h-24 mx-auto bg-white rounded-full border-4 border-white shadow-md overflow-hidden flex items-center justify-center mb-2" id="preview-logo">
                        <?php if($rest['logo']): ?>
                            <img src="../uploads/<?= htmlspecialchars($rest['logo']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fa-solid fa-store text-3xl text-gray-300"></i>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" onchange="previewImagem(event, 'preview-logo')" class="w-full text-xs text-gray-600 block mx-auto file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    <?php if($rest['logo']): ?>
                        <button type="submit" name="acao" value="excluir_logo" onclick="return confirm('Deseja realmente excluir sua logomarca atual?');" class="mt-2 text-xs text-red-500 hover:text-red-700 font-bold block mx-auto">
                            <i class="fa-solid fa-trash mr-1"></i> Excluir Logo Atual
                        </button>
                    <?php endif; ?>
                </div>
                
                <hr>
                
                <div class="text-center mb-4 mt-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Capa do Menu (Banner Max 3MB)</label>
                    <div class="w-full h-24 mx-auto bg-white rounded-lg border border-gray-300 shadow-sm overflow-hidden flex items-center justify-center mb-2" id="preview-banner">
                        <?php if($rest['banner']): ?>
                            <img src="../uploads/<?= htmlspecialchars($rest['banner']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fa-solid fa-image text-3xl text-gray-300"></i>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="banner" accept=".jpg,.jpeg,.png,.webp" onchange="previewImagem(event, 'preview-banner')" class="w-full text-xs text-gray-600 block mx-auto file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    <?php if($rest['banner']): ?>
                        <button type="submit" name="acao" value="excluir_banner" onclick="return confirm('Deseja realmente excluir a capa (banner) atual?');" class="mt-2 text-xs text-red-500 hover:text-red-700 font-bold block mx-auto">
                            <i class="fa-solid fa-trash mr-1"></i> Excluir Capa Atual
                        </button>
                    <?php endif; ?>
                </div>
                
                <hr>
                <h3 class="font-bold text-gray-700 text-sm mb-2 mt-4 text-center">Cores do Tema</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 text-xs font-bold mb-1">Primária</label>
                        <input type="color" name="cor_primaria" value="<?= htmlspecialchars($rest['cor_primaria']) ?>" class="w-full h-10 border rounded focus:outline-none focus:border-red-500 cursor-pointer p-0.5">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs font-bold mb-1">Secundária</label>
                        <input type="color" name="cor_secundaria" value="<?= htmlspecialchars($rest['cor_secundaria']) ?>" class="w-full h-10 border rounded focus:outline-none focus:border-red-500 cursor-pointer p-0.5">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="border-t pt-4 mt-6">
            <h3 class="font-bold text-gray-800 text-lg mb-3">Opções de Exibição / Status do Sistema</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <label class="flex items-start cursor-pointer bg-gray-50 p-3 rounded border">
                    <input type="checkbox" name="ativo" <?= $rest['ativo'] ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded mt-0.5">
                    <div class="ml-3">
                        <span class="block text-gray-800 font-bold text-sm">Cardápio Online</span>
                        <span class="text-xs text-gray-500">Permitir acesso de clientes.</span>
                    </div>
                </label>

                <label class="flex items-start cursor-pointer bg-gray-50 p-3 rounded border">
                    <input type="checkbox" name="ocultar_nome" <?= $rest['ocultar_nome'] ?? 0 ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded mt-0.5">
                    <div class="ml-3">
                        <span class="block text-gray-800 font-bold text-sm">Inibir Nome</span>
                        <span class="text-xs text-gray-500">Ocultar título sobre a capa.</span>
                    </div>
                </label>

                <label class="flex items-start cursor-pointer bg-gray-50 p-3 rounded border">
                    <input type="checkbox" name="ocultar_logo" <?= $rest['ocultar_logo'] ?? 0 ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded mt-0.5">
                    <div class="ml-3">
                        <span class="block text-gray-800 font-bold text-sm">Inibir Logo</span>
                        <span class="text-xs text-gray-500">Ocultar o círculo com o logotipo da loja.</span>
                    </div>
                </label>

                <label class="flex items-start cursor-pointer bg-gray-50 p-3 rounded border">
                    <input type="checkbox" name="ocultar_mensagem" <?= $rest['ocultar_mensagem'] ?? 0 ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-red-600 rounded mt-0.5">
                    <div class="ml-3">
                        <span class="block text-gray-800 font-bold text-sm">Inibir Msg</span>
                        <span class="text-xs text-gray-500">Ocultar mensagem de boas-vindas.</span>
                    </div>
                </label>
            </div>
        </div>
        
        <div class="flex">
            <button type="submit" name="acao" value="salvar" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Salvar Configurações
            </button>
        </div>
    </form>
</div>

<script>
function previewImagem(event, containerId) {
    var output = document.getElementById(containerId);
    if(event.target.files.length > 0) {
        var src = URL.createObjectURL(event.target.files[0]);
        output.innerHTML = '<img src="' + src + '" class="w-full h-full object-cover">';
    } else {
        output.innerHTML = '<i class="fa-solid fa-store text-3xl text-gray-300"></i>';
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>
