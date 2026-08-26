import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { apiRequest } from '../../services/api';

const SITE_URL = (import.meta.env.VITE_SITE_URL ?? 'https://paineldecomando.com.br').replace(/\/$/, '');
const DEFAULT_IMAGE = `${SITE_URL}/brand/painel-de-comando-raio-favicon.png`;

type SeoProduct = {
  name: string;
  slug: string;
  summary?: string;
  description?: string;
  seo_title?: string;
  seo_description?: string;
  image_url?: string;
  gallery_images?: string[];
  reference_code?: string;
  brand?: string;
  price_cents?: number | null;
  stock_status?: 'in_stock' | 'out_of_stock' | 'on_demand';
};

type SeoState = {
  title: string;
  description: string;
  canonical: string;
  image?: string;
  noindex?: boolean;
  structuredData?: Record<string, unknown>;
};

const routeCopy: Record<string, Pick<SeoState, 'title' | 'description'>> = {
  '/': {
    title: 'Painéis de Comando Elétrico e Automação Industrial | Painel de Comando',
    description: 'Painéis elétricos de comando, partida estrela-triângulo, soft starter, inversores de frequência, irrigação e bombas para aplicações industriais.',
  },
  '/empresa': {
    title: 'Empresa de Painéis de Comando Elétrico | Painel de Comando',
    description: 'Conheça a Painel de Comando e nossas soluções em painéis elétricos, acionamentos e automação industrial.',
  },
  '/produtos': {
    title: 'Painéis de Comando Elétrico: Produtos e Modelos',
    description: 'Compare modelos de painéis de comando, estrela-triângulo, soft starter, inversor de frequência, irrigação e bombas.',
  },
  '/servicos': {
    title: 'Serviços em Painéis Elétricos e Automação | Painel de Comando',
    description: 'Projetos, montagem, retrofit, instalação, comissionamento e manutenção de painéis elétricos e sistemas de automação.',
  },
  '/segmentos': {
    title: 'Painéis Elétricos para Indústria e Infraestrutura',
    description: 'Soluções em painéis de comando para indústria, saneamento, irrigação, construção, agronegócio e infraestrutura.',
  },
  '/projetos': {
    title: 'Projetos de Painéis de Comando | Painel de Comando',
    description: 'Projetos e aplicações de painéis elétricos de comando, acionamentos e automação industrial.',
  },
  '/blog': {
    title: 'Conteúdo Técnico sobre Painéis de Comando',
    description: 'Artigos técnicos sobre painéis elétricos, partidas de motores, inversores, soft starters, automação e manutenção.',
  },
  '/contato': {
    title: 'Contato e Orçamento | Painel de Comando',
    description: 'Fale com a equipe da Painel de Comando e solicite orientação comercial ou orçamento para sua aplicação.',
  },
};

const catalogCopy: Record<string, Pick<SeoState, 'title' | 'description'>> = {
  'estrela-triangulo': { title: 'Painel Estrela-Triângulo: Modelos e Preços', description: 'Painéis estrela-triângulo para partida de motores em diferentes potências, tensões e configurações.' },
  'soft-starter': { title: 'Painel com Soft Starter: Modelos e Preços', description: 'Painéis com soft starter para partidas e paradas suaves, proteção e controle de motores industriais.' },
  'inversor-de-frequencia': { title: 'Painel com Inversor de Frequência: Modelos e Preços', description: 'Painéis com inversor de frequência para controle preciso de velocidade de motores e processos industriais.' },
  'bomba-de-incendio': { title: 'Painel para Bomba de Incêndio: Modelos e Preços', description: 'Painéis de comando para supervisão, acionamento e proteção de bombas de sistemas de combate a incêndio.' },
  irrigacao: { title: 'Painel para Irrigação: Modelos e Preços', description: 'Painéis elétricos para controle confiável de bombas e sistemas de irrigação.' },
  revezamento: { title: 'Painel para Revezamento de Bombas: Modelos e Preços', description: 'Painéis para alternância automática e proteção de conjuntos de bombas.' },
};

function absoluteUrl(path?: string) {
  if (!path) return DEFAULT_IMAGE;
  if (/^https?:\/\//i.test(path)) return path;
  return `${SITE_URL}${path.startsWith('/') ? path : `/${path}`}`;
}

function setMeta(selector: string, attributes: Record<string, string>) {
  let element = document.head.querySelector<HTMLMetaElement>(selector);
  if (!element) {
    element = document.createElement('meta');
    document.head.appendChild(element);
  }
  Object.entries(attributes).forEach(([name, value]) => element?.setAttribute(name, value));
}

function applySeo(state: SeoState) {
  document.title = state.title;
  setMeta('meta[name="description"]', { name: 'description', content: state.description });
  setMeta('meta[name="robots"]', { name: 'robots', content: state.noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' });
  setMeta('meta[property="og:type"]', { property: 'og:type', content: state.structuredData?.['@type'] === 'Product' ? 'product' : 'website' });
  setMeta('meta[property="og:locale"]', { property: 'og:locale', content: 'pt_BR' });
  setMeta('meta[property="og:site_name"]', { property: 'og:site_name', content: 'Painel de Comando' });
  setMeta('meta[property="og:title"]', { property: 'og:title', content: state.title });
  setMeta('meta[property="og:description"]', { property: 'og:description', content: state.description });
  setMeta('meta[property="og:url"]', { property: 'og:url', content: state.canonical });
  setMeta('meta[property="og:image"]', { property: 'og:image', content: absoluteUrl(state.image) });
  setMeta('meta[name="twitter:card"]', { name: 'twitter:card', content: 'summary_large_image' });

  let canonical = document.head.querySelector<HTMLLinkElement>('link[rel="canonical"]');
  if (!canonical) {
    canonical = document.createElement('link');
    canonical.rel = 'canonical';
    document.head.appendChild(canonical);
  }
  canonical.href = state.canonical;

  document.head.querySelector('script[data-site-structured-data]')?.remove();
  if (state.structuredData) {
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.dataset.siteStructuredData = 'true';
    script.textContent = JSON.stringify(state.structuredData);
    document.head.appendChild(script);
  }
}

function baseSeo(pathname: string, search: string): SeoState {
  const noindex = pathname.startsWith('/admin') || ['/carrinho', '/conta', '/orcamento'].some(path => pathname.startsWith(path));
  const route = routeCopy[pathname] ?? {
    title: 'Painel de Comando Elétrico e Automação Industrial',
    description: 'Soluções em painéis elétricos de comando, acionamentos e automação industrial.',
  };
  let canonical = `${SITE_URL}${pathname === '/' ? '/' : pathname.replace(/\/$/, '')}`;
  let copy = route;
  if (pathname === '/produtos') {
    const params = new URLSearchParams(search);
    const line = params.get('linha');
    if (line && catalogCopy[line]) {
      copy = catalogCopy[line];
      canonical += `?linha=${encodeURIComponent(line)}`;
    }
  }
  return {
    ...copy,
    canonical,
    noindex,
    structuredData: pathname === '/' ? {
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Painel de Comando',
      url: `${SITE_URL}/`,
      inLanguage: 'pt-BR',
    } : undefined,
  };
}

export function SiteSeo() {
  const { pathname, search } = useLocation();

  useEffect(() => {
    let active = true;
    const initial = baseSeo(pathname, search);
    applySeo(initial);

    const match = pathname.match(/^\/produtos\/([^/]+)$/);
    if (match) {
      const slug = decodeURIComponent(match[1]);
      apiRequest<SeoProduct>(`/products/${encodeURIComponent(slug)}`).then(response => {
        if (!active) return;
        const product = response.data;
        const canonical = `${SITE_URL}/produtos/${product.slug}`;
        const description = product.seo_description || product.summary || product.description?.slice(0, 155) || `Conheça ${product.name}.`;
        const availability = product.stock_status === 'out_of_stock' ? 'https://schema.org/OutOfStock' : product.stock_status === 'on_demand' ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock';
        applySeo({
          title: product.seo_title || `${product.name} | Painel de Comando`,
          description,
          canonical,
          image: product.image_url,
          structuredData: {
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: product.name,
            description,
            image: [product.image_url, ...(product.gallery_images ?? [])].filter(Boolean).map(item => absoluteUrl(item)),
            sku: product.reference_code || product.slug,
            brand: { '@type': 'Brand', name: product.brand || 'Painel de Comando' },
            url: canonical,
            ...(product.price_cents != null ? { offers: { '@type': 'Offer', priceCurrency: 'BRL', price: (product.price_cents / 100).toFixed(2), availability, url: canonical } } : {}),
          },
        });
      }).catch(() => undefined);
    }

    return () => { active = false; };
  }, [pathname, search]);

  return null;
}
