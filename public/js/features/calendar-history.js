// Calendário visual premium com UX aprimorada

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-history');
    if (calendarEl) {
        renderCalendar(calendarEl);
    }
});

function renderCalendar(container) {
    let today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    let selectedDay = null;
    let reports = {};

    function fetchReportsAndUpdate() {
        const url = `/api/relatorios/get_historico.php?ano=${currentYear}&mes=${currentMonth+1}`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    reports = data.relatorios || {};
                } else {
                    reports = {};
                }
                updateCalendar();
            })
            .catch(() => {
                reports = {};
                updateCalendar();
            });
    }
    window.atualizarHistoricoCaixas = fetchReportsAndUpdate;

    function updateCalendar() {
        container.style.opacity = 0.7;
        setTimeout(() => { container.style.opacity = 1; }, 120);
        container.innerHTML = '';
        const monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        const daysOfWeek = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const startDay = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        // Header
        const header = document.createElement('div');
        header.className = 'calendar-header';
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = `<svg class="back-arrow-svg" viewBox="0 0 24 24" width="32" height="32" aria-hidden="true"><polyline points="16 6 8 12 16 18" fill="none" stroke="var(--accent-color)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        prevBtn.setAttribute('aria-label', 'Mês anterior');
        prevBtn.onclick = () => { currentMonth--; if(currentMonth<0){currentMonth=11;currentYear--;} fetchReportsAndUpdate(); };
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = `<svg class="back-arrow-svg" style="transform: scaleX(-1);" viewBox="0 0 24 24" width="32" height="32" aria-hidden="true"><polyline points="16 6 8 12 16 18" fill="none" stroke="var(--accent-color)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        nextBtn.setAttribute('aria-label', 'Próximo mês');
        nextBtn.onclick = () => { currentMonth++; if(currentMonth>11){currentMonth=0;currentYear++;} fetchReportsAndUpdate(); };
        const title = document.createElement('span');
        title.className = 'calendar-title';
        title.textContent = monthNames[currentMonth] + ' ' + currentYear;
        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        container.appendChild(header);

        // Table
        const table = document.createElement('table');
        table.className = 'calendar-table';
        const thead = document.createElement('thead');
        const trHead = document.createElement('tr');
        daysOfWeek.forEach(d => {
            const th = document.createElement('th');
            th.textContent = d;
            trHead.appendChild(th);
        });
        thead.appendChild(trHead);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        let tr = document.createElement('tr');
        for(let i=0; i<startDay; i++) {
            tr.appendChild(document.createElement('td'));
        }
        for(let day=1; day<=daysInMonth; day++) {
            const dateStr = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const td = document.createElement('td');
            td.textContent = day;
            td.tabIndex = 0;
            // Destacar hoje
            const isToday = (day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear());
            if (isToday) td.classList.add('today');
            // Tooltip customizada e destaque
            if (reports[dateStr]) {
                td.classList.add('has-report');
                td.setAttribute('aria-label', reports[dateStr].map(r=>r.tipo).join(', '));
                // Tooltip custom
                const tooltip = document.createElement('span');
                tooltip.className = 'calendar-tooltip';
                tooltip.textContent = reports[dateStr].map(r=>r.tipo).join(', ');
                td.appendChild(tooltip);
            }
            if (selectedDay === dateStr) {
                td.classList.add('selected');
            }
            td.onclick = () => {
                selectedDay = dateStr;
                updateCalendar();
                showRelatoriosDoDia(dateStr);
            };
            td.onkeydown = (e) => {
                if (e.key === 'Enter' || e.key === ' ') td.click();
            };
            tr.appendChild(td);
            if ((tr.children.length) % 7 === 0) {
                tbody.appendChild(tr);
                tr = document.createElement('tr');
            }
        }
        if (tr.children.length > 0) {
            while (tr.children.length < 7) tr.appendChild(document.createElement('td'));
            tbody.appendChild(tr);
        }
        table.appendChild(tbody);
        container.appendChild(table);
    }

    function showRelatoriosDoDia(dateStr) {
        let detailsEl = document.getElementById('calendar-day-details');
        if (!detailsEl) {
            detailsEl = document.createElement('div');
            detailsEl.id = 'calendar-day-details';
            detailsEl.style.marginTop = '1.5rem';
            detailsEl.style.width = '100%';
            detailsEl.style.maxWidth = '700px';
            detailsEl.style.background = 'var(--surface-color)';
            detailsEl.style.borderRadius = '12px';
            detailsEl.style.padding = '1.2rem 1.5rem';
            detailsEl.style.boxShadow = '0 2px 12px 0 rgba(0,0,0,0.08)';
            detailsEl.style.border = '1px solid var(--border-color)';
            container.parentNode.appendChild(detailsEl);
        }
        detailsEl.innerHTML = '';
        if (!reports[dateStr]) {
            detailsEl.innerHTML = '<span style="color:var(--secondary-text-color);font-size:1.1rem;">Nenhum relatório gerado neste dia.</span>';
            return;
        }
        const lista = document.createElement('ul');
        lista.style.listStyle = 'none';
        lista.style.padding = '0';
        lista.style.margin = '0';
        reports[dateStr].forEach(r => {
            const li = document.createElement('li');
            li.style.marginBottom = '1.1rem';
            li.innerHTML = `<strong style='color:#e11d48;'>${r.tipo}</strong> <span style='color:var(--secondary-text-color);font-size:0.98rem;'>(às ${r.hora})</span> <span style='color:${r.status==='Sucesso'?'#238636':'#da3633'};font-weight:600;'>${r.status}</span>`;
            if (r.arquivo) {
                li.innerHTML += ` <a href='${r.arquivo}' target='_blank' style='margin-left:1rem;color:#3b82f6;text-decoration:underline;font-weight:500;'>Baixar</a>`;
            }
            if (r.status==='Erro' && r.mensagem_erro) {
                li.innerHTML += `<br><span style='color:#da3633;font-size:0.95rem;'>${r.mensagem_erro}</span>`;
            }
            lista.appendChild(li);
        });
        detailsEl.appendChild(lista);
    }

    fetchReportsAndUpdate();
} 