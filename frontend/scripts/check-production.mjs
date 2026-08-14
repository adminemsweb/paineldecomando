import { existsSync, readFileSync } from 'node:fs';

const values = { ...process.env };
if (existsSync('.env.production')) {
  for (const rawLine of readFileSync('.env.production', 'utf8').split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith('#') || !line.includes('=')) continue;
    const [key, ...parts] = line.split('=');
    values[key.trim()] ??= parts.join('=').trim().replace(/^['"]|['"]$/g, '');
  }
}

const required = ['VITE_COMPANY_PHONE', 'VITE_COMPANY_WHATSAPP', 'VITE_COMPANY_WHATSAPP_LABEL', 'VITE_COMPANY_ADDRESS', 'VITE_COMPANY_HOURS'];
const missing = required.filter((key) => !values[key] || /CONFIGURAR|EXEMPLO|000000000/i.test(values[key]));
if (missing.length) {
  console.error(`Configuração de produção incompleta: ${missing.join(', ')}`);
  process.exit(1);
}
console.log('Configuração pública de produção validada.');
