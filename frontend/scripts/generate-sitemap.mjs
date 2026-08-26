import { readFile, stat, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const frontendDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const catalogPath = path.resolve(frontendDir, '..', 'backend', 'database', 'catalog.json');
const outputPath = path.join(frontendDir, 'public', 'sitemap.xml');
const siteUrl = (process.env.VITE_SITE_URL || 'https://paineldecomando.com.br').replace(/\/$/, '');
const catalog = JSON.parse(await readFile(catalogPath, 'utf8'));
const lastmod = (await stat(catalogPath)).mtime.toISOString().slice(0, 10);
const staticPaths = ['/', '/empresa', '/produtos', '/servicos', '/segmentos', '/projetos', '/blog', '/contato'];
const catalogLines = ['estrela-triangulo', 'soft-starter', 'inversor-de-frequencia', 'bomba-de-incendio', 'irrigacao', 'revezamento'];
const productPaths = catalog.filter(product => product.status === 'published').map(product => `/produtos/${product.slug}`);

const escapeXml = value => value.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
const urls = [
  ...staticPaths,
  ...catalogLines.map(line => `/produtos?linha=${line}`),
  ...productPaths,
];
const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.map(url => `  <url><loc>${escapeXml(`${siteUrl}${url}`)}</loc><lastmod>${lastmod}</lastmod></url>`).join('\n')}
</urlset>
`;

await writeFile(outputPath, xml, 'utf8');
console.log(`Sitemap gerado com ${urls.length} URLs em ${outputPath}`);
