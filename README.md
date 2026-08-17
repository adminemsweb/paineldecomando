# Site industrial

Base funcional de um site institucional B2B para engenharia, fabricação e manutenção de painéis elétricos. A arquitetura e o escopo estão em [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

> Os nomes, contatos, indicadores, imagens e conteúdos iniciais são dados demonstrativos identificáveis. Não publicar sem substituí-los e validá-los. Textos legais exigem revisão jurídica.

## Execução local

Requisitos: Node 22+ para o front-end; ou Docker Desktop para a pilha completa.

```bash
docker compose up --build
```

- Site: `http://localhost:5173`
- API/health: `http://localhost:8080/api/v1/health`
- MariaDB: `127.0.0.1:3308` (altere `DB_FORWARD_PORT` se necessário)

Sem Docker, execute `npm install && npm run dev` em `frontend`. Para a API, configure `backend/.env`, importe `database/schema.sql` e aponte um servidor PHP cujo document root seja `backend/public`.

### Painel administrativo local

Com a configuração SQLite de desenvolvimento, prepare o banco com `php backend/bin/setup-local.php`, inicie a API apontando o document root para `backend/public` e acesse `http://localhost:5173/admin/login`. As credenciais iniciais são definidas por `ADMIN_EMAIL` e `ADMIN_PASSWORD` no arquivo local `backend/.env`; troque a senha antes de disponibilizar o painel em uma rede.

## Comandos de qualidade

```bash
cd frontend
npm run typecheck
npm run lint
npm test
npm audit --audit-level=high
npm run build
cd ..
php backend/tests/run.php
```

Composer não é obrigatório nesta base: o autoloader PSR-4 interno evita bootstrap externo.

## Produção

A imagem de produção usa Nginx para o frontend, Apache/PHP para a API e MariaDB sem porta pública. Consulte o checklist completo em [docs/PRODUCTION.md](docs/PRODUCTION.md).

```powershell
Copy-Item .env.production.example .env.production
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
```

O build de produção bloqueia placeholders de telefone, WhatsApp, endereço e horário. A API gera logs JSON estruturados com `request_id` e remoção de segredos.

## Logs locais

Use `Ctrl + Shift + B` no VS Code e execute **Iniciar Painel de Comando (com logs)**. O backend mostra cada requisição em tempo real com estas cores:

- verde: sucesso e respostas `2xx`;
- azul/ciano: informação e redirecionamento;
- amarelo: aviso, validação e respostas `4xx`;
- vermelho: erro interno e respostas `5xx`;
- magenta: autenticação, bloqueios e outros eventos de segurança.

Os mesmos registros são gravados em JSON em `backend/storage/logs`, sem senhas, tokens, cookies, e-mails ou telefones. `LOG_CONSOLE=false` desativa a saída no terminal e `LOG_COLORS=false` mantém os registros sem cores.
