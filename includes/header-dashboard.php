<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: index.php');
    exit;
}

if (!isset($pagina_atual)) {
    // Detecta a página atual automaticamente pelo nome do arquivo
    $pagina_atual = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<!-- HEADER/MENU FIXO EASY RAKE - DASHBOARD PRINCIPAL -->
<link rel="stylesheet" href="css/components/_header.css">
<header class="header">
    <div class="header__container">
        <div class="header__logo" onclick="window.location.href='abrir-caixa.php'" style="cursor: pointer;">EASY RAKE</div>
        <nav class="header__nav">
            <a href="abrir-caixa.php" class="header__link<?= $pagina_atual === 'abrir-caixa' ? ' active' : '' ?>">Abrir Caixa</a>
            <a href="relatorios.php" class="header__link<?= $pagina_atual === 'relatorios' ? ' active' : '' ?>">Relatórios</a>
            <a href="jogadores.php" class="header__link<?= $pagina_atual === 'jogadores' ? ' active' : '' ?>">Jogadores</a>
            <a href="aprovacoes.php" class="header__link<?= $pagina_atual === 'aprovacoes' ? ' active' : '' ?>">Aprovações</a>
        </nav>
        <div class="header__user">
            <button class="header__logout" onclick="window.location.href='api/auth/logout.php'">Sair</button>
        </div>
        <button class="header__menu-btn" id="headerMenuBtn" aria-label="Abrir menu">&#9776;</button>
    </div>
    <nav class="header__nav-mobile" id="headerNavMobile">
        <a href="abrir-caixa.php" class="header__link<?= $pagina_atual === 'abrir-caixa' ? ' active' : '' ?>">Abrir Caixa</a>
        <a href="relatorios.php" class="header__link<?= $pagina_atual === 'relatorios' ? ' active' : '' ?>">Relatórios</a>
        <a href="jogadores.php" class="header__link<?= $pagina_atual === 'jogadores' ? ' active' : '' ?>">Jogadores</a>
        <a href="aprovacoes.php" class="header__link<?= $pagina_atual === 'aprovacoes' ? ' active' : '' ?>">Aprovações</a>
        <button class="header__logout-mobile" onclick="window.location.href='api/auth/logout.php'">Sair</button>
    </nav>
</header>
<script src="js/features/header.js"></script>