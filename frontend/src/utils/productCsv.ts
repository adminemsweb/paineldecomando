export type ProductCsvData = {
  id: number;
  name: string;
  slug: string;
  summary: string | null;
  description: string | null;
  features: string[];
  benefits: string[];
  components: string[];
  voltages: string | null;
  power_range: string | null;
  protection_rating: string | null;
  image_url: string | null;
  gallery_images: string[];
  video_url: string | null;
  video_urls: string[];
  category_name: string | null;
  reference_code: string | null;
  brand: string | null;
  model: string | null;
  price_cents: number | null;
  installments: number;
  stock_status: 'in_stock' | 'out_of_stock' | 'on_demand';
  stock_quantity: number;
  lead_time: string | null;
  sales_channel: 'site' | 'whatsapp' | 'both';
  warranty_days: number;
  sort_order: number;
  featured: boolean;
  status: 'draft' | 'published' | 'archived';
  updated_at: string;
};

function csvCell(value: string | number | boolean | null | undefined) {
  const normalized = String(value ?? '').replace(/\r?\n/g, ' | ');
  const safe = /^[=+\-@]/.test(normalized) ? `'${normalized}` : normalized;
  return `"${safe.replace(/"/g, '""')}"`;
}

function stockLabel(product: ProductCsvData) {
  if (product.stock_status === 'in_stock') return 'Em estoque';
  if (product.stock_status === 'out_of_stock') return 'Sem estoque';
  return 'Sob encomenda';
}

function publicationLabel(product: ProductCsvData) {
  if (product.status === 'published') return 'Publicado';
  if (product.status === 'draft') return 'Rascunho';
  return 'Arquivado';
}

export function buildProductsCsv(products: ProductCsvData[]) {
  const siteUrl = (import.meta.env.VITE_SITE_URL ?? 'https://paineldecomando.com.br').replace(/\/$/, '');
  const headers = [
    'ID', 'Produto', 'Tipo de painel', 'Referência', 'Modelo', 'Marca', 'Endereço', 'Resumo', 'Descrição',
    'Preço (R$)', 'Parcelas', 'Estoque', 'Quantidade', 'Disponibilidade', 'Canal de venda', 'Potência', 'Tensões',
    'Grau de proteção', 'Garantia (dias)', 'Características', 'Componentes', 'Benefícios', 'Destaque',
    'Status', 'Ordem', 'Última atualização', 'URL do produto', 'Imagem principal', 'Galeria', 'Vídeos',
  ];
  const rows = products.map(product => [
    product.id,
    product.name,
    product.category_name || 'Sem categoria',
    product.reference_code,
    product.model,
    product.brand,
    product.slug,
    product.summary,
    product.description,
    product.price_cents == null ? '' : (product.price_cents / 100).toFixed(2).replace('.', ','),
    product.installments,
    stockLabel(product),
    product.stock_quantity,
    product.lead_time,
    product.sales_channel === 'both' ? 'Site e WhatsApp' : product.sales_channel === 'site' ? 'Site' : 'WhatsApp',
    product.power_range,
    product.voltages,
    product.protection_rating,
    product.warranty_days,
    product.features.join(' | '),
    product.components.join(' | '),
    product.benefits.join(' | '),
    product.featured ? 'Sim' : 'Não',
    publicationLabel(product),
    product.sort_order,
    new Date(product.updated_at).toLocaleDateString('pt-BR'),
    `${siteUrl}/produtos/${product.slug}`,
    product.image_url,
    product.gallery_images.join(' | '),
    (product.video_urls.length ? product.video_urls : product.video_url ? [product.video_url] : []).join(' | '),
  ]);
  return [headers, ...rows].map(row => row.map(csvCell).join(';')).join('\r\n');
}
