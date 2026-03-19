let carrinho = JSON.parse(localStorage.getItem('carrinho_menuzap')) || [];
const mesaAtual = typeof mesaGlobal !== 'undefined' ? mesaGlobal : '';

// Atualizar interface no load
document.addEventListener('DOMContentLoaded', () => {
    atualizarCarrinhoVisual();
});

// Adicionar produto
function adicionarCarrinho(id, nome, preco, inputId) {
    const qtyInput = document.getElementById(inputId);
    let qtd = 1;
    if (qtyInput) {
        qtd = parseInt(qtyInput.value) || 1;
    }

    const itemStr = { id: id, nome: nome, preco: parseFloat(preco), qtd: qtd, obs: '' };
    
    // Verificar se já tem no carrinho
    const index = carrinho.findIndex(i => i.id == id);
    if (index > -1) {
        carrinho[index].qtd += qtd;
    } else {
        carrinho.push(itemStr);
    }

    salvarCarrinho();
    atualizarCarrinhoVisual();
    mostrarToast(nome + ' adicionado!');
}

function salvarCarrinho() {
    localStorage.setItem('carrinho_menuzap', JSON.stringify(carrinho));
}

function atualizarCarrinhoVisual() {
    const cartBadge = document.getElementById('cartBadge');
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    
    if(!cartBadge || !cartItems || !cartTotal) return;

    let totalItens = 0;
    let valorTotal = 0;
    
    cartItems.innerHTML = '';

    if (carrinho.length === 0) {
        cartItems.innerHTML = '<div class="text-center text-gray-400 mt-10"><i class="fa-solid fa-cart-shopping text-4xl mb-4"></i><p>Seu carrinho está vazio.</p></div>';
    } else {
        carrinho.forEach((item, index) => {
            totalItens += item.qtd;
            valorTotal += (item.preco * item.qtd);
            
            cartItems.innerHTML += `
                <div class="bg-white p-3 rounded shadow-sm mb-3 border border-gray-100 flex flex-col gap-2 relative">
                    <button onclick="removerItem(${index})" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <div class="font-semibold text-gray-800 pr-6">${item.nome}</div>
                    
                    <div class="flex justify-between items-center mt-1">
                        <div class="flex items-center gap-3 border rounded px-2 py-1 select-none">
                            <button onclick="alterarQtd(${index}, -1)" class="text-gray-500 hover:text-primary px-1">-</button>
                            <span class="font-medium text-sm w-4 text-center">${item.qtd}</span>
                            <button onclick="alterarQtd(${index}, 1)" class="text-gray-500 hover:text-primary px-1">+</button>
                        </div>
                        <div class="font-bold text-primary text-sm">
                            R$ ${(item.preco * item.qtd).toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                    <div>
                        <input type="text" placeholder="Alguma observação? (ex: sem cebola)" 
                            class="w-full text-xs p-1 border-b border-gray-200 mt-1 focus:outline-none focus:border-primary"
                            value="${item.obs}" onchange="atualizarObs(${index}, this.value)">
                    </div>
                </div>
            `;
        });
    }

    cartBadge.innerText = totalItens;
    cartTotal.innerText = 'R$ ' + valorTotal.toFixed(2).replace('.', ',');
}

function alterarQtd(index, variacao) {
    if (carrinho[index]) {
        carrinho[index].qtd += variacao;
        if (carrinho[index].qtd <= 0) {
            carrinho.splice(index, 1);
        }
        salvarCarrinho();
        atualizarCarrinhoVisual();
    }
}

function removerItem(index) {
    carrinho.splice(index, 1);
    salvarCarrinho();
    atualizarCarrinhoVisual();
}

function atualizarObs(index, valor) {
    if (carrinho[index]) {
        carrinho[index].obs = valor;
        salvarCarrinho();
    }
}

function toggleCart() {
    const modal = document.getElementById('cartModal');
    if (modal.classList.contains('show')) {
        modal.classList.remove('show');
    } else {
        modal.classList.add('show');
        atualizarCarrinhoVisual();
    }
}

function mostrarToast(msg) {
    const toast = document.getElementById('toast');
    if(toast) {
        toast.innerText = msg;
        toast.classList.replace('opacity-0', 'opacity-100');
        setTimeout(() => {
            toast.classList.replace('opacity-100', 'opacity-0');
        }, 3000);
    }
}

function finalizarPedido() {
    if (carrinho.length === 0) {
        alert("Adicione itens ao carrinho primeiro!");
        return;
    }

    const clienteNome = document.getElementById('clienteNome') ? document.getElementById('clienteNome').value : '';
    
    // Dados a serem enviados
    const payload = {
        mesa: mesaAtual,
        cliente: clienteNome,
        itens: carrinho
    };

    fetch('api_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            carrinho = [];
            salvarCarrinho();
            // Redirecionar
            window.location.href = `pedido_finalizado.php?id=${data.pedido_id}&mesa=${mesaAtual}`;
        } else {
            alert("Erro ao fazer pedido: " + (data.mensagem || 'Desconhecido'));
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erro de conexão ao enviar pedido.");
    });
}
