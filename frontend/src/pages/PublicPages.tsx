import { FormEvent, Fragment, useEffect, useMemo, useState } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { Icon } from '../components/common/Icon';
import { PaymentIcon } from '../components/common/PaymentIcon';
import { ShippingCalculator } from '../components/common/ShippingCalculator';
import { companyConfig } from '../constants/company';
import { ApiError, apiRequest } from '../services/api';
import { Customer, useAuth } from '../auth/AuthContext';
import { PolicyDocument } from './policies/PolicyDocument';
import { TechnicalTicketPage } from './TechnicalTicketPage';

const labels: Record<string, { eyebrow: string; title: string; body: string }> = {
  empresa: { eyebrow: 'Quem somos', title: 'Engenharia organizada em torno da sua necessidade', body: 'Esta área receberá história, estrutura, capacidade técnica, missão, visão, valores e processo de qualidade após validação das informações empresariais.' },
  produtos: { eyebrow: 'Loja técnica', title: 'Painéis e soluções industriais', body: 'Compare aplicações, recursos e configurações. Produtos padronizados poderão ser comprados diretamente; projetos especiais seguem para cotação técnica.' },
  servicos: { eyebrow: 'Atuação técnica', title: 'Serviços', body: 'Projetos, montagem, programação, retrofit, instalação, comissionamento, manutenção e assistência técnica.' },
  segmentos: { eyebrow: 'Aplicações', title: 'Segmentos atendidos', body: 'Soluções orientadas às necessidades de indústria, saneamento, irrigação, construção, mineração, alimentos, agronegócio e infraestrutura.' },
  projetos: { eyebrow: 'Experiência aplicada', title: 'Projetos realizados', body: 'Estudos de caso serão publicados somente com informações e imagens autorizadas.' },
  blog: { eyebrow: 'Conteúdo técnico', title: 'Conhecimento para decisões mais seguras', body: 'Artigos sobre painéis, acionamentos, automação, manutenção e boas práticas de projeto.' },
};

export function ListingPage({ kind }: { kind: keyof typeof labels }) {
  const copy = labels[kind];
  if (kind === 'produtos') return <ManagedStorefrontListing copy={copy}/>;
  return <><PageHero {...copy}/><section className="section"><div className="container empty-state"><span aria-hidden="true">⌁</span><h2>Conteúdo demonstrativo</h2><p>Os registros desta seção virão da API e serão administráveis. A base de rota, carregamento e integração está pronta para a próxima etapa.</p><ButtonLink to="/orcamento">Solicitar uma solução</ButtonLink></div></section></>;
}

export function DetailPage({ kind }: { kind: string }) {
  const { slug } = useParams();
  if (kind === 'produto' && slug) return <StarDeltaProduct key={slug} slug={slug}/>;
  return <><PageHero eyebrow="Detalhe técnico" title={slug?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') ?? kind} body={`Página individual de ${kind}, preparada para receber conteúdo da API.`}/><section className="section"><div className="container split"><div><h2>Informações da solução</h2><p>Características, aplicações, benefícios, especificações, mídia e relações serão exibidos aqui após o cadastro administrativo.</p></div><div><ButtonLink to={`/orcamento?interesse=${encodeURIComponent(slug ?? kind)}`}>Pedir orçamento</ButtonLink></div></div></section></>;
}

type ManagedProduct = { id:number; name:string; slug:string; summary?:string; description?:string; features?:string[]; benefits?:string[]; components?:string[]; voltages?:string; power_range?:string; protection_rating?:string; image_url?:string; gallery_images?:string[]; video_url?:string; video_urls?:string[]; category_name?:string; reference_code?:string; brand?:string; model?:string; price_cents?:number|null; installments?:number; stock_status?:'in_stock'|'out_of_stock'|'on_demand'; stock_quantity?:number; lead_time?:string; sales_channel?:'site'|'whatsapp'|'both'; warranty_days?:number; featured?:boolean };

const catalogLines = [
  { slug: 'todos', label: 'Todos os produtos', title: 'Painéis e soluções industriais', description: 'Encontre painéis elétricos, sistemas de partida e soluções de automação para sua aplicação.', terms: [] },
  { slug: 'estrela-triangulo', label: 'Painel Estrela-Triângulo', title: 'Painéis Estrela-Triângulo', description: 'Compare todos os modelos Estrela-Triângulo publicados, suas potências, tensões e configurações.', terms: ['estrela triangulo', 'estrela-triangulo'] },
  { slug: 'soft-starter', label: 'Painel com Soft Starter', title: 'Painéis com Soft Starter', description: 'Soluções para partidas e paradas suaves, com proteção e dimensionamento para cada motor.', terms: ['soft starter', 'soft-starter'] },
  { slug: 'inversor-de-frequencia', label: 'Painel com Inversor', title: 'Painéis com Inversor de Frequência', description: 'Controle de velocidade e processo com inversores dimensionados para aplicações industriais.', terms: ['inversor'] },
  { slug: 'bomba-de-incendio', label: 'Bomba de Incêndio', title: 'Painéis para Bombas de Incêndio', description: 'Comando, supervisão e proteção para sistemas de combate a incêndio.', terms: ['incendio'] },
  { slug: 'irrigacao', label: 'Painel para Irrigação', title: 'Painéis para Irrigação', description: 'Controle confiável de bombas e sistemas de irrigação.', terms: ['irrigacao'] },
  { slug: 'revezamento', label: 'Revezamento de Bombas', title: 'Painéis para Revezamento de Bombas', description: 'Alternância automática para equilibrar o funcionamento e aumentar a vida útil das bombas.', terms: ['revezamento'] },
] as const;

const starDeltaSubcategories = [
  { slug: 'economico', label: 'Painel Estrela Triângulo Econômico', terms: ['eco', 'economico'] },
  { slug: 'padrao', label: 'Painel Estrela Triângulo Padrão', terms: ['padrao'] },
  { slug: 'com-amperimetro', label: 'Painel Estrela Triângulo Com Amperímetro', terms: ['amperimetro'] },
] as const;

function normalizeCatalogText(value: string) {
  return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function productMatchesLine(product: ManagedProduct, terms: readonly string[]) {
  if (terms.length === 0) return true;
  const haystack = normalizeCatalogText(`${product.name} ${product.slug} ${product.category_name ?? ''}`);
  return terms.some(term => haystack.includes(normalizeCatalogText(term)));
}

function productPrice(product: ManagedProduct) {
  return product.price_cents == null
    ? 'Preço sob consulta'
    : (product.price_cents / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function productInstallment(product: ManagedProduct) {
  const installments = Math.max(1, product.installments ?? 1);
  if (product.price_cents == null || installments < 2) return null;
  const installmentPrice = (product.price_cents / installments / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  return `ou ${installments}x de ${installmentPrice} sem juros`;
}

function ManagedStorefrontListing({ copy }: { copy: { eyebrow: string; title: string; body: string } }) {
  const [products, setProducts] = useState<ManagedProduct[] | null>(null);
  const [failed, setFailed] = useState(false);
  const [searchParams] = useSearchParams();
  const searchFromUrl = searchParams.get('busca') ?? '';
  const [searchState, setSearchState] = useState({ source: searchFromUrl, value: searchFromUrl });
  const search = searchState.source === searchFromUrl ? searchState.value : searchFromUrl;
  const [availability, setAvailability] = useState<'all' | 'available' | 'on_demand'>('all');
  const [sort, setSort] = useState<'featured' | 'name' | 'price_asc' | 'price_desc'>('featured');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const legacyLine = searchParams.get('categoria') ?? searchParams.get('aplicacao');
  const requestedLine = searchParams.get('linha') ?? ({ incendio: 'bomba-de-incendio', irrigacao: 'irrigacao', revezamento: 'revezamento', 'soft-starter': 'soft-starter', 'inversor-de-frequencia': 'inversor-de-frequencia' }[legacyLine ?? ''] ?? 'todos');
  const activeLine = catalogLines.find(line => line.slug === requestedLine) ?? catalogLines[0];
  const requestedSubcategory = searchParams.get('subcategoria');
  const activeSubcategory = activeLine.slug === 'estrela-triangulo' ? starDeltaSubcategories.find(category => category.slug === requestedSubcategory) : undefined;
  useEffect(() => {
    let active = true;
    async function loadProducts() {
      const firstPage = await apiRequest<ManagedProduct[]>('/products?per_page=50&page=1');
      const totalPages = firstPage.meta?.total_pages ?? 1;
      const remainingPages = totalPages > 1
        ? await Promise.all(Array.from({ length: totalPages - 1 }, (_, index) => apiRequest<ManagedProduct[]>(`/products?per_page=50&page=${index + 2}`)))
        : [];
      if (active) setProducts([firstPage, ...remainingPages].flatMap(response => response.data));
    }
    loadProducts().catch(() => { if (active) setFailed(true); });
    return () => { active = false; };
  }, []);
  const lineCounts = useMemo(() => Object.fromEntries(catalogLines.map(line => [line.slug, (products ?? []).filter(product => productMatchesLine(product, line.terms)).length])), [products]);
  const subcategoryCounts = useMemo(() => Object.fromEntries(starDeltaSubcategories.map(category => [category.slug, (products ?? []).filter(product => productMatchesLine(product, catalogLines[1].terms) && productMatchesLine(product, category.terms)).length])), [products]);
  const visibleProducts = useMemo(() => {
    const normalizedSearch = normalizeCatalogText(search.trim());
    const filtered = (products ?? []).filter(product => {
      if (!productMatchesLine(product, activeLine.terms)) return false;
      if (activeSubcategory && !productMatchesLine(product, activeSubcategory.terms)) return false;
      const searchable = normalizeCatalogText(`${product.name} ${product.slug} ${product.summary ?? ''} ${product.category_name ?? ''} ${product.reference_code ?? ''} ${product.voltages ?? ''}`);
      if (normalizedSearch && !searchable.includes(normalizedSearch)) return false;
      if (availability === 'available' && product.stock_status !== 'in_stock') return false;
      if (availability === 'on_demand' && product.stock_status !== 'on_demand') return false;
      if (minPrice !== '' && (product.price_cents == null || product.price_cents < Number(minPrice) * 100)) return false;
      if (maxPrice !== '' && (product.price_cents == null || product.price_cents > Number(maxPrice) * 100)) return false;
      return true;
    });
    return filtered.sort((a, b) => {
      if (sort === 'name') return a.name.localeCompare(b.name, 'pt-BR');
      if (sort === 'price_asc') return (a.price_cents ?? Number.MAX_SAFE_INTEGER) - (b.price_cents ?? Number.MAX_SAFE_INTEGER);
      if (sort === 'price_desc') return (b.price_cents ?? -1) - (a.price_cents ?? -1);
      return Number(Boolean(b.featured)) - Number(Boolean(a.featured));
    });
  }, [activeLine, activeSubcategory, availability, maxPrice, minPrice, products, search, sort]);
  if (failed) return <StorefrontListing copy={copy}/>;
  return <>
    <section className="catalog-page"><div className="container catalog-layout">
      <aside className="catalog-sidebar">
        <div><span className="catalog-sidebar__title">Categorias <b>‹</b></span><nav aria-label="Categorias de produtos" className="catalog-category-list">{catalogLines.filter(line => products === null || line.slug === 'todos' || (lineCounts[line.slug] ?? 0) > 0).map(line => <Fragment key={line.slug}><Link className={line.slug === activeLine.slug && !activeSubcategory ? 'active' : ''} to={line.slug === 'todos' ? '/produtos' : `/produtos?linha=${line.slug}`}><i/><span>{line.label}</span><b>{lineCounts[line.slug] ?? 0}</b></Link>{line.slug === 'estrela-triangulo' && activeLine.slug === 'estrela-triangulo' && <div className="catalog-subcategory-list">{starDeltaSubcategories.map(category => <Link className={activeSubcategory?.slug === category.slug ? 'active' : ''} to={`/produtos?linha=estrela-triangulo&subcategoria=${category.slug}`} key={category.slug}><span>{category.label}</span><b>{subcategoryCounts[category.slug] ?? 0}</b></Link>)}</div>}</Fragment>)}</nav></div>
        <div><span className="catalog-sidebar__title">Preço <b>‹</b></span><div className="catalog-price-filter"><label><span>Valor mínimo</span><div><b>R$</b><input aria-label="Valor mínimo" min="0" type="number" value={minPrice} onChange={event => setMinPrice(event.target.value)} placeholder="0"/></div></label><label><span>Valor máximo</span><div><b>R$</b><input aria-label="Valor máximo" min="0" type="number" value={maxPrice} onChange={event => setMaxPrice(event.target.value)} placeholder="10.000"/></div></label></div></div>
        <div><span className="catalog-sidebar__title">Disponibilidade</span><div className="catalog-filter-options"><button className={availability === 'all' ? 'active' : ''} type="button" onClick={() => setAvailability('all')}><i/> Todas as condições</button><button className={availability === 'available' ? 'active' : ''} type="button" onClick={() => setAvailability('available')}><i/> Disponível</button><button className={availability === 'on_demand' ? 'active' : ''} type="button" onClick={() => setAvailability('on_demand')}><i/> Sob encomenda</button></div></div>
        <div><span className="catalog-sidebar__title">Marca <b>‹</b></span><div className="catalog-filter-options"><button className="active" type="button"><i/> Painel de Comando <b>{products?.length ?? 0}</b></button></div></div>
        <button className="catalog-filter-button" type="button">Filtrar</button>
      </aside>
      <div className="catalog-results">
        <header className="catalog-heading"><div className="catalog-heading__breadcrumb"><Link to="/">Início</Link><span>›</span>{activeSubcategory && <><Link to="/produtos?linha=estrela-triangulo">{activeLine.label}</Link><span>›</span></>}<strong>{activeSubcategory?.label || activeLine.label}</strong></div><div className="catalog-heading__row"><div><span>Catálogo profissional</span><h1>{activeSubcategory?.label || activeLine.title}</h1></div><label className="catalog-sort"><span>Ordenar por:</span><select value={sort} onChange={event => setSort(event.target.value as typeof sort)}><option value="featured">Destaques</option><option value="name">Nome</option><option value="price_asc">Menor preço</option><option value="price_desc">Maior preço</option></select></label></div><p>{activeLine.description}</p></header>
        <div className="catalog-toolbar"><label className="catalog-search"><Icon name="search" size={20}/><span className="sr-only">Buscar produtos</span><input value={search} onChange={event => setSearchState({ source: searchFromUrl, value: event.target.value })} placeholder="Buscar por produto, modelo ou aplicação"/></label><span className="catalog-count">{products === null ? 'Carregando…' : `${visibleProducts.length} ${visibleProducts.length === 1 ? 'produto encontrado' : 'produtos encontrados'}`}</span></div>
        {products !== null && visibleProducts.length === 0 && <div className="empty-state"><h2>Nenhum produto encontrado</h2><p>Tente outra busca ou consulte todas as categorias.</p><ButtonLink to="/produtos" variant="secondary">Ver todos os produtos</ButtonLink></div>}
        <div className="catalog-grid">{visibleProducts.map(product => {
          const isAvailable = product.stock_status === 'in_stock' && (product.stock_quantity ?? 1) > 0;
          const whatsappMessage = encodeURIComponent(`Olá! Quero saber mais sobre ${product.name}.`);
          const installmentText = productInstallment(product);
          return <article className="catalog-card" key={product.id}>
            <Link className="catalog-card__image" to={`/produtos/${product.slug}`}><span>{product.featured ? 'Destaque' : isAvailable ? 'Disponível' : 'Sob encomenda'}</span><img src={product.image_url || '/images/hero-painel-comando-poster.jpg'} alt={product.name}/></Link>
            <div className="catalog-card__body"><div className="catalog-card__meta"><strong>{product.category_name || 'Solução industrial'}</strong>{product.reference_code && <small>Ref. {product.reference_code}</small>}</div><h2><Link to={`/produtos/${product.slug}`}>{product.name}</Link></h2><p>{product.summary || 'Consulte as configurações disponíveis para esta solução.'}</p><dl><div><dt>Potência</dt><dd>{product.power_range || 'Sob dimensionamento'}</dd></div><div><dt>Tensão</dt><dd>{product.voltages || 'Consulte opções'}</dd></div></dl><div className="catalog-card__price"><strong>{productPrice(product)}</strong>{installmentText && <span>{installmentText}</span>}<small>{isAvailable ? `${product.stock_quantity ?? 0} un. disponíveis` : product.lead_time || 'Produção sob encomenda'}</small></div><a className="catalog-card__whatsapp" href={`https://wa.me/${companyConfig.whatsapp}?text=${whatsappMessage}`} target="_blank" rel="noreferrer"><Icon name="whatsapp" size={18}/> Comprar via WhatsApp</a><Link className="catalog-card__details" to={`/produtos/${product.slug}`}>Ver detalhes e especificações <span>→</span></Link></div>
          </article>;
        })}</div>
      </div>
    </div></section>
  </>;
}

function StorefrontListing({ copy }: { copy: { eyebrow: string; title: string; body: string } }) {
  return <><PageHero {...copy}/><section className="store-catalog"><div className="container store-catalog__layout">
    <aside className="store-filters"><span>Filtrar por</span><h2>Encontre a configuração certa</h2><label>Aplicação<select defaultValue=""><option value="">Todas</option><option>Bombas</option><option>Compressores</option><option>Ventiladores</option><option>Máquinas industriais</option></select></label><label>Tipo de acionamento<select defaultValue=""><option value="">Todos</option><option>Estrela-triângulo</option><option>Soft starter</option><option>Inversor de frequência</option></select></label><p>Precisa dimensionar tensão, potência ou proteção?</p><ButtonLink to="/orcamento" variant="secondary">Falar com a engenharia</ButtonLink></aside>
    <div className="store-results"><div className="store-results__heading"><div><span>Catálogo em expansão</span><strong>1 solução publicada</strong></div><select aria-label="Ordenar produtos" defaultValue="relevancia"><option value="relevancia">Mais relevantes</option><option value="nome">Nome</option></select></div>
      <article className="store-product-card"><Link className="store-product-card__image" to="/produtos/painel-estrela-triangulo"><img src="/images/painel-estrela-triangulo-15cv-principal.png" alt="Painel Estrela-Triângulo 15 CV aberto com identificação Painel de Comando"/><span>Disponível em 3 dias úteis</span></Link><div className="store-product-card__body"><small>Marca Painel de Comando · Painéis de partida</small><h2><Link to="/produtos/painel-estrela-triangulo">Painel Estrela Triângulo 15CV 220V Man/Aut. Eco</Link></h2><p>Partida segura para motores trifásicos com acionamento manual e automático.</p><ul><li>Potência de até 15 CV</li><li>Tensão trifásica de 220 V</li><li>Garantia de 1 ano</li></ul><div><span><small>Preço</small><strong>R$ 1.247,00</strong></span><ButtonLink to="/produtos/painel-estrela-triangulo">Ver produto</ButtonLink></div></div></article>
    </div>
  </div></section></>;
}

function RatingStars({ rating }: { rating: number }) {
  return <span className="rating-stars" aria-label={`${rating.toLocaleString('pt-BR')} de 5 estrelas`}>{[1, 2, 3, 4, 5].map(star => <i className={rating >= star ? 'rating-stars__star--full' : rating >= star - .5 ? 'rating-stars__star--half' : 'rating-stars__star--empty'} aria-hidden="true" key={star}>★</i>)}</span>;
}

function StarDeltaProduct({ slug = 'painel-estrela-triangulo' }: { slug?: string }) {
  const [media, setMedia] = useState('photo-0');
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState<'description' | 'warranty' | 'payment' | 'reviews'>('description');
  const [managed, setManaged] = useState<ManagedProduct | null>(null);
  useEffect(() => {
    apiRequest<ManagedProduct>(`/products/${slug}`).then(response => setManaged(response.data)).catch(() => undefined);
  }, [slug]);
  const productName = managed?.name || 'Painel Estrela Triângulo 15CV 220V Man/Aut. Eco';
  const omitBrandFromTitle = slug === 'painel-estrela-triangulo-7-5cv-380v-compressor';
  const productTitle = omitBrandFromTitle || productName.includes(' | ') ? productName : `${productName} | ${managed?.brand || companyConfig.name}`;
  const principalImage = managed?.image_url || '/images/painel-estrela-triangulo-15cv-principal.png';
  const photoUrls = managed ? [principalImage, ...(managed.gallery_images || [])].slice(0, 5) : [principalImage, '/images/painel-estrela-triangulo-15cv-fechado-logo.png', '/images/painel-estrela-triangulo-15cv-aberto-amarelo.png'];
  const videoUrls = managed ? (managed.video_urls?.length ? managed.video_urls : managed.video_url ? [managed.video_url] : []).slice(0, 2) : ['/videos/painelvideo.mp4'];
  const price = managed?.price_cents == null ? 'Sob consulta' : (managed.price_cents / 100).toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
  const installments = Math.max(1, managed?.installments || 3);
  const installmentPrice = managed?.price_cents == null ? null : (managed.price_cents / installments / 100).toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
  const isOutOfStock = managed?.stock_status === 'out_of_stock' || (managed?.stock_status === 'in_stock' && (managed.stock_quantity ?? 0) < 1);
  const stockText = isOutOfStock ? 'Produto sem estoque' : managed?.lead_time || (managed?.stock_status === 'on_demand' ? 'Produção sob encomenda' : 'Disponível em 3 dias úteis');
  const canBuyOnSite = managed?.sales_channel !== 'whatsapp' && !isOutOfStock && managed?.price_cents != null;
  const canBuyOnWhatsapp = managed?.sales_channel !== 'site';
  const maxQuantity = managed?.stock_status === 'in_stock' ? Math.max(1, managed.stock_quantity ?? 1) : 999;
  const technicalFeatures = ['Tensão: 220 V trifásico', 'Corrente: 40 A', 'Potência do motor: até 15 CV', 'Sistema: Estrela-Triângulo', 'Acionamento: Manual e Automático', 'Construção: caixa metálica reforçada com pintura eletrostática', 'Montagem: trilho DIN e canaletas industriais de alta qualidade'];
  const components = ['Caixa de comando metálica robusta', 'Contatores de potência: 25 A + 18 A (220 V)', 'Relé de sobrecarga: 22–32 A', 'Mini disjuntor tripolar: 63 A', 'Relé temporizador para controle da transição estrela-triângulo', 'Canaletas, trilho DIN e bornes industriais de alta durabilidade'];
  const benefits = ['Reduz a corrente de partida e o estresse do motor', 'Prolonga a vida útil do sistema elétrico', 'Protege contra curtos-circuitos e sobrecargas', 'Instalação prática e manutenção simplificada', 'Equipamento funcional, confiável e com excelente custo-benefício'];
  const managedFeatures = managed?.features?.length ? managed.features : technicalFeatures;
  const managedComponents = managed?.components?.length ? managed.components : components;
  const managedBenefits = managed?.benefits?.length ? managed.benefits : benefits;
  const reviews = [
    { name: 'Marcos A.', initials: 'MA', rating: 5, title: 'Produto conforme a especificação', text: 'Painel bem montado, com acabamento organizado e funcionamento de acordo com o esperado.', image: '/images/painel-estrela-triangulo-15cv-fechado-logo.png' },
    { name: 'Renato S.', initials: 'RS', rating: 4, title: 'Boa construção e acabamento', text: 'Equipamento funcional e bem protegido. A apresentação interna facilitou a conferência dos componentes.', image: '/images/painel-estrela-triangulo-15cv-aberto-amarelo.png' },
  ];
  const reviewRatings = reviews.map(review => review.rating);
  const averageRating = reviewRatings.reduce((total, rating) => total + rating, 0) / reviewRatings.length;
  const positiveReviews = reviewRatings.filter(rating => rating >= 4).length;
  const negativeReviews = reviewRatings.filter(rating => rating <= 2).length;
  const relatedProducts = [
    { name: 'Painel com Soft Starter', detail: 'Partidas e paradas suaves', image: '/images/montagem-painel-industrial-v2.jpg', to: '/produtos?categoria=soft-starter' },
    { name: 'Painel com Inversor de Frequência', detail: 'Controle preciso de velocidade', image: '/images/hero-painel-industrial-v2.jpg', to: '/produtos?categoria=inversor-de-frequencia' },
    { name: 'Painel para Bombas', detail: 'Proteção e alternância', image: '/images/hero-painel-comando-poster.jpg', to: '/produtos?categoria=bombas' },
  ];
  const normalizedProductType = normalizeCatalogText(`${managed?.name || ''} ${managed?.category_name || ''}`);
  const breadcrumb = normalizedProductType.includes('soft starter')
    ? { label: 'Painel com Soft Starter', to: '/produtos?linha=soft-starter' }
    : normalizedProductType.includes('inversor')
      ? { label: 'Inversor de Frequência', to: '/produtos?linha=inversor-de-frequencia' }
      : normalizedProductType.includes('incendio')
        ? { label: 'Bomba de Incêndio', to: '/produtos?linha=bomba-de-incendio' }
        : normalizedProductType.includes('irrigacao')
          ? { label: 'Irrigação', to: '/produtos?linha=irrigacao' }
          : normalizedProductType.includes('revezamento')
            ? { label: 'Revezamento de Bombas', to: '/produtos?linha=revezamento' }
            : { label: managed?.category_name || 'Painéis de partida', to: '/produtos?linha=estrela-triangulo' };
  return <>
    <div className="product-breadcrumb"><div className="container"><Link to="/">Início</Link><span>/</span><Link to="/produtos">Produtos</Link><span>/</span><Link to={breadcrumb.to}>{breadcrumb.label}</Link><span>/</span><strong>{productName}</strong></div></div>
    <section className="product-detail"><div className="container">
      <div className="product-detail__grid">
        <div className="product-gallery">
          <div className="product-gallery__layout">
            <div className="product-gallery__thumbs" aria-label="Mídia do produto">
              {photoUrls.map((url, index) => <button type="button" className={media === `photo-${index}` ? 'active' : ''} aria-pressed={media === `photo-${index}`} onClick={() => setMedia(`photo-${index}`)} key={`photo-${url}-${index}`}><img src={url} alt=""/><span>{index === 0 ? 'Principal' : `Foto ${index + 1}`}</span></button>)}
              {videoUrls.map((_, index) => <button type="button" className={media === `video-${index}` ? 'active' : ''} aria-pressed={media === `video-${index}`} onClick={() => setMedia(`video-${index}`)} key={`video-${index}`}><img src={principalImage} alt=""/><span>Vídeo {index + 1}</span></button>)}
            </div>
            <div className="product-gallery__stage">
              {media.startsWith('video-') ? <video autoPlay loop muted playsInline controls poster={principalImage} aria-label={`Vídeo demonstrativo de ${productName}`} src={videoUrls[Number(media.split('-')[1])]}/> : <img src={photoUrls[Number(media.split('-')[1])] || principalImage} alt={media === 'photo-0' ? productName : `${productName}, foto ${Number(media.split('-')[1]) + 1}`}/>} 
            </div>
          </div>
        </div>

        <div className="product-summary">
          <div className="product-summary__topline"><span className="product-summary__category">{managed?.category_name || 'Painéis de partida'}</span><span>Ref: {managed?.reference_code || 'PAINEL-E.T-15CV+MAN-AUT.ECO'}</span></div>
          <h1>{productTitle}</h1>
          <div className="product-rating-summary"><RatingStars rating={averageRating}/><strong>{averageRating.toFixed(1).replace('.', ',')}</strong><button type="button" onClick={() => setActiveTab('reviews')}>{reviewRatings.length} avaliações</button></div>
          <dl className="product-summary__meta"><div><dt>Marca</dt><dd>{managed?.brand || companyConfig.name}</dd></div><div><dt>Modelo</dt><dd>{managed?.model || managed?.category_name || 'Painel de comando'}</dd></div><div><dt>Disponibilidade</dt><dd className={isOutOfStock ? 'product-stock product-stock--out' : 'product-stock'}>{stockText}</dd></div><div><dt>Garantia</dt><dd>{managed?.warranty_days ?? 365} dias</dd></div></dl>

          <div className="product-buybox">
            <div className="product-buybox__price"><span>Preço à vista</span><strong>{price}</strong>{installmentPrice && <small>ou {installments}x de {installmentPrice} sem juros</small>}</div>
            <div className="product-buybox__quantity"><span>Quantidade</span><div><button type="button" aria-label="Diminuir quantidade" onClick={() => setQuantity(current => Math.max(1, current - 1))}>−</button><strong>{quantity}</strong><button type="button" aria-label="Aumentar quantidade" disabled={quantity >= maxQuantity} onClick={() => setQuantity(current => Math.min(maxQuantity, current + 1))}>+</button></div></div>
            {canBuyOnSite && <ButtonLink to={`/carrinho?produto=${encodeURIComponent(managed?.slug || slug)}&quantidade=${quantity}`}>Comprar agora</ButtonLink>}
            {canBuyOnWhatsapp && <a className="product-buybox__whatsapp" href={`https://wa.me/${companyConfig.whatsapp}?text=${encodeURIComponent(`Olá! Quero comprar ${quantity} ${productName}.`)}`} target="_blank" rel="noreferrer"><Icon name="whatsapp" size={18}/> Comprar pelo WhatsApp</a>}
            {isOutOfStock && <div className="product-out-of-stock" role="status">Produto indisponível no momento</div>}
            <ShippingCalculator variant="product"/>
          </div>
        </div>
      </div>
    </div></section>
    <section className="product-information"><div className="container"><div className="product-tabs" role="tablist" aria-label="Informações do produto"><button type="button" className={activeTab === 'description' ? 'active' : ''} onClick={() => setActiveTab('description')}>Descrição geral</button><button type="button" className={activeTab === 'warranty' ? 'active' : ''} onClick={() => setActiveTab('warranty')}>Garantia</button><button type="button" className={activeTab === 'payment' ? 'active' : ''} onClick={() => setActiveTab('payment')}>Formas de pagamento</button><button type="button" className={activeTab === 'reviews' ? 'active' : ''} onClick={() => setActiveTab('reviews')}>Avaliações ({reviewRatings.length})</button></div>
      <div className="product-tab-panel">
        {activeTab === 'description' && <div className="product-long-description"><header><span>Excelência em Engenharia e Qualidade {managed?.brand || companyConfig.name}</span><h2>{productTitle}</h2><p>{managed?.description || `Todos os produtos ${companyConfig.name} são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais antes do envio.`}</p></header><div className="product-description-columns"><article><h3>Características técnicas</h3><ul>{managedFeatures.map(item => <li key={item}>{item}</li>)}</ul></article><article><h3>Principais componentes</h3><ul>{managedComponents.map(item => <li key={item}>{item}</li>)}</ul></article><article><h3>Benefícios e diferenciais</h3><ul>{managedBenefits.map(item => <li key={item}>{item}</li>)}</ul></article></div><section><h3>Personalização sob medida</h3><p>Precisa adaptar o painel ao seu projeto? A {managed?.brand || companyConfig.name} oferece personalização técnica sob demanda, garantindo compatibilidade, desempenho e total segurança. Entre em contato e solicite o seu modelo personalizado.</p><ButtonLink to={`/orcamento?interesse=${encodeURIComponent(managed?.slug || slug)}`}>Solicitar modelo personalizado</ButtonLink></section><section><h3>Instalação e uso técnico</h3><p>Os produtos {managed?.brand || companyConfig.name} são desenvolvidos com alto padrão de qualidade e segurança. Por se tratarem de equipamentos técnicos, a instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado. Nosso suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas técnicas específicas sobre instalação devem ser direcionadas a profissionais habilitados da área elétrica.</p></section><aside><strong>Atenção</strong><p>Os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado através de flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.</p></aside></div>}
        {activeTab === 'warranty' && <div className="product-policy"><h2>1 ano após o recebimento do produto</h2><p>Todos os produtos comercializados pela Painel de Comando possuem garantia contra defeitos de fabricação, conforme as condições estabelecidas pelo fabricante. O prazo deste produto é de {managed?.warranty_days ?? 365} dias.</p><div className="product-description-columns"><article><h3>Acionamento da garantia</h3><p>Caso seja identificado algum defeito, entre em contato com a equipe para receber orientação. O produto poderá passar por análise técnica antes da aprovação da garantia.</p></article><article><h3>Condições importantes</h3><ul><li>Cobre exclusivamente defeitos de fabricação</li><li>Não cobre mau uso, instalação incorreta ou desgaste natural</li><li>A instalação deve ser realizada por profissional qualificado</li></ul></article><article><h3>Trocas e devoluções</h3><p>O produto deve ser enviado em sua embalagem original e sem sinais de uso indevido. Caso seja constatado mau uso, os custos de envio e reparo serão de responsabilidade do comprador.</p></article></div><h3>Suporte técnico</h3><p>Em caso de dúvidas, nossa equipe está disponível para orientar e buscar a melhor solução para sua aplicação.</p></div>}
        {activeTab === 'payment' && <div className="product-payment-options"><header><div><span>Compra segura e transparente</span><h2>Escolha como deseja pagar</h2><p>Confira valores e condições antes de concluir o pedido.</p></div><div className="product-payment-price"><small>Valor do produto</small><strong>{price}</strong>{installmentPrice && <span>ou {installments}x de {installmentPrice} sem juros</span>}</div></header><div className="product-payment-brands" aria-label="Meios de pagamento aceitos"><strong>Meios aceitos</strong><span className="payment-card" aria-label="Mastercard"><PaymentIcon name="mastercard"/></span><span className="payment-card" aria-label="Visa"><PaymentIcon name="visa"/></span><span className="payment-card payment-card--brand-wide" aria-label="Hipercard"><PaymentIcon name="hipercard"/></span><span className="payment-card" aria-label="Elo"><PaymentIcon name="elo"/></span><span className="payment-card payment-card--labeled payment-card--wide" aria-label="Pagamento parcelado"><PaymentIcon name="installment"/><b>Pagamento parcelado</b></span><span className="payment-card" aria-label="Pix"><PaymentIcon name="pix"/></span><span className="payment-card" aria-label="PayPal"><PaymentIcon name="paypal"/></span><span className="payment-card payment-card--brand-wide" aria-label="Google Pay"><PaymentIcon name="googlepay"/></span><span className="payment-card payment-card--labeled" aria-label="Boleto"><PaymentIcon name="boleto"/><b>Boleto</b></span><span className="payment-card payment-card--brand-wide" aria-label="PicPay"><PaymentIcon name="picpay"/></span></div><div className="product-payment-methods"><article><span className="product-payment-methods__icon"><PaymentIcon name="pix"/></span><div><small>À vista</small><strong>Pix ou depósito bancário</strong><p>Os dados para pagamento são confirmados no fechamento.</p></div></article><article><span className="product-payment-methods__icon"><PaymentIcon name="mastercard"/></span><div><small>Cartão de crédito</small><strong>Até {installments}x sem juros</strong><p>{installmentPrice ? `${installments} parcelas de ${installmentPrice} no cartão.` : 'Condições confirmadas no fechamento.'}</p></div></article><article><span className="product-payment-methods__icon"><PaymentIcon name="boleto"/></span><div><small>Boleto bancário</small><strong>Pagamento à vista</strong><p>A confirmação ocorre após a compensação bancária.</p></div></article></div><div className="product-payment-security"><div><Icon name="shield" size={21}/><span><strong>Pagamento protegido</strong><small>Você revisa os dados antes de confirmar</small></span></div><div><Icon name="check" size={21}/><span><strong>Condições transparentes</strong><small>Valor e parcelamento apresentados com clareza</small></span></div><div><Icon name="headset" size={21}/><span><strong>Suporte no atendimento</strong><small>Equipe disponível para orientar sua compra</small></span></div></div><small className="product-payment-note">Nenhuma cobrança é realizada sem sua confirmação. As condições finais são apresentadas no fechamento do pedido.</small></div>}
        {activeTab === 'reviews' && <div className="product-reviews"><header><span>Opinião de compradores</span><h2>Avaliações do produto</h2><p>A média é calculada automaticamente a partir de todas as notas recebidas.</p></header><div className="product-reviews__content"><aside className="product-reviews__summary"><small>Nota média</small><strong>{averageRating.toFixed(1).replace('.', ',')}</strong><RatingStars rating={averageRating}/><p>Baseado em {reviewRatings.length} avaliações</p><div className="product-reviews__sentiment"><span><b>{positiveReviews}</b> positivas</span><span><b>{negativeReviews}</b> negativas</span></div><div className="product-reviews__bars">{[5, 4, 3, 2, 1].map(score => { const total = reviewRatings.filter(rating => rating === score).length; return <div key={score}><b>{score}</b><span><i style={{ width: `${(total / reviewRatings.length) * 100}%` }}/></span><small>{total}</small></div>; })}</div></aside><div className="product-reviews__grid">{reviews.map((review, index) => <article key={review.name}><header><span className="product-reviewer__avatar" aria-hidden="true">{review.initials}</span><div><strong>{review.name}</strong><small>Conteúdo demonstrativo</small></div></header><div className="product-review__rating"><RatingStars rating={review.rating}/><b>{review.rating.toFixed(1).replace('.', ',')}</b></div><h3>{review.title}</h3><p>“{review.text}”</p><figure><img src={review.image} alt={`Imagem do produto anexada à avaliação ${index + 1}`}/><figcaption>Imagem anexada à avaliação</figcaption></figure></article>)}</div></div><div className="product-reviews__login"><Icon name="user" size={24}/><div><strong>Já comprou este produto?</strong><span>Entre na sua conta para compartilhar sua experiência.</span></div><Link className="button" to={`/conta?retorno=/produtos/${encodeURIComponent(managed?.slug || slug)}#avaliacoes`}>Entrar para avaliar</Link></div></div>}
      </div></div></section>
    <section className="related-products"><div className="container"><h2>Produtos relacionados</h2><div className="related-products__grid">{relatedProducts.map(product => <article key={product.name}><Link to={product.to}><img src={product.image} alt=""/><h3>{product.name}</h3><p>{product.detail}</p><span>Ver produto →</span></Link></article>)}</div></div></section>
  </>;
}

export function CartPage() {
  return <section className="cart-page"><div className="container"><div className="cart-page__heading"><span className="eyebrow">Sua seleção</span><h1>Carrinho</h1></div><div className="cart-empty"><span aria-hidden="true">00</span><div><h2>Seu carrinho está vazio.</h2><p>Os produtos padronizados aparecerão aqui quando preços, estoque e condições comerciais forem cadastrados. Para um painel sob medida, inicie uma cotação técnica.</p><div className="button-row"><ButtonLink to="/produtos">Explorar produtos</ButtonLink><ButtonLink to="/orcamento" variant="secondary">Solicitar cotação</ButtonLink></div></div></div></div></section>;
}

export function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [sent, setSent] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [developmentUrl, setDevelopmentUrl] = useState('');

  async function requestRecovery(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitting(true); setError(''); setDevelopmentUrl('');
    try {
      const response = await apiRequest<{ development_reset_url?: string; email_sent?: boolean } | null>('/auth/forgot-password', { method:'POST', body:JSON.stringify({ email }) });
      setDevelopmentUrl(response.data?.development_reset_url ?? '');
      setSent(true);
    } catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível solicitar a recuperação.'); }
    finally { setSubmitting(false); }
  }

  return <section className="password-recovery"><div className="container password-recovery__layout">
    <div className="password-recovery__intro"><span className="eyebrow">Recuperação de acesso</span><h1>Esqueceu sua senha?</h1><p>Informe o e-mail usado no cadastro. Você receberá um link seguro para criar uma nova senha.</p><div><Icon name="shield" size={22}/><span><strong>Proteção da sua conta</strong><small>O link expira em 1 hora e só pode ser utilizado uma vez.</small></span></div></div>
    <form className="password-recovery__card" onSubmit={requestRecovery}><span>Área do cliente</span><h2>Receber link por e-mail</h2><p>As instruções serão enviadas por <strong>sistema@paineldecomando.com.br</strong>.</p><label>E-mail de acesso<input type="email" value={email} onChange={event => setEmail(event.target.value)} required autoComplete="email" placeholder="seuemail@empresa.com.br" autoFocus/></label>{error && <div className="account-form__error" role="alert">{error}</div>}{sent && <div className="account-form__notice" role="status">Se este e-mail estiver cadastrado, as instruções de recuperação serão enviadas.</div>}{developmentUrl && <a className="password-recovery__dev-link" href={developmentUrl}>Abrir link de teste local</a>}<button className="button button--primary" type="submit" disabled={submitting}>{submitting ? 'Enviando…' : 'Enviar link de recuperação'}</button><Link to="/conta">Voltar para entrar</Link><footer><Icon name="lock" size={16}/><span>Nunca enviaremos sua senha. Você criará uma nova pelo link recebido.</span></footer></form>
  </div></section>;
}

export function ResetPasswordPage() {
  const [params] = useSearchParams();
  const token = params.get('token') ?? '';
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setSubmitting(true); setError('');
    const data = new FormData(event.currentTarget);
    try {
      await apiRequest<null>('/auth/reset-password', { method:'POST', body:JSON.stringify({ token, password:data.get('password'), password_confirmation:data.get('password_confirmation') }) });
      setSuccess(true);
    } catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível alterar sua senha.'); }
    finally { setSubmitting(false); }
  }

  return <section className="password-recovery"><div className="container password-recovery__layout"><div className="password-recovery__intro"><span className="eyebrow">Nova senha</span><h1>Crie uma senha segura.</h1><p>Use pelo menos 8 caracteres, combinando letras e números.</p></div><form className="password-recovery__card" onSubmit={submit}><span>Área do cliente</span><h2>Redefinir senha</h2>{success ? <><div className="account-form__notice" role="status">Senha alterada com sucesso.</div><Link className="button button--primary" to="/conta">Entrar na minha conta</Link></> : <><label>Nova senha<input name="password" type="password" required minLength={8} autoComplete="new-password"/></label><label>Confirmar nova senha<input name="password_confirmation" type="password" required minLength={8} autoComplete="new-password"/></label>{error && <div className="account-form__error" role="alert">{error}</div>}<button className="button button--primary" type="submit" disabled={submitting || token === ''}>{submitting ? 'Salvando…' : 'Criar nova senha'}</button>{token === '' && <div className="account-form__error" role="alert">Este link de recuperação está incompleto.</div>}</>}<footer><Icon name="lock" size={16}/><span>Depois da alteração, as sessões antigas serão encerradas.</span></footer></form></div></section>;
}

export function AccountPage() {
  const { user: accountUser, loading: checkingSession, setUser: setAccountUser } = useAuth();
  const [accountMode, setAccountMode] = useState<'login' | 'register'>('login');
  const [profileTab, setProfileTab] = useState<'personal' | 'address' | 'support'>(() => window.location.hash === '#endereco-principal' ? 'address' : window.location.hash === '#atendimento' ? 'support' : 'personal');
  const [submitting, setSubmitting] = useState(false);
  const [cepLoading, setCepLoading] = useState(false);
  const [formError, setFormError] = useState('');
  const [notice, setNotice] = useState('');
  const [profile, setProfile] = useState({ name: '', company: '', phone: '', postalCode: '', street: '', number: '', complement: '', district: '', city: '', state: '' });
  const [profileUserId, setProfileUserId] = useState<number | null>(null);
  const selectProfileTab = (tab: 'personal' | 'address' | 'support') => {
    setProfileTab(tab);
    const hash = tab === 'address' ? '#endereco-principal' : tab === 'support' ? '#atendimento' : '#dados-pessoais';
    window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}${hash}`);
  };
  if (accountUser && profileUserId !== accountUser.id) {
    setProfile({ name: accountUser.name, company: accountUser.company ?? '', phone: accountUser.phone, postalCode: accountUser.address?.postalCode ?? '', street: accountUser.address?.street ?? '', number: accountUser.address?.number ?? '', complement: accountUser.address?.complement ?? '', district: accountUser.address?.district ?? '', city: accountUser.address?.city ?? '', state: accountUser.address?.state ?? '' });
    setProfileUserId(accountUser.id);
  }
  const changeMode = (mode: 'login' | 'register') => { setAccountMode(mode); setNotice(''); setFormError(''); };
  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = event.currentTarget;
    setSubmitting(true);
    setNotice('');
    setFormError('');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries()) as Record<string, FormDataEntryValue | boolean>;
    if (accountMode === 'register') payload.consent = formData.get('consent') === 'on';
    try {
      const response = await apiRequest<Customer>(accountMode === 'login' ? '/auth/login' : '/auth/register', { method: 'POST', body: JSON.stringify(payload) });
      setAccountUser(response.data);
      setNotice(response.message ?? (accountMode === 'login' ? 'Acesso realizado com sucesso.' : 'Conta criada com sucesso.'));
      form.reset();
    } catch (error) {
      const fieldMessage = error instanceof ApiError && error.errors ? Object.values(error.errors).flat()[0] : undefined;
      setFormError(fieldMessage ?? (error instanceof ApiError ? error.message : 'Não foi possível concluir a solicitação.'));
    } finally { setSubmitting(false); }
  };
  const logout = async () => {
    setSubmitting(true);
    try { await apiRequest('/auth/logout', { method: 'POST' }); setAccountUser(null); setProfileUserId(null); setNotice('Sessão encerrada com sucesso.'); }
    catch (error) { setFormError(error instanceof ApiError ? error.message : 'Não foi possível sair da conta.'); }
    finally { setSubmitting(false); }
  };
  const updateProfileField = (field: keyof typeof profile, value: string) => setProfile(current => ({ ...current, [field]: value }));
  const lookupProfileCep = async () => {
    if (profile.postalCode.replace(/\D/g, '').length !== 8) { setFormError('Informe um CEP válido com 8 dígitos.'); return; }
    setCepLoading(true); setFormError('');
    try {
      const response = await apiRequest<{ cep: string; street: string; district: string; city: string; uf: string }>('/shipping/cep', { method: 'POST', body: JSON.stringify({ cep: profile.postalCode }) });
      setProfile(current => ({ ...current, postalCode: response.data.cep, street: response.data.street, district: response.data.district, city: response.data.city, state: response.data.uf }));
      setNotice('Endereço localizado. Confira o número e o complemento.');
    } catch (error) { setFormError(error instanceof ApiError ? error.message : 'Não foi possível consultar o CEP.'); }
    finally { setCepLoading(false); }
  };
  const saveProfile = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault(); setSubmitting(true); setNotice(''); setFormError('');
    try {
      const payload: Record<string, unknown> = { name: profile.name, company: profile.company, phone: profile.phone };
      if (profileTab === 'address') payload.address = { postalCode: profile.postalCode, street: profile.street, number: profile.number, complement: profile.complement, district: profile.district, city: profile.city, state: profile.state };
      const response = await apiRequest<Customer>('/auth/profile', { method: 'POST', body: JSON.stringify(payload) });
      setAccountUser(response.data); setNotice(response.message ?? 'Dados atualizados com sucesso.');
    } catch (error) {
      const fieldMessage = error instanceof ApiError && error.errors ? Object.values(error.errors).flat()[0] : undefined;
      setFormError(fieldMessage ?? (error instanceof ApiError ? error.message : 'Não foi possível atualizar seus dados.'));
    } finally { setSubmitting(false); }
  };
  const submitAccountForm = (event: FormEvent<HTMLFormElement>) => {
    if (profileTab !== 'support') { void saveProfile(event); return; }
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    const message = ['Olá! Gostaria de abrir um atendimento.', `Cliente: ${accountUser?.name}`, `Empresa: ${accountUser?.company || 'Não informada'}`, `E-mail: ${accountUser?.email}`, `WhatsApp: ${accountUser?.phone}`, `Assunto: ${data.get('subject')}`, `Painel: ${data.get('product')}`, `Potência: ${data.get('power') || 'Não informada'}`, `Tensão: ${data.get('voltage') || 'Não informada'}`, `Aplicação: ${data.get('application') || 'Não informada'}`, `Mensagem: ${data.get('message')}`].join('\n');
    window.open(`https://wa.me/${companyConfig.whatsapp}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer');
  };

  if (checkingSession) return <section className="account-page account-page--session-loading"><div className="account-page-loading" role="status"><Icon name="verified" size={24}/><div><strong>Carregando sua conta</strong><span>Estamos verificando sua sessão com segurança.</span></div></div></section>;

  return <section className={accountUser ? 'account-page account-page--dashboard' : 'account-page'}><div className="container account-shell">
    {accountUser ? <aside className="account-customer-nav">
      <div className="account-customer-nav__title"><span>Painel do cliente</span><strong>Minha conta</strong></div>
      <div className="account-customer-nav__identity"><span aria-hidden="true">{accountUser.name.charAt(0).toUpperCase()}</span><div><strong>{accountUser.name}</strong><small>{accountUser.email}</small></div></div>
      <nav aria-label="Navegação da conta"><button type="button" className={profileTab === 'personal' ? 'active' : ''} onClick={() => selectProfileTab('personal')}><Icon name="idCard" size={20}/><span><strong>Dados pessoais</strong><small>Nome, empresa e contato</small></span></button><button type="button" className={profileTab === 'address' ? 'active' : ''} onClick={() => selectProfileTab('address')}><Icon name="map" size={20}/><span><strong>Endereço principal</strong><small>Entrega e cálculo de frete</small></span></button><button type="button" className={profileTab === 'support' ? 'active' : ''} onClick={() => selectProfileTab('support')}><Icon name="support" size={20}/><span><strong>Atendimento</strong><small>Suporte, garantia e orçamento</small></span></button></nav>
      <div className="account-customer-nav__security"><Icon name="verified" size={22}/><div><strong>Ambiente protegido</strong><small>Sessão segura e dados tratados conforme a LGPD.</small></div></div>
    </aside> : <aside className="account-intro">
      <span className="eyebrow">Sua área de compras</span>
      <h1>Mais controle em cada etapa do seu pedido.</h1>
      <p>Centralize compras, avaliações e solicitações técnicas em um ambiente organizado para sua empresa.</p>
      <div className="account-benefits">
        <article><Icon name="clipboard" size={24}/><div><strong>Acompanhe seus pedidos</strong><span>Consulte o andamento das suas compras e cotações.</span></div></article>
        <article><Icon name="check" size={24}/><div><strong>Avalie produtos comprados</strong><span>Compartilhe sua experiência com compras verificadas.</span></div></article>
        <article><Icon name="headset" size={24}/><div><strong>Atendimento mais ágil</strong><span>Mantenha suas solicitações reunidas em um só lugar.</span></div></article>
      </div>
      <div className="account-trust"><Icon name="shield" size={22}/><span><strong>Seus dados protegidos</strong>Privacidade e segurança em todas as etapas.</span></div>
    </aside>}

    <div className="account-card">
      {accountUser ? <div className="account-dashboard">
        <header className="account-dashboard__header"><div><span className="eyebrow">Área do cliente</span><h2>Olá, {accountUser.name.split(' ')[0]}.</h2><p>Gerencie seus dados pessoais e o endereço usado nas compras.</p></div><span className="account-dashboard__status"><Icon name="verified" size={17}/> Conta ativa</span></header>
        {notice && <div className="account-form__notice" role="status">{notice}</div>}
        {formError && <div className="account-form__error" role="alert">{formError}</div>}
        <div className="account-profile-tabs" role="tablist" aria-label="Dados da conta"><button type="button" role="tab" aria-selected={profileTab === 'personal'} className={profileTab === 'personal' ? 'active' : ''} onClick={() => selectProfileTab('personal')}>Dados pessoais</button><button type="button" role="tab" aria-selected={profileTab === 'address'} className={profileTab === 'address' ? 'active' : ''} onClick={() => selectProfileTab('address')}>Endereço principal</button><button type="button" role="tab" aria-selected={profileTab === 'support'} className={profileTab === 'support' ? 'active' : ''} onClick={() => selectProfileTab('support')}>Atendimento</button></div>
        <form className="account-profile-form" onSubmit={submitAccountForm}>
          {profileTab === 'personal' && <section id="dados-pessoais"><div className="account-profile-form__heading"><span>01</span><div><h3>Dados pessoais</h3><p>Informações de contato e identificação da sua conta.</p></div></div><div className="account-profile-form__grid"><label><span className="account-field-label">Nome completo</span><input value={profile.name} onChange={event => updateProfileField('name', event.target.value)} required maxLength={150} autoComplete="name"/></label><label><span className="account-field-label">Empresa <small>(opcional)</small></span><input value={profile.company} onChange={event => updateProfileField('company', event.target.value)} maxLength={190} autoComplete="organization"/></label><label><span className="account-field-label">Telefone</span><input value={profile.phone} onChange={event => updateProfileField('phone', event.target.value)} required maxLength={20} autoComplete="tel" inputMode="tel"/></label><label><span className="account-field-label">E-mail <small>(acesso da conta)</small></span><input value={accountUser.email} disabled aria-describedby="account-email-help"/><small className="account-field-help" id="account-email-help">Para alterar o e-mail, fale com o atendimento.</small></label></div></section>}
          {profileTab === 'address' && <section id="endereco-principal"><div className="account-profile-form__heading"><span>02</span><div><h3>Endereço principal</h3><p>Usado para entrega e cálculo de frete.</p></div></div><div className="account-profile-form__grid account-address-grid"><label><span className="account-field-label">CEP</span><div className="account-cep-field"><input value={profile.postalCode} onChange={event => updateProfileField('postalCode', event.target.value)} required inputMode="numeric" autoComplete="postal-code" placeholder="00000-000"/><button type="button" onClick={lookupProfileCep} disabled={cepLoading}>{cepLoading ? 'Buscando…' : 'Buscar CEP'}</button></div></label><label><span className="account-field-label">Rua</span><input value={profile.street} onChange={event => updateProfileField('street', event.target.value)} required maxLength={190} autoComplete="address-line1"/></label><label className="account-address-number"><span className="account-field-label">Número</span><input value={profile.number} onChange={event => updateProfileField('number', event.target.value)} required maxLength={30}/></label><label><span className="account-field-label">Complemento <small>(opcional)</small></span><input value={profile.complement} onChange={event => updateProfileField('complement', event.target.value)} maxLength={120} autoComplete="address-line2"/></label><label><span className="account-field-label">Bairro</span><input value={profile.district} onChange={event => updateProfileField('district', event.target.value)} required maxLength={120}/></label><label><span className="account-field-label">Cidade</span><input value={profile.city} onChange={event => updateProfileField('city', event.target.value)} required maxLength={120} autoComplete="address-level2"/></label><label className="account-address-state"><span className="account-field-label">Estado</span><input value={profile.state} onChange={event => updateProfileField('state', event.target.value.toUpperCase().slice(0, 2))} required maxLength={2} autoComplete="address-level1" placeholder="SP"/></label></div></section>}
          {profileTab === 'support' && <section id="atendimento"><div className="account-profile-form__heading"><span>03</span><div><h3>Atendimento</h3><p>Envie sua solicitação já identificada para nossa equipe.</p></div></div><div className="account-support-customer"><Icon name="verified" size={20}/><div><strong>{accountUser.name}</strong><span>{accountUser.email} · {accountUser.phone}</span></div></div><div className="account-profile-form__grid account-support-grid"><label><span className="account-field-label">Assunto</span><select name="subject" defaultValue="Suporte técnico"><option>Suporte técnico</option><option>Orçamento</option><option>Garantia</option><option>Troca ou devolução</option></select></label><label><span className="account-field-label">Painel</span><select name="product" defaultValue="Painel Estrela Triângulo 15CV"><option>Painel Estrela Triângulo 15CV</option><option>Painel com Soft Starter</option><option>Painel com Inversor de Frequência</option><option>Painel para Bombas</option><option>Projeto personalizado</option></select></label><label><span className="account-field-label">Potência <small>(opcional)</small></span><input name="power" placeholder="Ex.: 15 CV / 11 kW"/></label><label><span className="account-field-label">Tensão <small>(opcional)</small></span><input name="voltage" placeholder="Ex.: 220 V / 380 V"/></label><label className="account-support-wide"><span className="account-field-label">Aplicação <small>(opcional)</small></span><textarea name="application" placeholder="Ex.: bomba, esteira ou máquina industrial"/></label><label className="account-support-wide"><span className="account-field-label">Como podemos ajudar?</span><textarea name="message" required placeholder="Descreva sua solicitação com os detalhes importantes"/></label></div></section>}
          <div className="account-profile-form__actions">{profileTab === 'support' ? <button className="account-action account-action--whatsapp" type="submit"><span><Icon name="whatsapp" size={19}/></span><strong>Abrir no WhatsApp</strong></button> : <button className="account-action account-action--save" type="submit" disabled={submitting}><span><Icon name="save" size={18}/></span><strong>{submitting ? 'Salvando…' : profileTab === 'address' ? 'Salvar endereço' : 'Salvar dados pessoais'}</strong></button>}<button className="account-logout" type="button" onClick={logout} disabled={submitting}>Sair da conta</button></div>
        </form>
      </div> : <>
      <header><span>{accountMode === 'login' ? 'Bem-vindo de volta' : 'Comece seu cadastro'}</span><h2>{accountMode === 'login' ? 'Entre na sua conta' : 'Crie sua conta'}</h2><p>{accountMode === 'login' ? 'Use seus dados de acesso para continuar.' : 'Preencha seus dados para criar sua área de cliente.'}</p></header>
      <div className="account-access__switch" role="tablist" aria-label="Acesso à conta"><button type="button" role="tab" aria-selected={accountMode === 'login'} className={accountMode === 'login' ? 'active' : ''} onClick={() => changeMode('login')}>Entrar</button><button type="button" role="tab" aria-selected={accountMode === 'register'} className={accountMode === 'register' ? 'active' : ''} onClick={() => changeMode('register')}>Criar conta</button></div>
      <form className="form account-form" onSubmit={submit}>
        {accountMode === 'register' && <div className="account-form__grid"><label>Nome completo<input name="name" required maxLength={150} autoComplete="name" placeholder="Digite seu nome"/></label><label>Empresa <small>(opcional)</small><input name="company" maxLength={190} autoComplete="organization" placeholder="Nome da empresa"/></label><label className="account-form__wide">Telefone<input name="phone" required maxLength={20} autoComplete="tel" inputMode="tel" placeholder="(00) 00000-0000"/></label></div>}
        <label>E-mail<input name="email" type="email" required maxLength={190} autoComplete="username" placeholder="seuemail@empresa.com.br"/></label>
        <label>Senha{accountMode === 'login' && <Link to="/conta/recuperar-senha">Esqueci minha senha</Link>}<input name="password" type="password" required minLength={8} autoComplete={accountMode === 'login' ? 'current-password' : 'new-password'} placeholder="Mínimo de 8 caracteres"/></label>
        {accountMode === 'register' && <label className="account-form__consent"><input name="consent" type="checkbox" required/><span>Li e concordo com a <Link to="/politica-de-privacidade">Política de Privacidade</Link>.</span></label>}
        <button className="button button--primary account-form__submit" type="submit" disabled={submitting}>{submitting ? 'Aguarde…' : accountMode === 'login' ? 'Entrar na minha conta' : 'Criar minha conta'}{!submitting && <Icon name="arrow" size={18}/>}</button>
        {formError && <div className="account-form__error" role="alert">{formError}</div>}
        {notice && <div className="account-form__notice" role="status">{notice}</div>}
      </form>
      <footer><Icon name="lock" size={17}/><span>Ambiente seguro. Seus dados são tratados conforme nossa <Link to="/politica-de-privacidade">Política de Privacidade</Link>.</span></footer>
      </>}
    </div>
  </div></section>;
}

export function CompanyPage() { return <><PageHero {...labels.empresa}/><section className="section"><div className="container cards"><article className="card"><h2>Missão</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article><article className="card"><h2>Visão</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article><article className="card"><h2>Valores</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article></div></section></>; }

export function ContactPage() { return <TechnicalTicketPage/>; }

export function QuotePage() { return <><PageHero eyebrow="Orçamento técnico" title="Conte sua necessidade" body="Quanto mais contexto você fornecer, mais objetiva poderá ser a análise inicial."/><section className="section"><div className="container form-wrap"><SimpleForm type="quote"/></div></section></>; }

function SimpleForm({ type }: { type: 'contact' | 'quote' }) {
  const [sent, setSent] = useState(false);
  const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); setSent(true); };
  if (sent) return <div className="notice" role="status"><strong>Dados validados no front-end.</strong><p>O envio definitivo será conectado ao endpoint da API na etapa pública. Nenhum dado foi armazenado nesta demonstração.</p></div>;
  return <form className="form" onSubmit={submit}><label>Nome<input name="name" required autoComplete="name"/></label><label>Empresa<input name="company" autoComplete="organization"/></label><label>E-mail<input name="email" type="email" required autoComplete="email"/></label><label>Telefone<input name="phone" required autoComplete="tel"/></label>{type === 'quote' && <><label>Produto ou solução<input name="interest"/></label><label>Descrição da necessidade<textarea name="message" required rows={6}/></label><label className="checkbox"><input name="consent" type="checkbox" required/> Li e concordo com a Política de Privacidade.</label></>}{type === 'contact' && <label>Mensagem<textarea name="message" required rows={5}/></label>}<button className="button button--primary" type="submit">Enviar solicitação</button></form>;
}

export function LegalPage({ terms = false }: { terms?: boolean }) {
  if (!terms) return <PolicyDocument kind="privacy"/>;
  return <><PageHero eyebrow="Informações legais" title="Termos de uso" body="Condições para utilização do site Painel de Comando."/><section className="section"><article className="container legal"><h2>Uso deste site</h2><p>Este documento é um modelo inicial e deverá ser adaptado à operação real e passar por revisão jurídica antes da publicação.</p><h2>Contato</h2><p>Para solicitações, utilize o canal {companyConfig.email}.</p></article></section></>;
}

export function BuyerPolicyPage({ kind }: { kind: 'returns' | 'warranty' }) {
  return <PolicyDocument kind={kind}/>;
}

export function NotFoundPage() { return <section className="section not-found"><div className="container"><span className="eyebrow">Erro 404</span><h1>Esta página saiu do circuito.</h1><p>O endereço pode ter mudado. Volte ao início ou consulte nossas soluções.</p><div className="button-row"><ButtonLink to="/">Voltar ao início</ButtonLink><ButtonLink to="/produtos" variant="secondary">Ver soluções</ButtonLink></div></div></section>; }

export function LoginPage() { return <section className="login"><form className="form login__card" onSubmit={(e) => e.preventDefault()}><Link to="/" className="brand"><span>{companyConfig.shortName}</span>{companyConfig.name}</Link><h1>Acesso administrativo</h1><p>Autenticação será conectada à API protegida.</p><label>E-mail<input type="email" required autoComplete="username"/></label><label>Senha<input type="password" required autoComplete="current-password"/></label><button className="button button--primary" type="submit">Entrar</button><Link to="/">Voltar ao site</Link></form></section>; }

export function AdminPage({ section = 'Dashboard' }: { section?: string }) { return <><div className="admin-heading"><div><span className="eyebrow">Administração</span><h1>{section}</h1></div>{section !== 'Dashboard' && <button className="button button--primary" type="button">Adicionar registro</button>}</div><div className="admin-cards"><article><span>Conteúdo</span><strong>—</strong><small>Aguardando conexão com API</small></article><article><span>Status</span><strong>Base pronta</strong><small>Dados demonstrativos</small></article></div><section className="admin-panel"><h2>{section === 'Dashboard' ? 'Atividades recentes' : `Gerenciamento de ${section.toLowerCase()}`}</h2><p>O layout administrativo, navegação e rota estão funcionais. Autenticação, tabelas e CRUDs pertencem à Etapa 4.</p></section></>; }

function PageHero({ eyebrow, title, body }: { eyebrow: string; title: string; body: string }) { return <section className="page-hero"><div className="container"><span className="eyebrow eyebrow--light">{eyebrow}</span><h1>{title}</h1><p>{body}</p></div></section>; }
