// js/features/caixa-dashboard/inventario.js

document.addEventListener('DOMContentLoaded', function () {
  // Valor atual (mock pode ser substituído por integração real se necessário)
  // document.getElementById('inventario-valor-atual').textContent =
  //   'R$ ' + valorAtual.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

  // Buscar histórico real do backend
  function carregarHistoricoConferencia() {
    const tbody = document.getElementById('inventario-historico-tbody');
    tbody.innerHTML = '<tr><td colspan="5">Carregando...</td></tr>';
    fetch('api/caixas/historico_conferencia.php?caixa_id=' + window.CAIXA_ID)
      .then(r => r.json())
      .then(data => {
        tbody.innerHTML = '';
        if (data.success && Array.isArray(data.historico) && data.historico.length > 0) {
          data.historico.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${item.data_hora}</td>
              <td>R$ ${Number(item.valor_informado).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
              <td>${item.diferenca !== null ? 'R$ ' + Number(item.diferenca).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '-'}</td>
              <td>${item.operador || '-'}</td>
              <td>${item.resultado || '-'}</td>
            `;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="5">Nenhum registro encontrado para este caixa.</td></tr>';
        }
      })
      .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5">Erro ao carregar histórico.</td></tr>';
      });
  }

  carregarHistoricoConferencia();

  // Botão de conferido (mock)
  const btn = document.getElementById('btn-conferir-inventario');
  if (btn) {
    btn.addEventListener('click', function () {
      alert('Conferência registrada! (mock)');
      // Após registrar, recarregar histórico se integração real
      // carregarHistoricoConferencia();
    });
  }
}); 