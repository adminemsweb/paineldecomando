# Publicação em produção

## Pré-requisitos

- Docker Engine com Compose v2.
- DNS e HTTPS configurados no proxy ou plataforma de hospedagem.
- Credenciais reais de banco e, se o frete for habilitado, contrato/API dos Correios.
- Telefone, WhatsApp, endereço e horário comercial confirmados.
- Revisão jurídica final dos textos de garantia, trocas, privacidade e termos.

## Configuração

1. Copie `.env.production.example` para `.env.production`.
2. Substitua todas as senhas e os campos vazios.
3. Nunca envie `.env.production` ao Git.
4. Valide os dados públicos:

```powershell
cd frontend
npm run build:prod
```

O build de produção falha intencionalmente enquanto telefone, WhatsApp, endereço ou horário estiverem ausentes.

## Qualidade antes do deploy

```powershell
cd frontend
npm ci
npm run typecheck
npm run lint
npm test
npm audit --audit-level=high
npm run build:prod

cd ..
php backend/tests/run.php
```

## Subida com Docker

```powershell
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
```

O frontend Nginx atende a SPA e encaminha `/api/` internamente para o Apache/PHP. O MariaDB não publica porta externa no arquivo de produção.

## Saúde e logs

- Frontend: `GET /healthz`
- API: `GET /api/v1/health`
- Toda resposta da API inclui `X-Request-ID` e `request_id` no JSON.
- A API grava JSON Lines em `backend/storage/logs/app-AAAA-MM-DD.jsonl` no desenvolvimento e no volume `api_logs` em produção.
- Senhas, tokens, cookies e códigos de API são removidos automaticamente do contexto dos logs.

```powershell
docker compose -f docker-compose.prod.yml logs --tail=200 frontend api db
docker compose -f docker-compose.prod.yml exec api sh -lc 'tail -n 100 storage/logs/app-$(date +%F).jsonl'
```

Configure rotação e retenção externa conforme o provedor. O Compose limita os logs dos containers a cinco arquivos de 10 MB.

## Pendências que bloqueiam publicação real

- Número do WhatsApp e telefone comercial.
- Endereço e horário de atendimento.
- Peso e dimensões embaladas de cada produto.
- Credenciais e serviços habilitados no contrato dos Correios.
- Dados empresariais, CNPJ e textos jurídicos aprovados.
- Backup automatizado do banco, monitoramento e certificado TLS no ambiente final.
