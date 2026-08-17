import { FormEvent, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { companyConfig } from '../constants/company';
import { ApiError, apiRequest } from '../services/api';

export type AdminUser = { id: number; name: string; email: string; role: string };
export type AdminProduct = {
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
  seo_title: string | null;
  seo_description: string | null;
  updated_at: string;
};

const emptyProduct: Omit<AdminProduct, 'id' | 'updated_at'> = {
  name: '', slug: '', summary: '', description: '', features: [], benefits: [], components: [], voltages: '', power_range: '',
  protection_rating: '', image_url: '', gallery_images: [], video_url: '', video_urls: [], category_name: '', reference_code: '', brand: 'Painel de Comando', model: '',
  price_cents: null, installments: 1, stock_status: 'on_demand', stock_quantity: 0, lead_time: '', sales_channel: 'both', warranty_days: 365,
  sort_order: 0, featured: false, status: 'draft', seo_title: '', seo_description: '',
};

export function AdminLoginPage() {
  const navigate = useNavigate();
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setLoading(true); setError('');
    const data = new FormData(event.currentTarget);
    try {
      await apiRequest<AdminUser>('/admin/auth/login', { method: 'POST', body: JSON.stringify({ email: data.get('email'), password: data.get('password') }) });
      navigate('/admin/produtos', { replace: true });
    } catch (requestError) {
      setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível entrar.');
    } finally { setLoading(false); }
  }

  return <section className="login admin-login">
    <form className="form login__card" onSubmit={submit}>
      <Link to="/" className="brand"><span>{companyConfig.shortName}</span>{companyConfig.name}</Link>
      <div><span className="eyebrow">Área restrita</span><h1>Painel administrativo</h1><p>Entre para cadastrar, publicar e atualizar os produtos do catálogo.</p></div>
      <label>E-mail<input name="email" type="email" required autoComplete="username" autoFocus placeholder="admin@empresa.com.br"/></label>
      <label>Senha<input name="password" type="password" required autoComplete="current-password" placeholder="Sua senha"/></label>
      {error && <div className="admin-alert admin-alert--error" role="alert">{error}</div>}
      <button className="button button--primary" type="submit" disabled={loading}>{loading ? 'Entrando…' : 'Entrar no painel'}</button>
      <Link to="/">Voltar ao site</Link>
    </form>
  </section>;
}

export function AdminDashboardPage() {
  const [products, setProducts] = useState<AdminProduct[]>([]);
  useEffect(() => { apiRequest<AdminProduct[]>('/admin/products').then(response => setProducts(response.data)).catch(() => undefined); }, []);
  const published = products.filter(product => product.status === 'published').length;
  return <>
    <div className="admin-heading"><div><span className="eyebrow">Visão geral</span><h1>Dashboard</h1><p>Acompanhe o catálogo e acesse rapidamente o que precisa de atenção.</p></div><Link className="button button--primary" to="/admin/produtos">Gerenciar produtos</Link></div>
    <div className="admin-cards admin-cards--stats"><article><span>Total de produtos</span><strong>{products.length}</strong><small>Registros ativos no catálogo</small></article><article><span>Publicados</span><strong>{published}</strong><small>Visíveis para os clientes</small></article><article><span>Rascunhos</span><strong>{products.filter(product => product.status === 'draft').length}</strong><small>Aguardando publicação</small></article></div>
    <section className="admin-panel"><div className="admin-panel__heading"><div><span>Atalho</span><h2>Catálogo de produtos</h2></div></div><p>Edite nomes, descrições, imagens, características técnicas, visibilidade e ordem de exibição em um único lugar.</p></section>
  </>;
}

function slugify(value: string) {
  return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

type UploadedMedia = { url: string; kind: 'image' | 'video'; original_name: string; size_bytes: number };

async function uploadMedia(file: File) {
  const form = new FormData();
  form.append('file', file);
  return (await apiRequest<UploadedMedia>('/admin/uploads', { method:'POST', body:form, timeoutMs:120_000 })).data;
}

function ProductEditor({ product, onSaved, onCancel }: { product: AdminProduct | null; onSaved: (product: AdminProduct) => void; onCancel: () => void }) {
  const [draft, setDraft] = useState(() => product ? { ...product } : { ...emptyProduct });
  const [featuresText, setFeaturesText] = useState((product?.features ?? []).join('\n'));
  const [benefitsText, setBenefitsText] = useState((product?.benefits ?? []).join('\n'));
  const [componentsText, setComponentsText] = useState((product?.components ?? []).join('\n'));
  const [galleryText, setGalleryText] = useState((product?.gallery_images ?? []).join('\n'));
  const [videosText, setVideosText] = useState((product?.video_urls?.length ? product.video_urls : product?.video_url ? [product.video_url] : []).join('\n'));
  const [priceText, setPriceText] = useState(product?.price_cents == null ? '' : (product.price_cents / 100).toFixed(2).replace('.', ','));
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState<string | null>(null);
  const [error, setError] = useState('');
  const update = (field: string, value: string | number | boolean) => setDraft(current => ({ ...current, [field]: value }));

  async function uploadPrincipal(file?: File) {
    if (!file) return;
    setUploading('principal'); setError('');
    try { update('image_url', (await uploadMedia(file)).url); }
    catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível enviar a imagem.'); }
    finally { setUploading(null); }
  }

  async function uploadGallery(files: FileList | null) {
    if (!files?.length) return;
    const current = galleryText.split('\n').map(item => item.trim()).filter(Boolean);
    if (current.length + files.length > 4) { setError('O limite é de 5 fotos no total: 1 principal e até 4 fotos adicionais.'); return; }
    setUploading('galeria'); setError('');
    try {
      for (const file of Array.from(files)) current.push((await uploadMedia(file)).url);
      setGalleryText(current.join('\n'));
    } catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível enviar uma das imagens.'); }
    finally { setUploading(null); }
  }

  async function uploadVideo(files: FileList | null) {
    if (!files?.length) return;
    const current = videosText.split('\n').map(item => item.trim()).filter(Boolean);
    if (current.length + files.length > 2) { setError('Você pode adicionar no máximo 2 vídeos por produto.'); return; }
    setUploading('video'); setError('');
    try { for (const file of Array.from(files)) current.push((await uploadMedia(file)).url); setVideosText(current.join('\n')); }
    catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível enviar o vídeo.'); }
    finally { setUploading(null); }
  }

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setSaving(true); setError('');
    const parsedPrice = Number(priceText.replace(/\./g, '').replace(',', '.'));
    const videoUrls = videosText.split('\n').map(item => item.trim()).filter(Boolean);
    const payload = { ...draft, video_url: videoUrls[0] ?? '', video_urls: videoUrls, price_cents: priceText.trim() === '' ? null : Math.round(parsedPrice * 100), features: featuresText.split('\n').map(item => item.trim()).filter(Boolean), benefits: benefitsText.split('\n').map(item => item.trim()).filter(Boolean), components: componentsText.split('\n').map(item => item.trim()).filter(Boolean), gallery_images: galleryText.split('\n').map(item => item.trim()).filter(Boolean) };
    try {
      const response = await apiRequest<AdminProduct>(product ? `/admin/products/${product.id}` : '/admin/products', { method: product ? 'PUT' : 'POST', body: JSON.stringify(payload) });
      onSaved(response.data);
    } catch (requestError) {
      setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível salvar o produto.');
    } finally { setSaving(false); }
  }

  return <div className="admin-editor-backdrop" role="presentation"><section className="admin-editor" role="dialog" aria-modal="true" aria-labelledby="product-editor-title">
    <header><div><span>{product ? 'Editar produto' : 'Novo produto'}</span><h2 id="product-editor-title">{product?.name || 'Adicionar ao catálogo'}</h2></div><button type="button" onClick={onCancel} aria-label="Fechar editor">×</button></header>
    <form onSubmit={submit} className="admin-product-form">
      <div className="admin-form-section"><h3>Informações principais</h3><div className="admin-form-grid">
        <label className="admin-form-wide">Nome do produto<input value={draft.name} onChange={event => { update('name', event.target.value); if (!product) update('slug', slugify(event.target.value)); }} required maxLength={190}/></label>
        <label>Endereço (slug)<input value={draft.slug} onChange={event => update('slug', slugify(event.target.value))} required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"/></label>
        <label>Status<select value={draft.status} onChange={event => update('status', event.target.value)}><option value="draft">Rascunho</option><option value="published">Publicado</option><option value="archived">Arquivado</option></select></label>
        <label className="admin-form-wide">Resumo<textarea value={draft.summary ?? ''} onChange={event => update('summary', event.target.value)} rows={3} maxLength={600}/></label>
        <label className="admin-form-wide">Descrição completa<textarea value={draft.description ?? ''} onChange={event => update('description', event.target.value)} rows={6}/></label>
        <label>Categoria<input value={draft.category_name ?? ''} onChange={event => update('category_name', event.target.value)} placeholder="Painéis de partida"/></label>
        <label>Código de referência<input value={draft.reference_code ?? ''} onChange={event => update('reference_code', event.target.value)} placeholder="PAINEL-E.T-15CV"/></label>
        <label>Marca<input value={draft.brand ?? ''} onChange={event => update('brand', event.target.value)}/></label>
        <label>Modelo<input value={draft.model ?? ''} onChange={event => update('model', event.target.value)}/></label>
      </div></div>
      <div className="admin-form-section"><h3>Fotos e vídeo</h3><div className="admin-form-grid">
        <div className="admin-form-wide admin-upload-field"><span>Foto principal — 1 de 5</span><label className="admin-upload-drop"><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" onChange={event => void uploadPrincipal(event.target.files?.[0])} disabled={uploading !== null}/><strong>{uploading === 'principal' ? 'Enviando imagem…' : draft.image_url ? 'Trocar foto principal' : 'Escolher foto principal'}</strong><small>JPG, PNG, WEBP ou GIF — até 10 MB</small></label>{draft.image_url && <div className="admin-upload-preview"><img src={draft.image_url} alt="Prévia da foto principal"/><div><strong>Foto principal inserida</strong><small>{draft.image_url}</small></div><button type="button" onClick={() => update('image_url', '')}>Remover</button></div>}</div>
        <div className="admin-form-wide admin-upload-field"><span>Outras fotos — {galleryText.split('\n').map(item => item.trim()).filter(Boolean).length} de 4</span><label className="admin-upload-drop"><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple onChange={event => void uploadGallery(event.target.files)} disabled={uploading !== null || galleryText.split('\n').map(item => item.trim()).filter(Boolean).length >= 4}/><strong>{uploading === 'galeria' ? 'Enviando fotos…' : 'Adicionar fotos à galeria'}</strong><small>Limite total: 1 foto principal e até 4 fotos adicionais</small></label><div className="admin-upload-gallery">{galleryText.split('\n').map(item => item.trim()).filter(Boolean).map((url, index) => <figure key={`${url}-${index}`}><img src={url} alt={`Prévia da foto ${index + 2}`}/><button type="button" aria-label={`Remover foto ${index + 2}`} onClick={() => setGalleryText(galleryText.split('\n').map(item => item.trim()).filter(Boolean).filter((_, itemIndex) => itemIndex !== index).join('\n'))}>×</button><figcaption>Foto {index + 2}</figcaption></figure>)}</div></div>
        <div className="admin-form-wide admin-upload-field"><span>Vídeos — {videosText.split('\n').map(item => item.trim()).filter(Boolean).length} de 2</span><label className="admin-upload-drop"><input type="file" accept="video/mp4,video/webm,video/quicktime" multiple onChange={event => void uploadVideo(event.target.files)} disabled={uploading !== null || videosText.split('\n').map(item => item.trim()).filter(Boolean).length >= 2}/><strong>{uploading === 'video' ? 'Enviando vídeo…' : 'Adicionar vídeos'}</strong><small>Até 2 arquivos MP4, WEBM ou MOV — 20 MB cada</small></label><div className="admin-upload-video-list">{videosText.split('\n').map(item => item.trim()).filter(Boolean).map((url, index) => <div className="admin-upload-preview admin-upload-preview--video" key={`${url}-${index}`}><video src={url} controls preload="metadata"/><div><strong>Vídeo {index + 1}</strong><small>{url}</small></div><button type="button" onClick={() => setVideosText(videosText.split('\n').map(item => item.trim()).filter(Boolean).filter((_, itemIndex) => itemIndex !== index).join('\n'))}>Remover</button></div>)}</div></div>
      </div></div>
      <div className="admin-form-section"><h3>Preço, compra e estoque</h3><div className="admin-form-grid admin-form-grid--three">
        <label>Preço à vista (R$)<input inputMode="decimal" value={priceText} onChange={event => setPriceText(event.target.value)} placeholder="1.247,00"/></label>
        <label>Parcelas sem juros<input type="number" min="1" max="24" value={draft.installments} onChange={event => update('installments', Number(event.target.value))}/></label>
        <label>Como vender<select value={draft.sales_channel} onChange={event => update('sales_channel', event.target.value)}><option value="both">Site e WhatsApp</option><option value="site">Somente pelo site</option><option value="whatsapp">Somente pelo WhatsApp</option></select></label>
        <label>Situação do estoque<select value={draft.stock_status} onChange={event => update('stock_status', event.target.value)}><option value="in_stock">Em estoque</option><option value="out_of_stock">Sem estoque</option><option value="on_demand">Produção sob encomenda</option></select></label>
        <label>Quantidade em estoque<input type="number" min="0" value={draft.stock_quantity} onChange={event => update('stock_quantity', Number(event.target.value))}/></label>
        <label>Prazo / disponibilidade<input value={draft.lead_time ?? ''} onChange={event => update('lead_time', event.target.value)} placeholder="Disponível em 3 dias úteis"/></label>
        <label>Garantia em dias<input type="number" min="0" value={draft.warranty_days} onChange={event => update('warranty_days', Number(event.target.value))}/></label>
      </div></div>
      <div className="admin-form-section"><h3>Dados técnicos</h3><div className="admin-form-grid admin-form-grid--three">
        <label>Tensões<input value={draft.voltages ?? ''} onChange={event => update('voltages', event.target.value)} placeholder="220 V / 380 V"/></label>
        <label>Faixa de potência<input value={draft.power_range ?? ''} onChange={event => update('power_range', event.target.value)} placeholder="Até 15 CV"/></label>
        <label>Grau de proteção<input value={draft.protection_rating ?? ''} onChange={event => update('protection_rating', event.target.value)} placeholder="IP54"/></label>
        <label className="admin-form-wide">Características <small>uma por linha</small><textarea value={featuresText} onChange={event => setFeaturesText(event.target.value)} rows={5}/></label>
        <label className="admin-form-wide">Principais componentes <small>um por linha</small><textarea value={componentsText} onChange={event => setComponentsText(event.target.value)} rows={5}/></label>
        <label className="admin-form-wide">Benefícios <small>um por linha</small><textarea value={benefitsText} onChange={event => setBenefitsText(event.target.value)} rows={5}/></label>
      </div></div>
      <div className="admin-form-section"><h3>Exibição e busca</h3><div className="admin-form-grid">
        <label>Ordem de exibição<input type="number" min="0" value={draft.sort_order} onChange={event => update('sort_order', Number(event.target.value))}/></label>
        <label className="admin-check"><input type="checkbox" checked={draft.featured} onChange={event => update('featured', event.target.checked)}/><span>Destacar este produto</span></label>
        <label className="admin-form-wide">Título para buscadores<input value={draft.seo_title ?? ''} onChange={event => update('seo_title', event.target.value)} maxLength={190}/></label>
        <label className="admin-form-wide">Descrição para buscadores<textarea value={draft.seo_description ?? ''} onChange={event => update('seo_description', event.target.value)} rows={2} maxLength={320}/></label>
      </div></div>
      {error && <div className="admin-alert admin-alert--error" role="alert">{error}</div>}
      <footer><button className="button button--secondary" type="button" onClick={onCancel}>Cancelar</button><button className="button button--primary" type="submit" disabled={saving || uploading !== null}>{saving ? 'Salvando…' : uploading ? 'Aguarde o envio…' : 'Salvar produto'}</button></footer>
    </form>
  </section></div>;
}

export function AdminProductsPage() {
  const [products, setProducts] = useState<AdminProduct[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [editing, setEditing] = useState<AdminProduct | null | undefined>(undefined);

  async function load() {
    setLoading(true); setError('');
    try { setProducts((await apiRequest<AdminProduct[]>('/admin/products')).data); }
    catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível carregar os produtos.'); }
    finally { setLoading(false); }
  }
  useEffect(() => {
    apiRequest<AdminProduct[]>('/admin/products')
      .then(response => setProducts(response.data))
      .catch(requestError => setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível carregar os produtos.'))
      .finally(() => setLoading(false));
  }, []);
  const visible = useMemo(() => { const term = search.trim().toLocaleLowerCase('pt-BR'); return term ? products.filter(product => `${product.name} ${product.slug}`.toLocaleLowerCase('pt-BR').includes(term)) : products; }, [products, search]);

  function saved(product: AdminProduct) {
    setProducts(current => current.some(item => item.id === product.id) ? current.map(item => item.id === product.id ? product : item) : [...current, product]);
    setEditing(undefined); setNotice('Produto salvo com sucesso.'); window.setTimeout(() => setNotice(''), 3500);
  }
  async function remove(product: AdminProduct) {
    if (!window.confirm(`Remover “${product.name}” do catálogo?`)) return;
    try { await apiRequest<null>(`/admin/products/${product.id}`, { method: 'DELETE' }); setProducts(current => current.filter(item => item.id !== product.id)); setNotice('Produto removido.'); }
    catch (requestError) { setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível remover o produto.'); }
  }

  return <>
    <div className="admin-heading"><div><span className="eyebrow">Catálogo</span><h1>Produtos</h1><p>Cadastre, edite e controle o que aparece no site.</p></div><button className="button button--primary" type="button" onClick={() => setEditing(null)}>+ Novo produto</button></div>
    {notice && <div className="admin-alert admin-alert--success" role="status">{notice}</div>}
    {error && <div className="admin-alert admin-alert--error" role="alert">{error}<button type="button" onClick={load}>Tentar novamente</button></div>}
    <section className="admin-panel admin-products-panel">
      <div className="admin-toolbar"><label><span>Buscar produto</span><input type="search" value={search} onChange={event => setSearch(event.target.value)} placeholder="Nome ou endereço…"/></label><span>{visible.length} {visible.length === 1 ? 'produto' : 'produtos'}</span></div>
      {loading ? <div className="admin-empty" role="status">Carregando produtos…</div> : visible.length === 0 ? <div className="admin-empty"><strong>Nenhum produto encontrado</strong><p>Altere a busca ou adicione o primeiro produto.</p></div> : <div className="admin-product-table-wrap"><table className="admin-product-table"><thead><tr><th>Produto</th><th>Preço</th><th>Estoque</th><th>Status</th><th>Ordem</th><th>Atualização</th><th><span className="sr-only">Ações</span></th></tr></thead><tbody>{visible.map(product => <tr key={product.id}><td><div className="admin-product-cell">{product.image_url ? <img src={product.image_url} alt=""/> : <span aria-hidden="true">PC</span>}<div><strong>{product.name}</strong><small>/{product.slug}</small></div></div></td><td>{product.price_cents == null ? 'Sob consulta' : (product.price_cents / 100).toLocaleString('pt-BR', { style:'currency', currency:'BRL' })}</td><td>{product.stock_status === 'in_stock' ? `${product.stock_quantity} un.` : product.stock_status === 'out_of_stock' ? 'Sem estoque' : 'Sob encomenda'}</td><td><span className={`admin-status admin-status--${product.status}`}>{product.status === 'published' ? 'Publicado' : product.status === 'draft' ? 'Rascunho' : 'Arquivado'}</span></td><td>{product.sort_order}</td><td>{new Date(product.updated_at).toLocaleDateString('pt-BR')}</td><td><div className="admin-row-actions"><button type="button" onClick={() => setEditing(product)}>Editar</button><Link to={`/produtos/${product.slug}`} target="_blank">Ver</Link><button className="danger" type="button" onClick={() => remove(product)}>Remover</button></div></td></tr>)}</tbody></table></div>}
    </section>
    {editing !== undefined && <ProductEditor key={editing?.id ?? 'new'} product={editing} onSaved={saved} onCancel={() => setEditing(undefined)}/>} 
  </>;
}

export function AdminPlaceholderPage({ section }: { section: string }) {
  return <><div className="admin-heading"><div><span className="eyebrow">Administração</span><h1>{section}</h1></div></div><section className="admin-panel"><h2>Módulo em preparação</h2><p>O gerenciamento de produtos já está ativo. Esta área poderá ser conectada na próxima etapa.</p></section></>;
}
