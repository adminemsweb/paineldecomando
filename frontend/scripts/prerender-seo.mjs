import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const frontendDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const distDir = path.join(frontendDir, 'dist');
const catalog = JSON.parse(await readFile(path.resolve(frontendDir, '..', 'backend', 'database', 'catalog.json'), 'utf8'));
const template = await readFile(path.join(distDir, 'index.html'), 'utf8');
const siteUrl = (process.env.VITE_SITE_URL || 'https://paineldecomando.com.br').replace(/\/$/, '');
const googleVerification = (process.env.VITE_GOOGLE_SITE_VERIFICATION || '').trim();

const homePage = {
  title: 'Painéis de Comando Elétrico e Automação Industrial | Painel de Comando',
  description: 'Painéis elétricos de comando, partida estrela-triângulo, soft starter, inversores de frequência, irrigação e bombas para aplicações industriais.',
};

const staticPages = {
  empresa: ['Empresa de Painéis de Comando Elétrico | Painel de Comando', 'Conheça a Painel de Comando e nossas soluções em painéis elétricos, acionamentos e automação industrial.'],
  produtos: ['Painéis de Comando Elétrico: Produtos e Modelos', 'Compare modelos de painéis de comando, estrela-triângulo, soft starter, inversor de frequência, irrigação e bombas.'],
  servicos: ['Serviços em Painéis Elétricos e Automação | Painel de Comando', 'Projetos, montagem, retrofit, instalação, comissionamento e manutenção de painéis elétricos e sistemas de automação.'],
  segmentos: ['Painéis Elétricos para Indústria e Infraestrutura', 'Soluções em painéis de comando para indústria, saneamento, irrigação, construção, agronegócio e infraestrutura.'],
  projetos: ['Projetos de Painéis de Comando | Painel de Comando', 'Projetos e aplicações de painéis elétricos de comando, acionamentos e automação industrial.'],
  blog: ['Conteúdo Técnico sobre Painéis de Comando', 'Artigos técnicos sobre painéis elétricos, partidas de motores, inversores, soft starters, automação e manutenção.'],
  contato: ['Contato e Orçamento | Painel de Comando', 'Fale com a equipe da Painel de Comando e solicite orientação comercial ou orçamento para sua aplicação.'],
};

const escapeHtml = value => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
const absoluteUrl = value => !value ? `${siteUrl}/brand/painel-de-comando-raio-favicon.png` : /^https?:\/\//i.test(value) ? value : `${siteUrl}${value.startsWith('/') ? value : `/${value}`}`;
const titleHeading = title => title.split('|')[0].trim();

function renderSeo({ title, description, canonical, image, type = 'website', structuredData, bodyHtml = '' }) {
  let html = template
    .replace(/<title>.*?<\/title>/s, `<title>${escapeHtml(title)}</title>`)
    .replace(/<meta name="description"[^>]*>/, `<meta name="description" content="${escapeHtml(description)}" />`)
    .replace(/<link rel="canonical"[^>]*>/, `<link rel="canonical" href="${escapeHtml(canonical)}" />`)
    .replace(/<meta property="og:type"[^>]*>/, `<meta property="og:type" content="${type}" />`)
    .replace(/<meta property="og:title"[^>]*>/, `<meta property="og:title" content="${escapeHtml(title)}" />`)
    .replace(/<meta property="og:description"[^>]*>/, `<meta property="og:description" content="${escapeHtml(description)}" />`)
    .replace(/<meta property="og:url"[^>]*>/, `<meta property="og:url" content="${escapeHtml(canonical)}" />`)
    .replace(/<meta property="og:image"[^>]*>/, `<meta property="og:image" content="${escapeHtml(absoluteUrl(image))}" />`)
    .replace('<div id="root"></div>', `<div id="root">${bodyHtml}</div>`);

  if (googleVerification) {
    html = html.replace('</head>', `    <meta name="google-site-verification" content="${escapeHtml(googleVerification)}" />\n  </head>`);
  }
  if (structuredData) {
    const json = JSON.stringify(structuredData).replaceAll('<', '\\u003c');
    html = html.replace('</head>', `    <script type="application/ld+json" data-site-structured-data="true">${json}</script>\n  </head>`);
  }
  return html;
}

const homeStructuredData = {
  '@context': 'https://schema.org',
  '@graph': [
    { '@type': 'Organization', '@id': `${siteUrl}/#organization`, name: 'Painel de Comando', url: `${siteUrl}/`, logo: absoluteUrl('/brand/painel-de-comando-raio-favicon.png') },
    { '@type': 'WebSite', '@id': `${siteUrl}/#website`, name: 'Painel de Comando', url: `${siteUrl}/`, inLanguage: 'pt-BR', publisher: { '@id': `${siteUrl}/#organization` } },
  ],
};

const homeBody = `
      <main id="conteudo" data-seo-fallback>
        <h1>Painéis de comando elétrico e automação industrial</h1>
        <p>${escapeHtml(homePage.description)}</p>
        <nav aria-label="Principais páginas">
          <a href="/produtos">Ver painéis de comando</a>
          <a href="/empresa">Conheça a empresa</a>
          <a href="/servicos">Serviços</a>
          <a href="/contato">Solicitar orçamento</a>
        </nav>
      </main>`;

const publishedProducts = catalog.filter(item => item.status === 'published');
const productLinks = publishedProducts
  .map(product => `<li><a href="/produtos/${escapeHtml(product.slug)}">${escapeHtml(product.name)}</a></li>`)
  .join('');

await writeFile(path.join(distDir, 'index.html'), renderSeo({ ...homePage, canonical: `${siteUrl}/`, structuredData: homeStructuredData, bodyHtml: homeBody }), 'utf8');

for (const [route, [title, description]] of Object.entries(staticPages)) {
  const catalogLinks = route === 'produtos'
    ? `<section aria-labelledby="catalogo-produtos"><h2 id="catalogo-produtos">Catálogo de painéis de comando</h2><ul>${productLinks}</ul></section>`
    : '';
  const bodyHtml = `
      <main id="conteudo" data-seo-fallback>
        <nav aria-label="Navegação estrutural"><a href="/">Início</a></nav>
        <h1>${escapeHtml(titleHeading(title))}</h1>
        <p>${escapeHtml(description)}</p>
        <a href="/produtos">Ver produtos</a>
        ${catalogLinks}
      </main>`;
  await writeFile(path.join(distDir, `${route}.html`), renderSeo({ title, description, canonical: `${siteUrl}/${route}`, bodyHtml }), 'utf8');
}

const productDir = path.join(distDir, 'produtos');
await mkdir(productDir, { recursive: true });
let productCount = 0;
for (const product of publishedProducts) {
  const title = product.seo_title || `${product.name} | Painel de Comando`;
  const description = product.seo_description || product.summary || `Conheça ${product.name}.`;
  const canonical = `${siteUrl}/produtos/${product.slug}`;
  const images = [product.featured_image, ...JSON.parse(product.gallery_images || '[]')].filter(Boolean).map(absoluteUrl);
  const availability = product.stock_status === 'out_of_stock' ? 'https://schema.org/OutOfStock' : product.stock_status === 'on_demand' ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock';
  const structuredData = {
    '@context': 'https://schema.org', '@type': 'Product', name: product.name, description, image: images,
    sku: product.reference_code || product.slug,
    brand: { '@type': 'Brand', name: product.brand || 'Painel de Comando' }, url: canonical,
    ...(product.price_cents != null ? { offers: { '@type': 'Offer', priceCurrency: 'BRL', price: (product.price_cents / 100).toFixed(2), availability, url: canonical } } : {}),
  };
  const price = product.price_cents != null ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(product.price_cents / 100) : '';
  const bodyHtml = `
      <main id="conteudo" data-seo-fallback>
        <nav aria-label="Navegação estrutural"><a href="/">Início</a> / <a href="/produtos">Produtos</a></nav>
        <article>
          <h1>${escapeHtml(product.name)}</h1>
          ${product.featured_image ? `<img src="${escapeHtml(absoluteUrl(product.featured_image))}" alt="${escapeHtml(product.name)}" />` : ''}
          <p>${escapeHtml(description)}</p>
          <dl>
            ${product.reference_code ? `<dt>Referência</dt><dd>${escapeHtml(product.reference_code)}</dd>` : ''}
            ${product.power_label ? `<dt>Potência</dt><dd>${escapeHtml(product.power_label)}</dd>` : ''}
            ${product.voltage_label ? `<dt>Tensão</dt><dd>${escapeHtml(product.voltage_label)}</dd>` : ''}
            ${price ? `<dt>Preço</dt><dd>${escapeHtml(price)}</dd>` : ''}
          </dl>
        </article>
      </main>`;
  await writeFile(path.join(productDir, `${product.slug}.html`), renderSeo({ title, description, canonical, image: product.featured_image, type: 'product', structuredData, bodyHtml }), 'utf8');
  productCount += 1;
}

console.log(`SEO pré-renderizado para a página inicial, ${Object.keys(staticPages).length} páginas e ${productCount} produtos.`);
