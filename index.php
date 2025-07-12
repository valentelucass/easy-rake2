<?php
// Inicia a sessão para verificar se o usuário já está logado
session_start();

// Remover redirecionamento automático para dashboard.php
// O usuário só deve ser redirecionado após login bem-sucedido pelo frontend
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso - Easy Rake</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <main class="login-container">

        <div id="login-wrapper">
            <div class="login-header">
                <h1>Easy Rake</h1>
                <p>Acesse sua unidade</p>
            </div>

            <div id="login-error-message" class="error-message"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha_login" name="senha" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>

            <div class="login-links">
                <p><a href="#" id="show-register-employee-form">É funcionário? Cadastre-se com um código</a></p>
                <p><a href="#" id="show-register-unit-form">Ainda não tem uma unidade? Crie uma aqui</a></p>
            </div>
        </div>

        <div id="register-unit-wrapper" class="hidden">
            <div class="login-header">
                <h1>Crie sua Unidade</h1>
                <p>Seja o Gestor do seu clube. Preencha os dados abaixo.</p>
            </div>

            <div id="unit-success-message" class="success-message" style="display: none;"></div>
            <div id="unit-error-message" class="error-message"></div>

            <form id="formNovaUnidade">
                <div class="form-group">
                    <label for="nome">Nome da Unidade</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="cpf_gestor">CPF do Gestor</label>
                    <input type="text" id="cpf_gestor" name="cpf_gestor" required maxlength="14">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone da Unidade</label>
                    <input type="tel" id="telefone" name="telefone" required>
                </div>
                <hr style="border-color: var(--cor-cinza-medio); margin: 1.5rem 0;">
                <div class="form-group">
                    <label for="nome_gestor">Seu Nome Completo</label>
                    <input type="text" id="nome_gestor" name="nome_gestor" required>
                </div>
                <div class="form-group">
                    <label for="email_gestor">Seu E-mail</label>
                    <input type="email" id="email_gestor" name="email_gestor" required>
                </div>
                <div class="form-group">
                    <label for="senha">Crie uma Senha de Gestor</label>
                    <input type="password" id="senha_gestor" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirme sua Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                <button type="submit" class="btn btn-primary">Criar Unidade e Cadastrar</button>
            </form>

            <div class="login-links">
                <p><a href="#" class="show-login-form">Já tem uma conta? Faça o login</a></p>
            </div>
        </div>

        <div id="register-employee-wrapper" class="hidden">
            <div class="login-header">
                <h1>Cadastro de Funcionário</h1>
                <p>Use o código de acesso fornecido pelo seu gestor.</p>
            </div>
            <div id="employee-success-message" class="success-message" style="display: none;"></div>
            <div id="employee-error-message" class="error-message"></div>
            <form id="formNovoFuncionario">
                <div class="form-group">
                    <label for="codigo_acesso">Código de Acesso da Unidade</label>
                    <input type="text" id="codigo_acesso" name="codigo_acesso" required>
                </div>
                <div class="form-group">
                    <label for="nome_completo_funcionario">Seu Nome Completo</label>
                    <input type="text" id="nome_completo_funcionario" name="nome_completo" required>
                </div>
                <div class="form-group toggle-group">
                    <button type="button" class="toggle-btn" id="btn-tipo-caixa-func" data-value="caixa">Caixa</button>
                    <button type="button" class="toggle-btn" id="btn-tipo-sanger-func" data-value="sanger">Sanger</button>
                    <input type="hidden" name="tipo_usuario" id="tipo_usuario_func_hidden" required>
                </div>
                <div class="form-group">
                    <label for="cpf_funcionario">Seu CPF</label>
                    <input type="text" id="cpf_funcionario" name="cpf" required>
                </div>
                <div class="form-group">
                    <label for="senha_funcionario">Crie sua Senha</label>
                    <input type="password" id="senha_funcionario" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_senha_funcionario">Confirme sua Senha</label>
                    <input type="password" id="confirmar_senha_funcionario" name="confirmar_senha" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar para Aprovação</button>
            </form>
            <div class="login-links">
                <p><a href="#" class="show-login-form">Já tem uma conta? Faça o login</a></p>
            </div>
        </div>

    </main>

    <script src="js/features/auth.js" type="module"></script>
    <script>
// Toggle visual dos botões de tipo de usuário para funcionário
const btnTipoCaixaFunc = document.getElementById('btn-tipo-caixa-func');
const btnTipoSangerFunc = document.getElementById('btn-tipo-sanger-func');
const tipoUsuarioFuncHidden = document.getElementById('tipo_usuario_func_hidden');

function selecionarTipoUsuarioFunc(tipo) {
    if (tipo === 'caixa') {
        btnTipoCaixaFunc.classList.add('active');
        btnTipoSangerFunc.classList.remove('active');
    } else {
        btnTipoSangerFunc.classList.add('active');
        btnTipoCaixaFunc.classList.remove('active');
    }
    tipoUsuarioFuncHidden.value = tipo;
}
btnTipoCaixaFunc.addEventListener('click', () => selecionarTipoUsuarioFunc('caixa'));
btnTipoSangerFunc.addEventListener('click', () => selecionarTipoUsuarioFunc('sanger'));
// Se voltar do erro, manter seleção
if (tipoUsuarioFuncHidden.value) {
    selecionarTipoUsuarioFunc(tipoUsuarioFuncHidden.value);
}
</script>
</body>
</html>