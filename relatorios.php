<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco de dados
require_once 'api/db_connect.php';

// Busca informações do usuário logado
$user_id = $_SESSION['user_id'];
$nome_usuario = $_SESSION['nome_usuario'];
$perfil_usuario = $_SESSION['perfil'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EASY RAKE - Relatórios</title>
    <link rel="stylesheet" href="css/main.css">
    <!-- Importação do CSS específico da página de relatórios -->
    <link rel="stylesheet" href="css/pages/_relatorios.css">
</head>
<body>
<div class="app-container">
            <?php include 'includes/header-dashboard.php'; ?>
    <!-- conteúdo principal -->
    <main id="main-content" class="dashboard-main">
        <div class="content-container">
            <div class="card-box section">
                <h2>Relatórios</h2>

                <!-- Calendário de Histórico de Relatórios -->
                <div class="calendar-container">
                    <div class="calendar-history" id="calendar-history"></div>
                </div>

                <div class="reports-grid">
                    <div class="report-card">
                        <h3>Relatório de Caixas</h3>
                        <p>Visualize relatórios detalhados de todos os caixas</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                    <div class="report-card">
                        <h3>Relatório de Jogadores</h3>
                        <p>Estatísticas e informações sobre jogadores</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                    <div class="report-card">
                        <h3>Relatório Financeiro</h3>
                        <p>Análise financeira e movimentações</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                    <div class="report-card">
                        <h3>Relatório de Aprovações</h3>
                        <p>Histórico e status de aprovações</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                    <div class="report-card">
                        <h3>Relatório de Operadores</h3>
                        <p>Performance e atividades dos operadores</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                    <div class="report-card">
                        <h3>Relatório de Movimentações</h3>
                        <p>Movimentações financeiras detalhadas</p>
                        <button class="button">GERAR RELATÓRIO</button>
                    </div>
                </div>

                <!-- Histórico de Relatórios Gerados -->
                <div class="card-box section" style="display:none">
                    <h2>Histórico de Relatórios Gerados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5">Nenhum relatório gerado ainda</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
            <?php include 'includes/footer.php'; ?>
</div>
    <script>
        function logout() {
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = 'api/auth/logout.php';
            }
        }
    </script>
    <script src="js/features/dashboard.js"></script>
    <script src="js/features/calendar-history.js"></script>
    <script src="js/features/notifications.js"></script>
    <script>
    // --- INTEGRAÇÃO AJAX DOS CARDS DE RELATÓRIOS ---
    document.addEventListener('DOMContentLoaded', function() {
        // Carregar histórico de relatórios ao abrir a página
        carregarHistoricoRelatorios();

        // Adicionar evento aos botões de gerar relatório
        document.querySelectorAll('.report-card .button').forEach((btn, idx) => {
            btn.addEventListener('click', function() {
                const tipos = [
                    'caixas', 'jogadores', 'financeiro', 'aprovacoes', 'operadores', 'movimentacoes'
                ];
                const tipo = tipos[idx] || 'caixas';
                btn.disabled = true;
                btn.textContent = 'Gerando...';
                // Placeholder: simula geração de relatório
                setTimeout(() => {
                    btn.disabled = false;
                    btn.textContent = 'GERAR RELATÓRIO';
                    alert('Relatório de ' + tipo + ' gerado com sucesso! (placeholder)');
                    carregarHistoricoRelatorios();
                }, 1200);
                // Aqui você pode implementar chamada real para geração
            });
        });
    });

    function carregarHistoricoRelatorios() {
        // Parâmetros de exemplo: mês e ano atuais
        const data = new Date();
        const ano = data.getFullYear();
        const mes = data.getMonth() + 1;
        fetch(`api/relatorios/get_historico.php?ano=${ano}&mes=${mes}`)
            .then(r => r.json())
            .then(json => {
                if (!json || json.success === false) {
                    mostrarErroHistorico(json && json.message ? json.message : 'Erro ao carregar histórico.');
                    return;
                }
                renderizarHistorico(json);
            })
            .catch(() => mostrarErroHistorico('Erro de conexão ao buscar histórico.'));
    }

    function renderizarHistorico(relatorios) {
        const tabela = document.querySelector('.card-box.section + .card-box.section .data-table tbody');
        if (!tabela) return;
        tabela.innerHTML = '';
        let vazio = true;
        Object.keys(relatorios).forEach(dia => {
            relatorios[dia].forEach(r => {
                vazio = false;
                tabela.innerHTML += `<tr>
                    <td>${r.id}</td>
                    <td>${r.tipo}</td>
                    <td>${dia} ${r.hora}</td>
                    <td>${'Você'}</td>
                    <td>${r.arquivo ? `<a href="${r.arquivo}" target="_blank">Baixar</a>` : (r.mensagem_erro ? 'Erro' : 'Pendente')}</td>
                </tr>`;
            });
        });
        if (vazio) {
            tabela.innerHTML = '<tr><td colspan="5">Nenhum relatório gerado ainda</td></tr>';
        }
        // Exibe a seção de histórico
        const sec = document.querySelectorAll('.card-box.section')[1];
        if (sec) sec.style.display = '';
    }

    function mostrarErroHistorico(msg) {
        const tabela = document.querySelector('.card-box.section + .card-box.section .data-table tbody');
        if (tabela) tabela.innerHTML = `<tr><td colspan='5' style='color:red;'>${msg}</td></tr>`;
        const sec = document.querySelectorAll('.card-box.section')[1];
        if (sec) sec.style.display = '';
    }
    </script>
    <style>
    /* Removido: CSS da grid e dos cards de relatórios agora está em css/pages/_relatorios.css */
    </style>
</body>
</html> 