<?php
require 'config.php';
include 'includes/header.php';

$mesa_num = isset($_GET['mesa']) ? htmlspecialchars($_GET['mesa']) : null;
$search = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Buscar mesas ativas para validar
$mesa_id = null;
if ($mesa_num) {
    $stmtMesa = $pdo->prepare("SELECT id FROM mesas WHERE numero = ? AND ativo = 1");
    $stmtMesa->execute([$mesa_num]);
    $mesa_id = $stmtMesa->fetchColumn();
}

// Buscar Categorias Ativas
$stmtCat = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
$categorias = $stmtCat->fetchAll();

// Buscar Produtos
if ($search) {
    $stmtProd = $pdo->prepare("SELECT * FROM produtos WHERE ativo = 1 AND nome LIKE ? ORDER BY categoria_id ASC, nome ASC");
    $stmtProd->execute(['%' . $search . '%']);
} else {
    $stmtProd = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY categoria_id ASC, nome ASC");
}
$produtos = $stmtProd->fetchAll();

// Agrupar produtos por categoria
$produtosPorCat = [];
foreach ($produtos as $p) {
    if (!isset($produtosPorCat[$p['categoria_id']])) {
        $produtosPorCat[$p['categoria_id']] = [];
    }
    $produtosPorCat[$p['categoria_id']][] = $p;
}
?>

<!-- Banner e Header do Restaurante -->
<?php 
    $headerStyle = "bg-primary";
    $temBanner = !empty($restaurante['banner']) && empty($restaurante['ocultar_banner']);
    if ($temBanner) {
        $bannerUrl = htmlspecialchars($restaurante['banner']);
        $headerStyle = "bg-gray-900 bg-center bg-cover bg-no-repeat";
    }
?>
<div class="<?= $headerStyle ?> text-white py-12 md:py-16 min-h-[400px] md:min-h-[500px] flex items-center shadow-md relative overflow-hidden" 
     <?php if($temBanner): ?> style="background-image: url('uploads/<?= $bannerUrl ?>');" <?php endif; ?>>
    
    <!-- Efeito pattern (só se não tiver banner) -->
    <?php if(!$temBanner): ?>
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSI+PC9yZWN0Pgo8cGF0aCBkPSJNMCAwTDggOFoiIHN0cm9rZT0iI2ZmZiIHN0cm9rZS13aWR0aD0iMSI+PC9wYXRoPjwvc3ZnPg==')]"></div>
    <?php else: ?>
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <?php endif; ?>
    
    <div class="container mx-auto px-4 flex flex-col items-center relative z-10">
        <?php if(empty($restaurante['ocultar_logo'])): ?>
            <?php if (!empty($restaurante['logo'])): ?>
                <img src="uploads/<?= htmlspecialchars($restaurante['logo']) ?>" alt="Logo" class="w-24 h-24 rounded-full border-4 border-white shadow-lg mb-3 object-cover bg-white">
            <?php else: ?>
                <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg mb-3 bg-white flex items-center justify-center text-primary text-3xl font-bold">
                    <?= substr($restaurante['nome'], 0, 1) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(empty($restaurante['ocultar_nome'])): ?>
            <h1 class="text-2xl font-bold tracking-wide text-center mt-2"><?= htmlspecialchars($restaurante['nome']) ?></h1>
        <?php endif; ?>
        
        <?php if ($mesa_num && $mesa_id): ?>
            <span class="bg-white text-primary text-xs font-bold px-3 py-1 rounded-full mt-3 shadow">Mesa <?= $mesa_num ?></span>
        <?php elseif ($mesa_num): ?>
            <span class="bg-red-900 text-white text-xs font-bold px-3 py-1 rounded-full mt-3">Mesa não cadastrada</span>
        <?php endif; ?>
        
        <?php if(!empty($restaurante['mensagem_boasvindas']) && empty($restaurante['ocultar_mensagem'])): ?>
            <p class="text-sm mt-3 opacity-90 text-center max-w-md px-2 bg-black bg-opacity-30 rounded-lg py-1"><?= htmlspecialchars($restaurante['mensagem_boasvindas']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container mx-auto px-4 mt-6">
    <!-- Busca -->
    <form class="relative mb-6" method="GET" action="cardapio.php">
        <?php if ($mesa_num): ?>
            <input type="hidden" name="mesa" value="<?= $mesa_num ?>">
        <?php endif; ?>
        <input type="text" name="busca" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar pratos ou bebidas..." class="w-full pl-5 pr-12 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm text-sm">
        <button type="submit" class="absolute right-2 top-1.5 bottom-1.5 w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-colors">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    <!-- Filtro de Categorias -->
    <?php if (empty($search) && count($categorias) > 0): ?>
    <div class="flex overflow-x-auto gap-3 pb-2 mb-6 hide-scrollbar snap-x">
        <?php foreach ($categorias as $cat): ?>
            <a href="#cat-<?= $cat['id'] ?>" class="snap-start shrink-0 bg-white border border-gray-200 px-4 py-2 rounded-full text-sm font-medium text-gray-700 shadow-sm hover:border-primary hover:text-primary transition-colors flex items-center gap-2">
                <?php if ($cat['icone']): ?>
                    <i class="<?= htmlspecialchars($cat['icone']) ?> text-primary"></i>
                <?php endif; ?>
                <?= htmlspecialchars($cat['nome']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Lista de Produtos -->
    <?php if (!empty($search)): ?>
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Resultados para: "<?= htmlspecialchars($search) ?>"</h2>
        <?php if (count($produtos) == 0): ?>
            <p class="text-gray-500 text-center py-10">Nenhum produto encontrado.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php 
    // Mapear nome da categoria
    $catMap = [];
    foreach ($categorias as $c) {
        $catMap[$c['id']] = $c;
    }

    foreach ($produtosPorCat as $catId => $prods): 
        $catInfo = $catMap[$catId] ?? null;
        if (!$catInfo) continue; // se não achar a categoria no mapeamento, ignora (categoria inativa?)
    ?>
    <section id="cat-<?= $catId ?>" class="mb-8 pt-4">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <?php if ($catInfo['icone']): ?>
                <i class="<?= htmlspecialchars($catInfo['icone']) ?> text-primary"></i>
            <?php endif; ?>
            <?= htmlspecialchars($catInfo['nome']) ?>
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($prods as $p): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-transform hover:-translate-y-1 <?= $p['esgotado'] ? 'card-esgotado' : '' ?>">
                    <!-- Imagem -->
                    <div class="relative w-full pb-[75%] bg-gray-200"> <!-- Ratio 4:3 -->
                        <?php if ($p['foto']): ?>
                            <img src="uploads/<?= $p['foto'] ?>" alt="<?= htmlspecialchars($p['nome']) ?>" class="absolute inset-0 w-full h-full object-cover <?= $p['esgotado'] ? 'img-esgotado' : '' ?>">
                        <?php else: ?>
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-utensils text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($p['esgotado']): ?>
                            <div class="absolute inset-0 bg-gray-900 bg-opacity-40 flex items-center justify-center">
                                <span class="bg-gray-800 text-white font-bold px-3 py-1 rounded text-sm uppercase tracking-wider">Esgotado</span>
                            </div>
                        <?php elseif ($p['destaque']): ?>
                            <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded shadow">Destaque</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info -->
                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-gray-800 text-lg leading-tight flex-1 mr-2"><?= htmlspecialchars($p['nome']) ?></h3>
                            <span class="font-bold text-primary whitespace-nowrap">R$ <?= number_format($p['preco'], 2, ',', '.') ?></span>
                        </div>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?= nl2br(htmlspecialchars($p['descricao'])) ?></p>
                        
                        <div class="mt-auto flex gap-2">
                            <?php if (!$p['esgotado']): ?>
                            <div class="flex items-center border border-gray-300 rounded overflow-hidden">
                                <button type="button" onclick="this.nextElementSibling.stepDown()" class="px-3 bg-gray-50 text-gray-600 hover:bg-gray-100 py-2">-</button>
                                <input type="number" id="qtd-<?= $p['id'] ?>" value="1" min="1" max="99" class="w-10 text-center font-bold outline-none no-spin bg-white text-sm" readonly>
                                <button type="button" onclick="this.previousElementSibling.stepUp()" class="px-3 bg-gray-50 text-gray-600 hover:bg-gray-100 py-2">+</button>
                            </div>
                            <!-- Botão Adicionar -->
                            <button onclick="adicionarCarrinho(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nome'])) ?>', <?= $p['preco'] ?>, 'qtd-<?= $p['id'] ?>')" class="flex-1 bg-primary hover:bg-secondary text-white font-semibold rounded py-2 transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                            <?php else: ?>
                            <button disabled class="w-full bg-gray-300 text-gray-500 font-semibold rounded py-2 cursor-not-allowed">
                                Indisponível
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</div>

<style>
/* Remove setas de input number */
.no-spin::-webkit-inner-spin-button,
.no-spin::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.no-spin {
    -moz-appearance: textfield;
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<?php include 'includes/footer.php'; ?>
