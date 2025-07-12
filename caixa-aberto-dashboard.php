<?php
session_start();
$id_caixa = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id_caixa) {
    echo '<h2>Caixa não encontrado.</h2>';
    exit;
}
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'fechamento';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Caixa #<?php echo $id_caixa; ?> | EASY RAKE</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/_dashboard.css">
    <link rel="stylesheet" href="css/pages/_caixa-dashboard.css">
    <link rel="stylesheet" href="css/pages/_fechamento.css">
    <link rel="stylesheet" href="css/pages/_fichas.css">
    <link rel="stylesheet" href="css/pages/_gastos.css">
    <link rel="stylesheet" href="css/pages/_caixinhas.css">
    <link rel="stylesheet" href="css/pages/_inventario.css">

    <link rel="stylesheet" href="css/components/_buttons.css">
    <style>
        .caixa-dashboard-header {
            margin-bottom: 2rem;
        }
        .caixa-dashboard-id {
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1.1rem;
            margin-left: 1rem;
        }
        .caixa-dashboard-menu {
            display: flex;
            gap: 1.2rem;
            justify-content: center;
            align-items: center;
            background: var(--surface-color);
            border-radius: 10px;
            margin-bottom: 2.2rem;
            padding: 0.7rem 0.5rem;
        }
        .caixa-dashboard-menu .tab-btn {
            background: none;
            border: none;
            color: var(--secondary-text-color);
            font-size: 1.08rem;
            font-weight: 600;
            padding: 0.7rem 1.3rem;
            border-radius: 7px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .caixa-dashboard-menu .tab-btn.active, .caixa-dashboard-menu .tab-btn:hover {
            background: var(--accent-gradient);
            color: #fff;
        }
        @media (max-width: 768px) {
            .caixa-dashboard-menu {
                display: none;
            }
            #caixa-dashboard-mobile-menu {
                display: flex !important;
            }
        }
        @media (min-width: 769px) {
            #caixa-dashboard-mobile-menu {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'includes/header-caixa-aberto.php'; ?>
        <main id="main-content" class="dashboard-main">
            <div class="content-container">
                <?php
                if ($tab === 'fechamento') {
                    include 'includes/fechamento.php';
                } else if ($tab === 'rake') {
                    include 'includes/rake.php';
                } else if ($tab === 'fichas') {
                    include 'includes/fichas.php';
                } else if ($tab === 'caixinhas') {
                    include 'includes/caixinhas.php';
                } else if ($tab === 'gastos') {
                    include 'includes/gastos.php';
                } else if ($tab === 'jogadores') {
                    include 'includes/jogadores-caixa.php';
                } else if ($tab === 'inventario') {
                    include 'includes/inventario.php';
                }
                ?>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    window.CAIXA_ID = <?php echo $id_caixa; ?>;
    </script>
    <script src="js/features/caixa-dashboard/caixa-aberto-dashboard.js"></script>
    <script src="js/features/caixa-dashboard/fechamento.js"></script>
    <script src="js/features/caixa-dashboard/fichas.js"></script>
    <script src="js/features/caixa-dashboard/gastos.js"></script>
    <script src="js/features/caixa-dashboard/caixinhas.js"></script>
    <script src="js/features/caixa-dashboard/inventario.js"></script>
    <script src="js/features/notifications.js"></script>
</body>
</html>