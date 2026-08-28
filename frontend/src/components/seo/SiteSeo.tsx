import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { apiRequest } from '../../services/api';
import { companyConfig } from '../../constants/company';

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
    description: 'Painéis de comando elétrico para motores, bombas, irrigação e máquinas. Compare modelos ou solicite um painel industrial sob medida com entrega para todo o Brasil.',
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
  '/painel-de-comando-eletrico': {
    title: 'Painel de Comando Elétrico para Motores e Máquinas',
    description: 'Painéis de comando elétrico para partida, proteção e controle de motores, bombas, irrigação e máquinas industriais. Veja modelos e solicite orçamento.',
  },
  '/paineis-eletricos-sob-medida': {
    title: 'Painéis Elétricos Industriais Sob Medida | Orçamento',
    description: 'Solicite painéis elétricos industriais sob medida para máquinas, motores, bombas e automação. Configuração conforme os dados da sua aplicação.',
  },
  '/montagem-de-paineis-eletricos-industriais': {
    title: 'Montagem de Painéis Elétricos Industriais',
    description: 'Montagem de painéis elétricos industriais para comando, acionamento e proteção, com organização dos componentes e verificação funcional.',
  },
  '/painel-de-comando-sorocaba': {
    title: 'Painel de Comando em Sorocaba | Atendimento e Orçamento',
    description: 'Painéis de comando elétrico em Sorocaba para indústrias, bombas e irrigação, com compra assistida e entregas para todo o Brasil.',
  },
};

const commercialQuestions: Record<string, Array<{ question: string; answer: string }>> = {
  '/painel-de-comando-eletrico': [
    { question: 'Como escolher o painel de comando correto?', answer: 'A escolha considera principalmente aplicação, potência do motor, tensão da rede, corrente nominal, quantidade de partidas e forma de acionamento.' },
    { question: 'O painel já chega pronto para instalação?', answer: 'O produto é preparado para integração à aplicação descrita. A instalação deve ser realizada por profissional habilitado, seguindo o projeto e as normas aplicáveis.' },
    { question: 'É possível solicitar uma configuração diferente?', answer: 'Sim. Quando um modelo de catálogo não atende ao projeto, é possível solicitar uma análise para configuração sob medida.' },
  ],
  '/paineis-eletricos-sob-medida': [
    { question: 'Quais informações são necessárias para o orçamento?', answer: 'Informe a aplicação, potência e tensão dos motores, quantidade de cargas, forma de acionamento, local de instalação e funções esperadas.' },
    { question: 'Vocês adaptam um painel já existente?', answer: 'A possibilidade depende do estado, da documentação e do escopo. Envie fotos e dados do painel para uma avaliação inicial.' },
    { question: 'O orçamento de painel sob medida é imediato?', answer: 'Projetos especiais precisam de análise. O prazo da proposta varia conforme a quantidade de cargas e a complexidade funcional.' },
  ],
  '/montagem-de-paineis-eletricos-industriais': [
    { question: 'Vocês montam painéis a partir de um projeto existente?', answer: 'Sim, o projeto pode ser analisado para verificar escopo, documentação, componentes e condições de fornecimento.' },
    { question: 'Quais tipos de painéis são montados?', answer: 'O catálogo inclui partidas de motores, soft starters, inversores de frequência, bombas, irrigação e revezamento, além de configurações especiais sob análise.' },
    { question: 'A instalação em campo está incluída?', answer: 'As condições de cada fornecimento são apresentadas na proposta. A instalação elétrica deve sempre ser executada por profissionais habilitados.' },
  ],
  '/painel-de-comando-sorocaba': [
    { question: 'Vocês atendem somente Sorocaba?', answer: 'Não. As entregas são realizadas em território brasileiro e o atendimento comercial também pode ser feito por telefone, e-mail e WhatsApp.' },
    { question: 'Posso solicitar orçamento pelo WhatsApp?', answer: 'Sim. Envie os dados do equipamento e informe potência, tensão, aplicação e cidade.' },
    { question: 'Existe painel pronto para compra?', answer: 'Sim. O catálogo apresenta modelos com características e preços. Projetos que exigem alterações seguem para cotação técnica.' },
  ],
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
  const questions = commercialQuestions[pathname];
  const commercialStructuredData = questions ? {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'Service',
        name: copy.title,
        description: copy.description,
        url: canonical,
        areaServed: { '@type': 'Country', name: 'Brasil' },
        provider: { '@type': 'Organization', name: 'Painel de Comando', url: `${SITE_URL}/` },
      },
      {
        '@type': 'FAQPage',
        mainEntity: questions.map(item => ({
          '@type': 'Question',
          name: item.question,
          acceptedAnswer: { '@type': 'Answer', text: item.answer },
        })),
      },
    ],
  } : undefined;
  return {
    ...copy,
    canonical,
    noindex,
    structuredData: pathname === '/' ? {
      '@context': 'https://schema.org',
      '@graph': [
        {
          '@type': 'Organization',
          '@id': `${SITE_URL}/#organization`,
          name: 'Painel de Comando',
          legalName: companyConfig.legalName,
          url: `${SITE_URL}/`,
          logo: `${SITE_URL}/brand/painel-de-comando-raio-favicon.png`,
          email: companyConfig.email,
          telephone: companyConfig.phone,
          address: {
            '@type': 'PostalAddress',
            streetAddress: 'Rua Cabreúva',
            addressLocality: 'Sorocaba',
            addressRegion: 'SP',
            postalCode: '18085-340',
            addressCountry: 'BR',
          },
          contactPoint: {
            '@type': 'ContactPoint',
            telephone: companyConfig.phone,
            contactType: 'sales',
            areaServed: 'BR',
            availableLanguage: 'Portuguese',
          },
        },
        {
          '@type': 'WebSite',
          '@id': `${SITE_URL}/#website`,
          name: 'Painel de Comando',
          url: `${SITE_URL}/`,
          inLanguage: 'pt-BR',
          publisher: { '@id': `${SITE_URL}/#organization` },
        },
      ],
    } : commercialStructuredData,
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
