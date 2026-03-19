<?php
require 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['itens'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Carrinho vazio.']);
    exit;
}

$mesa_num = !empty($data['mesa']) ? $data['mesa'] : null;
$cliente = !empty($data['cliente']) ? $data['cliente'] : null;
$itens = $data['itens'];

$mesa_id = null;
if ($mesa_num) {
    $stmt = $pdo->prepare("SELECT id FROM mesas WHERE numero = ? AND ativo = 1");
    $stmt->execute([$mesa_num]);
    $mesa_id = $stmt->fetchColumn() ?: null;
}

try {
    $pdo->beginTransaction();

    $total = 0;
    foreach ($itens as $item) {
        $total += $item['preco'] * $item['qtd'];
    }

    $stmtPedido = $pdo->prepare("INSERT INTO pedidos (mesa_id, cliente_nome, status, total) VALUES (?, ?, 'recebido', ?)");
    $stmtPedido->execute([$mesa_id, $cliente, $total]);
    $pedido_id = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, observacao, preco_unitario) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($itens as $item) {
        $stmtItem->execute([
            $pedido_id,
            $item['id'],
            $item['qtd'],
            $item['obs'] ?? null,
            $item['preco']
        ]);
    }

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'pedido_id' => $pedido_id]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar pedido: ' . $e->getMessage()]);
}
