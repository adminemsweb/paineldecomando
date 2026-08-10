# Arquitetura proposta

## Visão geral

Monorepo com SPA React/TypeScript no front-end, API REST PHP 8.2+ no back-end e MariaDB/MySQL. O domínio público e `/admin` compartilham componentes básicos, mas usam layouts e proteções de rota distintos. A API responde sob `/api/v1`, usa Controller → Service → Repository → PDO e mantém validação, autenticação, autorização e erros fora dos controllers.

```text
Browser → React Router → services/api → /api/v1
                                      ↓
Router → Middleware → Controller → Service → Repository → PDO → MariaDB
```

Decisões:

- `fetch` nativo reduz dependências; uma camada única padroniza timeout, erros e autenticação.
- Sessão HTTP-only é a estratégia inicial do admin, com CSRF nas mutações, `SameSite=Lax` e `Secure` em produção.
- Conteúdo publicado é público; rascunhos e CRUD exigem sessão e permissão.
- Uploads ficam fora da execução PHP, são renomeados aleatoriamente e validados por extensão, MIME e tamanho.
- Exclusão lógica preserva histórico editorial e comercial.
- Conteúdo empresarial e números permanecem placeholders até validação do cliente.

## Sitemap e rotas

| Área | Rotas |
|---|---|
| Institucional | `/`, `/empresa`, `/contato`, `/orcamento`, `/politica-de-privacidade`, `/termos-de-uso` |
| Catálogo | `/produtos`, `/produtos/:slug`, `/servicos`, `/servicos/:slug` |
| Autoridade | `/segmentos`, `/segmentos/:slug`, `/projetos`, `/projetos/:slug`, `/blog`, `/blog/:slug` |
| Administração | `/admin/login`, `/admin`, `/admin/produtos`, `/admin/categorias`, `/admin/segmentos`, `/admin/servicos`, `/admin/projetos`, `/admin/posts`, `/admin/leads`, `/admin/configuracoes` |
| Sistema | `*` (404), `/sitemap.xml`, `/robots.txt` |

## Modelo de dados

- Identidade e acesso: `users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `password_resets`, `activity_logs`.
- Catálogo: `products`, `categories`, `product_categories`, `product_images`, `product_specifications`, `product_applications`, `product_segments`, `downloads`.
- Conteúdo: `segments`, `services`, `projects`, `project_images`, `project_products`, `posts`, `authors`, `post_categories`, `tags`, `post_tags`, `media`.
- Comercial: `leads`, `lead_files`, `lead_history`, `contacts`.
- Institucional: `site_settings`, `banners`, `testimonials`.

O diagrama lógico completo e as restrições estão materializados em `database/schema.sql`.

## Endpoints

Públicos: `GET /products[/{slug}]`, `/categories`, `/segments[/{slug}]`, `/services[/{slug}]`, `/projects[/{slug}]`, `/posts[/{slug}]`; `POST /leads`, `/contacts`, `/auth/login`, `/auth/logout`, `/auth/forgot-password`.

Administrativos: `GET /admin/dashboard` e CRUD REST de `products`, `categories`, `segments`, `services`, `projects`, `posts`, `leads` e `settings`. Listagens recebem `page`, `per_page`, `search`, `sort`, `direction` e filtros próprios. Respostas seguem `{success,data,message,meta,errors}`.

## Componentes principais

- Layout: `Topbar`, `Header`, `MobileNavigation`, `Footer`, `PublicLayout`, `AdminLayout`.
- Conversão: `QuoteForm`, `ContactForm`, `WhatsAppButton`, `CallToAction`.
- Conteúdo: `ProductCard`, `ProjectCard`, `ArticleCard`, `Breadcrumbs`, `Seo`.
- Catálogo: `FilterBar`, `SearchField`, `Pagination`, `Skeleton`, `EmptyState`.
- Admin: `ProtectedRoute`, `DataTable`, `StatusBadge`, `MediaUploader`, `EditorForm`.

## Fluxo de orçamento

1. Usuário abre formulário geral ou contextualizado por produto.
2. Front-end valida experiência e envia `multipart/form-data`.
3. API aplica rate limit, valida campos/LGPD e inspeciona arquivos.
4. Service grava lead e arquivos em transação, gera protocolo não sequencial e registra histórico.
5. Notificações são disparadas; falha de e-mail não perde o lead e fica registrada para reprocessamento.
6. Cliente recebe confirmação e o comercial acompanha estados e histórico no admin.

## Plano de implementação

1. Base: monorepo, roteamento, layouts, API, banco, ambientes e documentação.
2. Site público: home e páginas de conteúdo/catálogo, formulários e SEO.
3. Admin: autenticação/RBAC, dashboard e CRUDs.
4. Qualidade: testes de API e UI, uploads, segurança, acessibilidade, responsividade e performance.

## Dados pendentes

Nome/razão social, logotipo, domínio, telefones, WhatsApp, e-mails, endereço/mapa, horários, redes, história, equipe, área atendida, indicadores comprováveis, normas/certificações confirmadas, garantias, portfólio e fotos autorizadas, textos jurídicos revisados, SMTP, analytics e pixels. Todo placeholder deve ser substituído antes da produção.

