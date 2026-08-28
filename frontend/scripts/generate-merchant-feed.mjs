import { readFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const frontendDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const catalogPath = path.resolve(frontendDir, '..', 'backend', 'database', 'catalog.json');
const outputPath = path.join(frontendDir, 'public', 'google-merchant-feed.xml');
const siteUrl = (process.env.VITE_SITE_URL || 'https://paineldecomando.com.br').replace(/\/$/, '');
const catalog = JSON.parse(await readFile(catalogPath, 'utf8'));

const escapeXml = value => String(value ?? '')
  .replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;')
  .replaceAll('"', '&quot;')
  .replaceAll("'", '&apos;');
const absoluteUrl = value => /^https?:\/\//i.test(value) ? value : `${siteUrl}${value.startsWith('/') ? value : `/${value}`}`;
const cleanText = value => String(value ?? '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
const availability = value => value === 'out_of_stock' ? 'out_of_stock' : value === 'on_demand' ? 'preorder' : 'in_stock';

const products = catalog.filter(product => product.status === 'published' && product.price_cents != null && product.featured_image);
const items = products.map(product => `    <item>
      <g:id>${escapeXml(product.reference_code || product.slug)}</g:id>
      <title>${escapeXml(product.name)}</title>
      <description>${escapeXml(cleanText(product.summary || product.description).slice(0, 5000))}</description>
      <link>${escapeXml(`${siteUrl}/produtos/${product.slug}`)}</link>
      <g:image_link>${escapeXml(absoluteUrl(product.featured_image))}</g:image_link>
      <g:availability>${availability(product.stock_status)}</g:availability>
      <g:price>${(product.price_cents / 100).toFixed(2)} BRL</g:price>
      <g:condition>new</g:condition>
      <g:brand>${escapeXml(product.brand || 'Painel de Comando')}</g:brand>
      <g:mpn>${escapeXml(product.reference_code || product.slug)}</g:mpn>
      <g:product_type>${escapeXml(product.category_name || 'Painéis de comando elétrico')}</g:product_type>
    </item>`).join('\n');

const xml = `<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
  <channel>
    <title>Painel de Comando — Catálogo de produtos</title>
    <link>${siteUrl}/produtos</link>
    <description>Painéis de comando elétrico para motores, bombas, irrigação e máquinas industriais.</description>
${items}
  </channel>
</rss>
`;

await writeFile(outputPath, xml, 'utf8');
console.log(`Feed do Google Merchant Center gerado com ${products.length} produtos em ${outputPath}`);
