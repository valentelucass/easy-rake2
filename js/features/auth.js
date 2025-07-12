// ===== CONTROLE DE VISIBILIDADE DOS FORMULÁRIOS UNIFICADOS =====

// Seleciona os containers de cada formulário
const loginWrapper = document.getElementById('login-wrapper');
const registerUnitWrapper = document.getElementById('register-unit-wrapper');
const registerEmployeeWrapper = document.getElementById('register-employee-wrapper');

// Seleciona os links que disparam a troca
const showRegisterUnitLink = document.getElementById('show-register-unit-form');
const showRegisterEmployeeLink = document.getElementById('show-register-employee-form');
const showLoginLinks = document.querySelectorAll('.show-login-form'); // Pode haver mais de um

// Função para mostrar o formulário de Registro de Unidade
if (showRegisterUnitLink) {
    showRegisterUnitLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginWrapper.classList.add('hidden');
        registerEmployeeWrapper.classList.add('hidden');
        registerUnitWrapper.classList.remove('hidden');
        activateContainerGlow();
    });
}

// Função para mostrar o formulário de Registro de Funcionário
if (showRegisterEmployeeLink) {
    showRegisterEmployeeLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginWrapper.classList.add('hidden');
        registerUnitWrapper.classList.add('hidden');
        registerEmployeeWrapper.classList.remove('hidden');
        activateContainerGlow();
    });
}

// Função para os links de "Voltar para o Login"
showLoginLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        registerUnitWrapper.classList.add('hidden');
        registerEmployeeWrapper.classList.add('hidden');
        loginWrapper.classList.remove('hidden');
        activateContainerGlow();
    });
});

// ===== CONTROLE DO GLOW NEON INTERATIVO =====

const loginContainer = document.querySelector('.login-container');

// Função para ativar o glow do container
function activateContainerGlow() {
    if (loginContainer) {
        loginContainer.classList.add('active');
    }
}

// Função para desativar o glow do container
function deactivateContainerGlow() {
    if (loginContainer) {
        loginContainer.classList.remove('active');
    }
}

// Event listeners para interatividade do glow
if (loginContainer) {
    // Ativa glow quando qualquer elemento dentro do container é focado
    loginContainer.addEventListener('focusin', activateContainerGlow);
    
    // Desativa glow quando não há mais foco no container
    loginContainer.addEventListener('focusout', (e) => {
        // Verifica se o foco saiu completamente do container
        setTimeout(() => {
            if (!loginContainer.contains(document.activeElement)) {
                deactivateContainerGlow();
            }
        }, 100);
    });
    
    // Ativa glow quando há clique no container
    loginContainer.addEventListener('click', activateContainerGlow);
    
    // Desativa glow quando clica fora do container
    document.addEventListener('click', (e) => {
        if (!loginContainer.contains(e.target)) {
            deactivateContainerGlow();
        }
    });
}

// ===== LÓGICA DO FORMULÁRIO DE LOGIN =====

const loginForm = document.getElementById('loginForm');
const loginErrorMessage = document.getElementById('login-error-message');

if (loginForm) {
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Impede o envio padrão do formulário

        // Ativa o glow ao submeter o formulário
        activateContainerGlow();

        // Pega os dados do formulário
        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        // Limpa mensagens de erro anteriores
        loginErrorMessage.style.display = 'none';
        loginErrorMessage.textContent = '';

        try {
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cpf: data.cpf,
                    senha: data.senha
                })
            });

            const result = await response.json();

            if (result.success) {
                // Sucesso! Redireciona para a tela de abertura de caixa
                window.location.href = 'abrir-caixa.php';
            } else {
                // Exibe a mensagem de erro retornada pela API
                loginErrorMessage.textContent = result.message || 'Ocorreu um erro desconhecido.';
                loginErrorMessage.style.display = 'block';
            }

        } catch (error) {
            // Erro de rede ou na comunicação com a API
            console.error('Erro ao tentar fazer login:', error);
            loginErrorMessage.textContent = 'Não foi possível conectar ao servidor. Tente novamente mais tarde.';
            loginErrorMessage.style.display = 'block';
        }
    });
}

// ===== LÓGICA PARA REGISTRO DE NOVA UNIDADE =====

const formNovaUnidade = document.getElementById('formNovaUnidade');
const unitErrorMessage = document.getElementById('unit-error-message');
const unitSuccessMessage = document.getElementById('unit-success-message');

if (formNovaUnidade) {
    formNovaUnidade.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(formNovaUnidade);
        const data = Object.fromEntries(formData.entries());

        // Limpa mensagens
        unitErrorMessage.style.display = 'none';
        unitSuccessMessage.style.display = 'none';

        try {
            const response = await fetch('api/unidades/registrar_unidade.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                formNovaUnidade.style.display = 'none'; // Esconde o formulário
                unitSuccessMessage.innerHTML = `${result.message}<br><strong>Email do gestor: ${result.gestor_email}</strong><br><strong>Código de acesso da unidade: ${result.codigo_acesso}</strong>`;
                unitSuccessMessage.style.display = 'block';
            } else {
                unitErrorMessage.textContent = result.message;
                unitErrorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Erro ao criar unidade:', error);
            unitErrorMessage.textContent = 'Erro de comunicação com o servidor.';
            unitErrorMessage.style.display = 'block';
        }
    });
} 

const formNovoFuncionario = document.getElementById('formNovoFuncionario');
const employeeErrorMessage = document.getElementById('employee-error-message');
const employeeSuccessMessage = document.getElementById('employee-success-message');

if (formNovoFuncionario) {
    formNovoFuncionario.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(formNovoFuncionario);
        const data = Object.fromEntries(formData.entries());

        employeeErrorMessage.style.display = 'none';
        employeeSuccessMessage.style.display = 'none';

        try {
            const response = await fetch('api/usuarios/registrar_funcionario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                formNovoFuncionario.style.display = 'none'; // Esconde o formulário
                employeeSuccessMessage.textContent = result.message;
                employeeSuccessMessage.style.display = 'block';
            } else {
                employeeErrorMessage.textContent = result.message;
                employeeErrorMessage.style.display = 'block';
            }
        } catch (error) {
            employeeErrorMessage.textContent = 'Erro de comunicação com o servidor.';
            employeeErrorMessage.style.display = 'block';
        }
    });
} 

// ===== FUNÇÃO GLOBAL DE LOGOUT =====
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        window.location.href = 'api/auth/logout.php';
    }
}
window.logout = logout; 