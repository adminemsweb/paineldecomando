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
- MariaDB: `localhost:3306`

Sem Docker, execute `npm install && npm run dev` em `frontend`. Para a API, configure `backend/.env`, importe `database/schema.sql` e aponte um servidor PHP cujo document root seja `backend/public`.

## Comandos de qualidade

```bash
cd frontend
npm run typecheck
npm run build
npm run lint
```

Composer não é obrigatório nesta base: o autoloader PSR-4 interno evita bootstrap externo. Em produção, use segredos fortes, HTTPS, SMTP real, backup, fila de e-mails, armazenamento privado de uploads e desative `APP_DEBUG`.
