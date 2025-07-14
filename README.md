# Easy Rake

## Visão Geral
Sistema de gestão de clubes, caixas, jogadores e operações financeiras.

---

## Configuração do Projeto

- **Banco de dados:**
  - Porta: **3307** (XAMPP padrão)
  - Usuário: **root**
  - Senha: **36140888**
  - Banco: **easy_rake**
- Para criar o banco e as tabelas, execute:
  ```bash
  php tests/setup_database.php
  ```

---

## Conta Padrão de Testes

> **Sempre utilize esta conta para testes automáticos e manuais:**
- **CPF:** `12924554466`
- **Senha:** `123456`
- **Nome:** Lucas Andrade
- **E-mail:** teste@teste.com

A IA deve SEMPRE testar e validar fluxos usando esta conta antes de sugerir qualquer alteração!

---

## Testes Automatizados

- Para rodar todos os testes automáticos:
  ```bash
  php tests/teste_cadastros_login.php
  ```
- Para limpar dados de teste:
  ```bash
  php tests/limpar_dados_teste.php
  ```

---

## Scripts e Utilitários para Desenvolvedores e IA

- **Ler README automaticamente:**
  ```bash
  cat README.md
  ```
- **Atualizar changelog automaticamente (exemplo IA):**
  ```php
  // Exemplo de função PHP para IA
  function atualizarChangelog($mensagem) {
      $readme = file_get_contents('README.md');
      $readme = preg_replace('/(## Changelog \/ Relat[óo]rio de Atualiza[cç][ãa]oes\n)([\s\S]*?)(---)/', "$1- ".date('Y-m-d')." - IA - $mensagem\n$2$3", $readme);
      file_put_contents('README.md', $readme);
  }
  ```
- **Script para forçar leitura do README antes de qualquer alteração:**
  ```bash
  #!/bin/bash
  echo "Leia o README.md antes de continuar!"
  cat README.md | less
  ```
- **Script para validar se README foi atualizado após mudanças:**
  ```bash
  git diff --name-only HEAD~1 | grep README.md && echo "README.md foi alterado." || echo "Atenção: README.md não foi alterado!"
  ```

---

## Checklist da IA (e dos Devs)

- [ ] Ler o README.md antes de sugerir/aplicar qualquer mudança
- [ ] Validar a conta padrão de testes (CPF: 12924554466)
- [ ] Rodar todos os testes automáticos
- [ ] Atualizar o changelog/relatório após qualquer alteração relevante
- [ ] Documentar decisões, aprendizados e padrões no README
- [ ] Sugerir scripts ou hooks para facilitar a automação
- [ ] Validar se README.md foi alterado após mudanças importantes

---

## Hooks e Integração Contínua (CI)

- **Exemplo de hook de pré-commit (Git):**
  ```bash
  # .git/hooks/pre-commit
  if ! grep -q "CPF: 12924554466" README.md; then
    echo "Atenção: README.md deve conter a conta padrão de testes!"; exit 1;
  fi
  echo "Checklist: Lembre-se de atualizar o changelog no README.md!"
  ```
- **Sugestão de CI (GitHub Actions, GitLab CI, etc):**
  - Sempre rode os testes automáticos após cada push/merge
  - Valide se README.md foi alterado em PRs que mudam APIs, banco ou regras de negócio
  - Gere um alerta se o changelog não for atualizado

---

## Como a IA deve agir neste projeto

1. **Leia o README.md antes de qualquer ação.**
2. **Sempre valide a conta padrão de testes após mudanças em login, cadastro ou permissões.**
3. **Rode os testes automáticos e limpe dados de teste se necessário.**
4. **Atualize o changelog/relatório no README.md após qualquer alteração relevante.**
5. **Documente decisões, padrões e aprendizados no README.md.**
6. **Sugira scripts, hooks e automações para facilitar a vida dos devs e da IA.**
7. **Se possível, automatize a leitura e atualização do README.md.**

---

## Changelog / Relatório de Atualizações

> **IMPORTANTE:**
> Sempre que a IA ou um desenvolvedor fizer uma alteração relevante, registre aqui:

- **[DATA]** [AUTOR] - [RESUMO DA MUDANÇA]
- 2024-04-XX - IA - Removido campo endereço da unidade do banco, backend e frontend.
- 2024-04-XX - IA - Corrigido fluxo de cadastro e login para usar conta padrão de testes.
- ...

---

## Dicas para Evolução da IA

- A IA deve SEMPRE ler este README antes de sugerir mudanças.
- A IA deve SEMPRE testar a conta padrão antes de finalizar qualquer alteração.
- A IA deve SEMPRE atualizar o changelog/relatório após qualquer mudança relevante.
- A IA deve SEMPRE sugerir testes automáticos para validar fluxos críticos.
- A IA deve sugerir scripts, hooks e automações para facilitar a evolução do projeto.

---

## Contato e Suporte

Dúvidas? Fale com o responsável pelo projeto ou registre uma issue. 

# Relatório Técnico: Página de Aprovações (Easy Rake)

## 1. Objetivo da Página
A página de Aprovações permite que gestores visualizem, aprovem ou rejeitem solicitações de acesso de funcionários (Caixa/Sanger) ao sistema, controlando permissões e mantendo um histórico detalhado de todas as decisões.

## 2. Fluxo de Funcionamento

### Frontend
- **Listagem de Pendentes:**
  - Faz fetch para `api/aprovacoes_acesso/listar_pendentes.php`.
  - Exibe ID, Tipo, Solicitante, CPF, Data Solicitação, Status e Ações (Aprovar/Rejeitar).
- **Aprovação/Reprovação:**
  - Botões disparam função JS que faz POST para `api/aprovacoes_acesso/acao.php` com `{id, acao}`.
  - Após ação, recarrega pendentes e histórico.
- **Histórico:**
  - Faz fetch para `api/aprovacoes_acesso/listar_historico.php`.
  - Exibe ID, Tipo, Solicitante, CPF, Status, Data Decisão e Gestor.

### Backend (PHP)
- **listar_pendentes.php:**
  - Retorna todas as solicitações com status 'Pendente', incluindo dados do funcionário e unidade.
- **acao.php:**
  - Recebe POST com `{id, acao}`.
  - Atualiza status e gestor na tabela `aprovacoes_acesso`.
  - Atualiza campo `acesso_liberado` na tabela `funcionarios`.
  - Registra ação na tabela `aprovacoes_acesso_historico` (com data, status, aprovador).
- **listar_historico.php:**
  - Busca na tabela `aprovacoes_acesso_historico` todas as ações realizadas, juntando com dados do funcionário e gestor.
  - Retorna campos: id, tipo, solicitante, cpf, status, data_decisao, gestor_nome.

## 3. Estrutura do Banco de Dados

- **aprovacoes_acesso**
  - id, funcionario_id, tipo, status, data_solicitacao, data_decisao, gestor_id, observacoes
- **funcionarios**
  - id, usuario_id, unidade_id, cargo, status, acesso_liberado, data_vinculo, data_aprovacao, data_demissao
- **aprovacoes_acesso_historico**
  - id, aprovacao_id, funcionario_id, status, data_acao, funcionario_aprovador_id

## 4. Pontos Críticos e Lições Aprendidas

- **Porta do MySQL:**
  - O sistema usa a porta 3307 (XAMPP).
  - O arquivo `config/database.php` deve sempre refletir isso.
- **Campos obrigatórios:**
  - O campo `acesso_liberado` em `funcionarios` é essencial para o controle de permissões.
  - O campo `funcionario_aprovador_id` em `aprovacoes_acesso_historico` é fundamental para rastreabilidade.
- **Nomes de campos:**
  - O backend e o frontend devem estar sincronizados quanto aos nomes dos campos retornados (ex: `data_decisao`).
- **Histórico:**
  - O histórico é construído a partir da tabela `aprovacoes_acesso_historico`, não apenas do status atual.
- **Erros comuns:**
  - Erros de "Unknown column" geralmente indicam que o banco de dados real não está sincronizado com o script SQL.
  - Sempre conferir a estrutura real do banco com `DESCRIBE` no phpMyAdmin.

## 5. Checklist de Diagnóstico Rápido

- [ ] O banco de dados está na porta correta e com a senha certa?
- [ ] A tabela `funcionarios` tem o campo `acesso_liberado`?
- [ ] A tabela `aprovacoes_acesso_historico` tem o campo `funcionario_aprovador_id`?
- [ ] O endpoint `acao.php` está atualizando os campos corretos (`gestor_id`, `data_decisao`)?
- [ ] O histórico está sendo buscado da tabela correta e retornando os campos certos?
- [ ] O frontend está usando os nomes de campo corretos?

## 6. Como corrigir se quebrar no futuro

- **Erro de coluna:**
  - Execute `DESCRIBE` na tabela e compare com o script SQL.
  - Adicione a coluna faltante com `ALTER TABLE`.
- **Erro de permissão:**
  - Verifique se o campo `acesso_liberado` está sendo atualizado corretamente.
- **Histórico vazio ou com null:**
  - Verifique se o insert no histórico está sendo feito corretamente.
  - Confirme se o join no SQL do histórico está correto.
- **Dados não aparecem no frontend:**
  - Verifique se o fetch está correto e se os nomes dos campos batem com o backend. 