import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const frontendDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const distDir = path.join(frontendDir, 'dist');
const catalog = JSON.parse(await readFile(path.resolve(frontendDir, '..', 'backend', 'database', 'catalog.json'), 'utf8'));
const template = await readFile(path.join(distDir, 'index.html'), 'utf8');
const siteUrl = (process.env.VITE_SITE_URL || 'https://paineldecomando.com.br').replace(/\/$/, '');

const staticPages = {
  empresa: ['Empresa de Painéis de Comando Elétrico | Painel de Comando', 'Conheça a Painel de Comando e nossas soluções em painéis elétricos, acionamentos e automação industrial.'],
  produtos: ['Painéis de Comando Elétrico: Produtos e Modelos', 'Compare modelos de painéis de comando, estrela-triângulo, soft starter, inversor de frequência, irrigação e bombas.'],
  servicos: ['Serviços em Painéis Elétricos e Automação | Painel de Comando', 'Projetos, montagem, retrofit, instalação, comissionamento e manutenção de painéis elétricos e sistemas de automação.'],
  segmentos: ['Painéis Elétricos para Indústria e Infraestrutura', 'Soluções em painéis de comando para indústria, saneamento, irrigação, construção, agronegócio e infraestrutura.'],
  projetos: ['Projetos de Painéis de Comando | Painel de Comando', 'Projetos e aplicações de painéis elétricos de comando, acionamentos e automação industrial.'],
  blog: ['Conteúdo Técnico sobre Painéis de Comando', 'Artigos técnicos sobre painéis elétricos, partidas de motores, inversores, soft starters, automação e manutenção.'],
  contato: ['Contato e Orçamento | Painel de Comando', 'Fale com a equipe da Painel de Comando e solicite orientação comercial ou orçamento para sua aplicação.'],
};

const escapeAttribute = value => String(value).replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
const absoluteUrl = value => !value ? `${siteUrl}/brand/painel-de-comando-raio-favicon.png` : /^https?:\/\//i.test(value) ? value : `${siteUrl}${value.startsWith('/') ? value : `/${value}`}`;

function renderSeo({ title, description, canonical, image, type = 'website', structuredData }) {
  let html = template
    .replace(/<title>.*?<\/title>/s, `<title>${escapeAttribute(title)}</title>`)
    .replace(/<meta name="description"[^>]*>/, `<meta name="description" content="${escapeAttribute(description)}" />`)
    .replace(/<link rel="canonical"[^>]*>/, `<link rel="canonical" href="${escapeAttribute(canonical)}" />`)
    .replace(/<meta property="og:type"[^>]*>/, `<meta property="og:type" content="${type}" />`)
    .replace(/<meta property="og:title"[^>]*>/, `<meta property="og:title" content="${escapeAttribute(title)}" />`)
    .replace(/<meta property="og:description"[^>]*>/, `<meta property="og:description" content="${escapeAttribute(description)}" />`)
    .replace(/<meta property="og:url"[^>]*>/, `<meta property="og:url" content="${escapeAttribute(canonical)}" />`)
    .replace(/<meta property="og:image"[^>]*>/, `<meta property="og:image" content="${escapeAttribute(absoluteUrl(image))}" />`);
  if (structuredData) {
    const json = JSON.stringify(structuredData).replaceAll('<', '\\u003c');
    html = html.replace('</head>', `    <script type="application/ld+json">${json}</script>\n  </head>`);
  }
  return html;
}

for (const [route, [title, description]] of Object.entries(staticPages)) {
  await writeFile(path.join(distDir, `${route}.html`), renderSeo({ title, description, canonical: `${siteUrl}/${route}` }), 'utf8');
}

const productDir = path.join(distDir, 'produtos');
await mkdir(productDir, { recursive: true });
let productCount = 0;
for (const product of catalog.filter(item => item.status === 'published')) {
  const title = product.seo_title || `${product.name} | Painel de Comando`;
  const description = product.seo_description || product.summary || `Conheça ${product.name}.`;
  const canonical = `${siteUrl}/produtos/${product.slug}`;
  const images = [product.featured_image, ...JSON.parse(product.gallery_images || '[]')].filter(Boolean).map(absoluteUrl);
  const availability = product.stock_status === 'out_of_stock' ? 'https://schema.org/OutOfStock' : product.stock_status === 'on_demand' ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock';
  const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description,
    image: images,
    sku: product.reference_code || product.slug,
    brand: { '@type': 'Brand', name: product.brand || 'Painel de Comando' },
    url: canonical,
    ...(product.price_cents != null ? { offers: { '@type': 'Offer', priceCurrency: 'BRL', price: (product.price_cents / 100).toFixed(2), availability, url: canonical } } : {}),
  };
  await writeFile(path.join(productDir, `${product.slug}.html`), renderSeo({ title, description, canonical, image: product.featured_image, type: 'product', structuredData }), 'utf8');
  productCount += 1;
}

console.log(`SEO pré-renderizado para ${Object.keys(staticPages).length} páginas e ${productCount} produtos.`);
