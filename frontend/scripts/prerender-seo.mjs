import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const frontendDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const distDir = path.join(frontendDir, 'dist');
const catalog = JSON.parse(await readFile(path.resolve(frontendDir, '..', 'backend', 'database', 'catalog.json'), 'utf8'));
const template = await readFile(path.join(distDir, 'index.html'), 'utf8');
const siteUrl = (process.env.VITE_SITE_URL || 'https://paineldecomando.com.br').replace(/\/$/, '');
const googleVerification = (process.env.VITE_GOOGLE_SITE_VERIFICATION || '').trim();
const companyLegalName = process.env.VITE_COMPANY_LEGAL_NAME || 'SMARTFLOW TECNOLOGIA EIRELI';
const companyPhone = process.env.VITE_COMPANY_PHONE || '+55 11 96919-5102';
const companyEmail = process.env.VITE_COMPANY_EMAIL || 'contato@paineldecomando.com.br';

const homePage = {
  title: 'Painéis de Comando Elétrico e Automação Industrial | Painel de Comando',
  description: 'Painéis de comando elétrico para motores, bombas, irrigação e máquinas. Compare modelos ou solicite um painel industrial sob medida com entrega para todo o Brasil.',
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

const commercialPages = {
  'painel-de-comando-eletrico': {
    title: 'Painel de Comando Elétrico para Motores e Máquinas',
    description: 'Painéis de comando elétrico para partida, proteção e controle de motores, bombas, irrigação e máquinas industriais. Veja modelos e solicite orçamento.',
    heading: 'Painel de comando elétrico para motores e máquinas',
    intro: 'Encontre painéis de comando elétrico para partida, proteção e controle de motores em aplicações industriais, sistemas de bombeamento e irrigação.',
    questions: [
      ['Como escolher o painel de comando correto?', 'A escolha considera principalmente aplicação, potência do motor, tensão da rede, corrente nominal, quantidade de partidas e forma de acionamento.'],
      ['O painel já chega pronto para instalação?', 'O produto é preparado para integração à aplicação descrita. A instalação deve ser realizada por profissional habilitado, seguindo o projeto e as normas aplicáveis.'],
      ['É possível solicitar uma configuração diferente?', 'Sim. Quando um modelo de catálogo não atende ao projeto, é possível solicitar uma análise para configuração sob medida.'],
    ],
  },
  'paineis-eletricos-sob-medida': {
    title: 'Painéis Elétricos Industriais Sob Medida | Orçamento',
    description: 'Solicite painéis elétricos industriais sob medida para máquinas, motores, bombas e automação. Configuração conforme os dados da sua aplicação.',
    heading: 'Painéis elétricos industriais sob medida',
    intro: 'Desenvolvimento de painéis elétricos sob medida para máquinas, motores, bombas e processos que exigem uma configuração diferente dos modelos padronizados.',
    questions: [
      ['Quais informações são necessárias para o orçamento?', 'Informe a aplicação, potência e tensão dos motores, quantidade de cargas, forma de acionamento, local de instalação e funções esperadas.'],
      ['Vocês adaptam um painel já existente?', 'A possibilidade depende do estado, da documentação e do escopo. Envie fotos e dados do painel para uma avaliação inicial.'],
      ['O orçamento de painel sob medida é imediato?', 'Projetos especiais precisam de análise. O prazo da proposta varia conforme a quantidade de cargas e a complexidade funcional.'],
    ],
  },
  'montagem-de-paineis-eletricos-industriais': {
    title: 'Montagem de Painéis Elétricos Industriais',
    description: 'Montagem de painéis elétricos industriais para comando, acionamento e proteção, com organização dos componentes e verificação funcional.',
    heading: 'Montagem de painéis elétricos industriais',
    intro: 'Montagem de painéis elétricos industriais para comando, acionamento e proteção, com organização dos componentes e verificação funcional antes da entrega.',
    questions: [
      ['Vocês montam painéis a partir de um projeto existente?', 'Sim, o projeto pode ser analisado para verificar escopo, documentação, componentes e condições de fornecimento.'],
      ['Quais tipos de painéis são montados?', 'O catálogo inclui partidas de motores, soft starters, inversores de frequência, bombas, irrigação e revezamento, além de configurações especiais sob análise.'],
      ['A instalação em campo está incluída?', 'As condições de cada fornecimento são apresentadas na proposta. A instalação elétrica deve sempre ser executada por profissionais habilitados.'],
    ],
  },
  'painel-de-comando-sorocaba': {
    title: 'Painel de Comando em Sorocaba | Atendimento e Orçamento',
    description: 'Painéis de comando elétrico em Sorocaba para indústrias, bombas e irrigação, com compra assistida e entregas para todo o Brasil.',
    heading: 'Painel de comando em Sorocaba e atendimento para todo o Brasil',
    intro: 'Atendimento em Sorocaba para seleção e orçamento de painéis de comando elétrico, com entrega de produtos para aplicações industriais, bombas e irrigação em todo o Brasil.',
    questions: [
      ['Vocês atendem somente Sorocaba?', 'Não. As entregas são realizadas em território brasileiro e o atendimento comercial também pode ser feito por telefone, e-mail e WhatsApp.'],
      ['Posso solicitar orçamento pelo WhatsApp?', 'Sim. Envie os dados do equipamento e informe potência, tensão, aplicação e cidade.'],
      ['Existe painel pronto para compra?', 'Sim. O catálogo apresenta modelos com características e preços. Projetos que exigem alterações seguem para cotação técnica.'],
    ],
  },
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
    {
      '@type': 'Organization',
      '@id': `${siteUrl}/#organization`,
      name: 'Painel de Comando',
      legalName: companyLegalName,
      url: `${siteUrl}/`,
      logo: absoluteUrl('/brand/painel-de-comando-raio-favicon.png'),
      email: companyEmail,
      telephone: companyPhone,
      address: { '@type': 'PostalAddress', streetAddress: 'Rua Cabreúva', addressLocality: 'Sorocaba', addressRegion: 'SP', postalCode: '18085-340', addressCountry: 'BR' },
      contactPoint: { '@type': 'ContactPoint', telephone: companyPhone, contactType: 'sales', areaServed: 'BR', availableLanguage: 'Portuguese' },
    },
    { '@type': 'WebSite', '@id': `${siteUrl}/#website`, name: 'Painel de Comando', url: `${siteUrl}/`, inLanguage: 'pt-BR', publisher: { '@id': `${siteUrl}/#organization` } },
  ],
};

const homeBody = `
      <main id="conteudo" data-seo-fallback>
        <h1>Painéis de comando elétrico e automação industrial</h1>
        <p>${escapeHtml(homePage.description)}</p>
        <nav aria-label="Principais páginas">
          <a href="/produtos">Ver painéis de comando</a>
          <a href="/painel-de-comando-eletrico">Painel de comando elétrico</a>
          <a href="/paineis-eletricos-sob-medida">Painéis elétricos sob medida</a>
          <a href="/montagem-de-paineis-eletricos-industriais">Montagem de painéis elétricos</a>
          <a href="/painel-de-comando-sorocaba">Atendimento em Sorocaba</a>
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

for (const [route, page] of Object.entries(commercialPages)) {
  const canonical = `${siteUrl}/${route}`;
  const structuredData = {
    '@context': 'https://schema.org',
    '@graph': [
      { '@type': 'Service', name: page.title, description: page.description, url: canonical, areaServed: { '@type': 'Country', name: 'Brasil' }, provider: { '@type': 'Organization', name: 'Painel de Comando', url: `${siteUrl}/` } },
      { '@type': 'FAQPage', mainEntity: page.questions.map(([question, answer]) => ({ '@type': 'Question', name: question, acceptedAnswer: { '@type': 'Answer', text: answer } })) },
    ],
  };
  const bodyHtml = `
      <main id="conteudo" data-seo-fallback>
        <nav aria-label="Navegação estrutural"><a href="/">Início</a> / <a href="/produtos">Produtos</a></nav>
        <article>
          <h1>${escapeHtml(page.heading)}</h1>
          <p>${escapeHtml(page.intro)}</p>
          <p>${escapeHtml(page.description)}</p>
          <p><a href="/orcamento">Solicitar orçamento</a> · <a href="/produtos">Ver painéis de comando</a></p>
          <section><h2>Perguntas frequentes</h2>${page.questions.map(([question, answer]) => `<h3>${escapeHtml(question)}</h3><p>${escapeHtml(answer)}</p>`).join('')}</section>
        </article>
      </main>`;
  await writeFile(path.join(distDir, `${route}.html`), renderSeo({ title: page.title, description: page.description, canonical, structuredData, bodyHtml }), 'utf8');
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

console.log(`SEO pré-renderizado para a página inicial, ${Object.keys(staticPages).length + Object.keys(commercialPages).length} páginas e ${productCount} produtos.`);
