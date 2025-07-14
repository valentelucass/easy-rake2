<?php
// Página da aba Rake - Easy Rake
// Estrutura pronta para integração com backend e JS
?>
<link rel="stylesheet" href="css/pages/_rake.css">
<div class="card-box section">
    <h2>Adicionar Rake Parcial</h2>
    <form id="form-adicionar-rake" class="form-inline" autocomplete="off">
        <input type="number" min="0.01" step="0.01" id="valor-rake" name="valor" placeholder="Digite o valor do rake" required style="max-width:180px; margin-right:1rem;">
        <button type="submit" class="btn btn-primary">Adicionar Rake</button>
    </form>
    <div id="rake-error-msg" class="error-message" style="display:none; margin-top:0.5rem;"></div>
</div>

<div class="card-box section" style="margin-top:2rem;">
    <h3>Registros de Rake</h3>
    <div class="table-container">
        <table class="data-table" id="tabela-rake-registros">
            <thead>
                <tr>
                    <th>Valor (R$)</th>
                    <th>Data e Hora</th>
                    <th>Inserido por</th>
                </tr>
            </thead>
            <tbody>
                <!-- Registros serão preenchidos via JS -->
            </tbody>
        </table>
    </div>
    <div id="rake-total" class="rake-total-destaque" style="margin-top:1.5rem; font-size:1.25em; font-weight:bold; color:#e53945;">
        Total Arrecadado: R$ 0,00
    </div>
    <button id="btn-gerar-relatorio-rake" class="btn btn-primary" style="margin-top:1.5rem; display:none;">Gerar Relatório de Rake</button>
</div>

<script src="js/features/caixa-dashboard/rake.js"></script> 