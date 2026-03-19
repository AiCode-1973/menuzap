<?php
$restaurante = getRestauranteInfo($pdo);
// Definir variáveis de cor CSS inline no root para customização
$cor_primaria = htmlspecialchars($restaurante['cor_primaria'] ?? '#ef4444');
$cor_secundaria = htmlspecialchars($restaurante['cor_secundaria'] ?? '#dc2626');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurante['nome']) ?> - Cardápio Digital</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: 'var(--color-primary)',
                        secondary: 'var(--color-secondary)',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --color-primary: <?= $cor_primaria ?>;
            --color-secondary: <?= $cor_secundaria ?>;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans pb-24">
