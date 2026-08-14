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
