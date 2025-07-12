// js/features/caixa-dashboard/caixinhas.js

document.addEventListener('DOMContentLoaded', function () {
  // --- Elementos principais ---
  const btnCriar = document.getElementById('btn-criar-caixinha');
  const modalCriar = document.getElementById('modal-criar-caixinha');
  const closeCriar = document.getElementById('close-modal-criar');
  const formCriar = document.getElementById('form-criar-caixinha');
  const listaCaixinhas = document.getElementById('lista-caixinhas');
  const feedback = document.getElementById('caixinhas-feedback');

  const modalAdicionar = document.getElementById('modal-adicionar-valor');
  const closeAdicionar = document.getElementById('close-modal-adicionar');
  const formAdicionar = document.getElementById('form-adicionar-valor');

  const modalDetalhes = document.getElementById('modal-detalhes-caixinha');
  const closeDetalhes = document.getElementById('close-modal-detalhes');
  const detalhesConteudo = document.getElementById('detalhes-caixinha-conteudo');

  let caixinhas = [];
  let inclusoes = {};

  // --- Utilitários ---
  function mostrarFeedback(msg, tipo = 'sucesso') {
    feedback.textContent = msg;
    feedback.className = tipo === 'erro' ? 'erro' : 'sucesso';
    feedback.style.display = 'block';
    setTimeout(() => { feedback.style.display = 'none'; }, 2500);
  }

  function calcularValores(caixinha) {
    const lista = inclusoes[caixinha.id] || [];
    const totalBruto = lista.reduce((s, i) => s + parseFloat(i.valor), 0);
    const cashback = Math.round(totalBruto * (caixinha.cashback / 100));
    const totalLiquido = totalBruto - cashback;
    const valorParticipante = caixinha.participantes > 0 ? totalLiquido / caixinha.participantes : 0;
    return { totalBruto, cashback, totalLiquido, valorParticipante };
  }

  // --- AJAX ---
  function carregarCaixinhas() {
    fetch('api/caixas/caixinhas_listar.php')
      .then(r => r.json())
      .then(json => {
        if (!json.success) {
          mostrarFeedback(json.message || 'Erro ao buscar caixinhas', 'erro');
          caixinhas = [];
        } else {
          caixinhas = json.caixinhas || [];
        }
        // Para cada caixinha, carregar inclusões
        let promises = caixinhas.map(c => carregarInclusoes(c.id));
        Promise.all(promises).then(renderizarCaixinhas);
      })
      .catch(() => mostrarFeedback('Erro de conexão ao buscar caixinhas', 'erro'));
  }

  function carregarInclusoes(id) {
    return fetch('api/caixas/caixinhas_listar_inclusoes.php?id_caixinha=' + id)
      .then(r => r.json())
      .then(json => {
        inclusoes[id] = json.success ? json.inclusoes : [];
      });
  }

  // --- Renderização ---
  function renderizarCaixinhas() {
    listaCaixinhas.innerHTML = '';
    if (!caixinhas.length) {
      listaCaixinhas.innerHTML = '<p style="color:#888;">Nenhuma caixinha criada nesta sessão.</p>';
      return;
    }
    caixinhas.forEach(caixinha => {
      const { totalBruto, cashback, totalLiquido, valorParticipante } = calcularValores(caixinha);
      const card = document.createElement('div');
      card.className = 'caixinha-card';
      card.innerHTML = `
        <div class="caixinha-nome">${caixinha.nome}</div>
        <div class="caixinha-cards-grid">
          <div class="caixinha-value-card">
            <span class="caixinha-label">Total bruto</span>
            <span class="caixinha-valor entrada">R$ ${totalBruto.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
          </div>
          <div class="caixinha-value-card">
            <span class="caixinha-label">Cashback</span>
            <span class="caixinha-valor info">${caixinha.cashback}% (R$ ${cashback.toLocaleString('pt-BR', {minimumFractionDigits:2})})</span>
          </div>
          <div class="caixinha-value-card">
            <span class="caixinha-label">Total líquido</span>
            <span class="caixinha-valor saldo">R$ ${totalLiquido.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
          </div>
          <div class="caixinha-value-card">
            <span class="caixinha-label">Participantes</span>
            <span class="caixinha-valor">${caixinha.participantes}</span>
          </div>
          <div class="caixinha-value-card">
            <span class="caixinha-label">Valor por participante</span>
            <span class="caixinha-valor entrada">R$ ${valorParticipante.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
          </div>
        </div>
        <div class="caixinha-actions">
          <button class="btn btn-success btn-adicionar" data-id="${caixinha.id}">Adicionar Valor</button>
          <button class="btn btn-secondary btn-detalhes" data-id="${caixinha.id}">Detalhes</button>
        </div>
      `;
      listaCaixinhas.appendChild(card);
    });
    // Eventos dos botões
    document.querySelectorAll('.btn-adicionar').forEach(btn => {
      btn.onclick = () => abrirModalAdicionar(btn.dataset.id);
    });
    document.querySelectorAll('.btn-detalhes').forEach(btn => {
      btn.onclick = () => abrirModalDetalhes(btn.dataset.id);
    });
  }

  // --- Modais ---
  btnCriar.onclick = () => { modalCriar.style.display = 'flex'; };
  closeCriar.onclick = () => { modalCriar.style.display = 'none'; };
  closeAdicionar.onclick = () => { modalAdicionar.style.display = 'none'; };
  closeDetalhes.onclick = () => { modalDetalhes.style.display = 'none'; };

  // --- Criação de caixinha ---
  formCriar.onsubmit = function(e) {
    e.preventDefault();
    const nome = this.nome.value.trim();
    const cashback = parseInt(this.cashback.value);
    const participantes = parseInt(this.participantes.value);
    if (!nome || participantes < 1 || cashback < 0 || cashback > 100) {
      mostrarFeedback('Preencha todos os campos corretamente.', 'erro');
      return;
    }
    fetch('api/caixas/caixinhas_criar.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nome, cashback, participantes })
    })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          modalCriar.style.display = 'none';
          mostrarFeedback('Caixinha criada com sucesso!');
          carregarCaixinhas();
          this.reset();
        } else {
          mostrarFeedback(json.message || 'Erro ao criar caixinha', 'erro');
        }
      })
      .catch(() => mostrarFeedback('Erro de conexão ao criar caixinha', 'erro'));
  };

  // --- Adicionar valor ---
  function abrirModalAdicionar(id) {
    formAdicionar.id_caixinha.value = id;
    formAdicionar.valor.value = '';
    modalAdicionar.style.display = 'flex';
  }
  formAdicionar.onsubmit = function(e) {
    e.preventDefault();
    const id = this.id_caixinha.value;
    const valor = parseFloat(this.valor.value);
    if (!id || isNaN(valor) || valor <= 0) {
      mostrarFeedback('Informe um valor válido.', 'erro');
      return;
    }
    fetch('api/caixas/caixinhas_incluir_valor.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_caixinha: id, valor })
    })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          modalAdicionar.style.display = 'none';
          mostrarFeedback('Valor adicionado! Cashback lançado nas receitas.');
          carregarCaixinhas();
        } else {
          mostrarFeedback(json.message || 'Erro ao adicionar valor', 'erro');
        }
      })
      .catch(() => mostrarFeedback('Erro de conexão ao adicionar valor', 'erro'));
  };

  // --- Detalhes da caixinha ---
  function abrirModalDetalhes(id) {
    fetch('api/caixas/caixinhas_listar_inclusoes.php?id_caixinha=' + id)
      .then(r => r.json())
      .then(json => {
        const lista = json.success ? json.inclusoes : [];
        let html = `<table style="width:100%;margin-bottom:1rem;">
          <thead><tr><th>Valor</th><th>Data/Hora</th><th>Operador</th><th>Perfil</th><th>Ação</th></tr></thead><tbody>`;
        lista.forEach((inc) => {
          html += `<tr>
            <td>R$ ${parseFloat(inc.valor).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
            <td>${inc.data_inclusao}</td>
            <td>${inc.nome}</td>
            <td>${inc.perfil}</td>
            <td><button class="btn btn-danger btn-excluir" data-id="${inc.id}">🗑</button></td>
          </tr>`;
        });
        html += lista.length ? '' : '<tr><td colspan="5" style="color:#888;">Nenhuma inclusão ainda.</td></tr>';
        html += '</tbody></table>';
        detalhesConteudo.innerHTML = html;
        modalDetalhes.style.display = 'flex';
        // Evento de exclusão
        document.querySelectorAll('.btn-excluir').forEach(btn => {
          btn.onclick = () => excluirInclusao(btn.dataset.id, id);
        });
      });
  }
  function excluirInclusao(id_inclusao, id_caixinha) {
    fetch('api/caixas/caixinhas_excluir_inclusao.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_inclusao })
    })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          mostrarFeedback('Inclusão excluída.');
          abrirModalDetalhes(id_caixinha);
          carregarCaixinhas();
        } else {
          mostrarFeedback(json.message || 'Erro ao excluir inclusão', 'erro');
        }
      })
      .catch(() => mostrarFeedback('Erro de conexão ao excluir inclusão', 'erro'));
  }

  // --- Inicialização ---
  carregarCaixinhas();
}); 