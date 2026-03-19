    <!-- Modal do Carrinho -->
    <div id="cartModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-end transform transition-transform duration-300 translate-x-full">
        <div class="bg-white w-full max-w-md h-full shadow-2xl flex flex-col">
            <!-- Header do Carrinho -->
            <div class="p-4 bg-primary text-white flex justify-between items-center shadow-md">
                <h2 class="text-xl font-bold"><i class="fa-solid fa-basket-shopping mr-2"></i> Meu Pedido</h2>
                <button type="button" aria-label="Fechar" onclick="toggleCart()" class="text-white hover:text-gray-200">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>
            
            <!-- Items -->
            <div class="p-4 flex-1 overflow-y-auto bg-gray-50" id="cartItems">
                <!-- Itens serão renderizados via JS -->
            </div>
            
            <!-- Footer Carrinho -->
            <div class="p-4 bg-white border-t border-gray-200 flex flex-col gap-3">
                <div class="flex justify-between items-center font-bold text-lg text-gray-800">
                    <span>Total:</span>
                    <span id="cartTotal">R$ 0,00</span>
                </div>
                
                <input type="text" id="clienteNome" placeholder="Seu nome (Opcional)" class="w-full p-2 border border-gray-300 rounded focus:ring-primary focus:border-primary text-sm">
                
                <button onclick="finalizarPedido()" class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 rounded shadow-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Fazer Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Carrinho Flutuante (Icone) -->
    <div id="btnCartFloating" class="fixed bottom-6 right-6 z-40">
        <button onclick="toggleCart()" class="bg-primary text-white p-4 rounded-full shadow-xl hover:scale-105 transition-transform duration-200 relative group flex items-center justify-center">
            <i class="fa-solid fa-basket-shopping text-2xl"></i>
            <span id="cartBadge" class="absolute -top-2 -right-2 bg-yellow-400 text-gray-900 font-bold text-xs px-2 py-1 rounded-full animate-bounce">0</span>
        </button>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 pointer-events-none z-50 text-sm whitespace-nowrap">
        Item adicionado!
    </div>

    <script>
        // Variável global pegando o id da mesa da URL (se houver)
        const mesaGlobal = '<?= isset($_GET["mesa"]) ? htmlspecialchars($_GET["mesa"]) : "" ?>';
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
