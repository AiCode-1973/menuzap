# MenuZap - Sistema de Cardápio Digital

Sistema completo de cardápio digital para restaurantes com painel administrativo e de garçom em PHP, MySQL e Tailwind CSS.

## 🚀 Estrutura de Pastas e Arquivos

* `/admin/`: Painel de controle para gerentes/donos. CRUDs e acompanhamento.
* `/garcom/`: Interface simplificada e responsiva para garçons atualizarem o status dos pedidos.
* `/includes/`: Componentes reutilizáveis (header, footer, modais).
* `/assets/`: Scripts JS, CSS e recursos estáticos.
* `/uploads/`: Imagens dos produtos e logotipo do restaurante armazenadas aqui.
* `config.php`: Arquivo central de configurações e conexão com o banco de dados.
* `database.sql`: Estrutura do banco de dados (tabelas e setup inicial).
* `instalar.php`: Script utilitário para importar o banco automaticamente durante a instalação.
* `cardapio.php`: Tela pública mobile-first para clientes visualizarem os produtos e pedirem.

## ⚙️ Instalação e Configuração

**1. Pré-Requisitos**
* Servidor Web configurado (XAMPP/WAMP para local ou Apache/Nginx para servidor de produção).
* PHP 7.4 ou superior (com as extensões PDO, json habilitadas).
* Banco de dados MySQL / MariaDB.

**2. Configuração do Banco de Dados**
O arquivo `config.php` já está preenchido com as credenciais remotas fornecidas:
```php
$host = '186.209.113.107';
$user = 'dema5738_menuzap';
$pass = 'Dema@1973';
$dbname = 'dema5738_menuzap';
```

**3. Primeira Execução (Criação das Tabelas)**
Abra no navegador a URL onde foi hospedado o sistema com `/instalar.php` no final.
Exemplo: `http://localhost/menuzap/instalar.php`
* _Este arquivo vai ler o conteúdo de `database.sql` e criar todas as tabelas e usuários padrão. Em seguida, exclua o arquivo instalar.php por segurança._

**4. Acesso ao Painel Admin**
Acesse o sistema na URL: `http://localhost/menuzap/admin`
* E-mail: `admin@admin.com`
* Senha: `admin123`

_Recomenda-se trocar esta senha imediatamente após o primeiro login ou adicionar novos usuários diretamente no seu MySQL pela tabela `usuarios` com senhas em BCrypt._

## 📱 Utilizando o Sistema
1. Acesse o menu **Configurações** para adicionar sua logomarca, alterar o nome do restaurante e escolher um tema de cores.
2. Cadastre **Categorias** (ex: Pizzas, Bebidas, Sobremesas).
3. Cadastre os **Produtos** associando-os às categorias e subindo suas imagens.
4. Cadastre suas **Mesas** no menu de Mesas e gere os **QR Codes**.
5. Imprima os QR Codes e posicione nas mesas de seu restaurante!

> O cliente aponta o celular para o QR Code (ex: `cardapio.php?mesa=15`), abre o cardápio com as cores da loja, adiciona itens no carrinho floating e finaliza! O pedido apita automaticamente na tela `Pedidos` do admin e também na tela `Painel do Garçom` (auto-refresh de 30s).
