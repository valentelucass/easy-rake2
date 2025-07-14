<?php
$id_caixa = isset($_GET['id']) ? intval($_GET['id']) : 0;
$perfil_usuario = $_SESSION['perfil'] ?? '';
?>
<!-- HEADER/MENU FIXO EASY RAKE - CAIXA ABERTO -->
<link rel="stylesheet" href="css/components/_header.css">
<header class="header header--caixa-aberto">
    <div class="header__container">
        <div class="header__logo" onclick="window.location.href='abrir-caixa.php'" style="cursor: pointer;">EASY RAKE</div>
        <nav class="header__nav-caixa-menu">
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=fechamento" class="header__caixa-btn">FECHAMENTO</a>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=rake" class="header__caixa-btn">RAKE</a>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=fichas" class="header__caixa-btn">FICHAS</a>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=caixinhas" class="header__caixa-btn">CAIXINHAS</a>
            <?php if (in_array($perfil_usuario, ['Gestor', 'Caixa'])): ?>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=gastos" class="header__caixa-btn">GASTOS</a>
            <?php endif; ?>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=jogadores" class="header__caixa-btn">JOGADORES</a>
            <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=inventario" class="header__caixa-btn">INVENTÁRIO</a>
        </nav>
        <button class="header__menu-btn" id="headerMenuBtn" aria-label="Abrir menu">&#9776;</button>
    </div>
    <nav class="header__nav-mobile" id="headerNavMobile">
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=fechamento" class="header__link">FECHAMENTO</a>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=rake" class="header__link">RAKE</a>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=fichas" class="header__link">FICHAS</a>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=caixinhas" class="header__link">CAIXINHAS</a>
        <?php if (in_array($perfil_usuario, ['Gestor', 'Caixa'])): ?>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=gastos" class="header__link">GASTOS</a>
        <?php endif; ?>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=jogadores" class="header__link">JOGADORES</a>
        <a href="caixa-aberto-dashboard.php?id=<?= $id_caixa ?>&tab=inventario" class="header__link">INVENTÁRIO</a>
    </nav>
</header>
<script src="js/features/header.js"></script> 