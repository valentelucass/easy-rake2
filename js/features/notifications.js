// Função centralizada para notificações
function showNotification(title, message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    notification.innerHTML = `
        <h4>${title}</h4>
        <p>${message}</p>
        <button onclick="this.parentElement.remove()" class="notification-close">&times;</button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        if (notification.parentElement) notification.remove();
    }, 5000);
}

// Função centralizada para feedback de exportação
function showExportFeedback(tipo, status, mensagem) {
    const feedback = document.createElement('div');
    feedback.className = `export-feedback ${status}`;
    feedback.textContent = `${tipo} ${status === 'success' ? 'gerado' : 'erro'} ${mensagem}`;
    document.body.appendChild(feedback);
    setTimeout(() => feedback.classList.add('show'), 100);
    setTimeout(() => {
        feedback.classList.remove('show');
        setTimeout(() => document.body.removeChild(feedback), 300);
    }, 3000);
}

// Exporta para uso global
window.showNotification = showNotification;
window.showExportFeedback = showExportFeedback; 