// JS para menu mobile do header
(function() {
    const btn = document.getElementById('headerMenuBtn');
    const navMobile = document.getElementById('headerNavMobile');
    if (!btn || !navMobile) return;
    btn.addEventListener('click', function() {
        navMobile.style.display = navMobile.style.display === 'flex' ? 'none' : 'flex';
    });
    // Fecha ao clicar fora
    document.addEventListener('click', function(e) {
        if (!navMobile.contains(e.target) && e.target !== btn) {
            navMobile.style.display = 'none';
        }
    });
    // Fecha ao redimensionar
    window.addEventListener('resize', function() {
        if (window.innerWidth > 700) {
            navMobile.style.display = 'none';
        }
    });
})(); 