import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { Icon } from '../components/common/Icon';
import { companyConfig } from '../constants/company';

const popularProducts = [
  { code: '01', title: 'Painel Estrela-Triângulo', detail: 'Partida com redução de corrente', to: '/produtos?linha=estrela-triangulo' },
  { code: '02', title: 'Painel com Inversor', detail: 'Controle de velocidade e processo', to: '/produtos?linha=inversor-de-frequencia' },
  { code: '03', title: 'Painel para Bombas', detail: 'Comando, proteção e segurança', to: '/produtos?linha=bomba-de-incendio' },
  { code: '04', title: 'Painel com Soft Starter', detail: 'Partidas e paradas controladas', to: '/produtos?linha=soft-starter' },
];

const products = [
  { category: 'Bomba de incêndio', name: 'Painel Bomba de Incêndio 10CV 220V', description: 'Acionamento estrela-triângulo seguro para bombas de incêndio trifásicas.', image: '/images/painel-bomba-incendio-10cv-220v-vermelho.png', to: '/produtos/painel-estrela-triangulo-bomba-incendio-10cv-220v', price: 'R$ 2.150,00', installment: '3x de R$ 716,67', badge: 'Em destaque' },
  { category: 'Acionamento', name: 'Painel Soft Starter SSW07 45A 30CV 380V', description: 'Partida suave e proteção para motores trifásicos de até 30 CV.', image: '/images/painel-soft-starter-com-logo.png', to: '/produtos/painel-soft-starter-ssw07-45a-30cv-380v', price: 'R$ 6.247,50', installment: '3x de R$ 2.082,50' },
  { category: 'Controle de velocidade', name: 'Painel com Inversor CFW300 1CV 220V', description: 'Controle preciso de velocidade com entrada monofásica e saída trifásica.', image: '/images/painel-inversor-cfw300-1cv-220v-principal.png', to: '/produtos/painel-inversor-cfw300-1cv-220v-mono', price: 'R$ 2.205,00', installment: '3x de R$ 735,00' },
  { category: 'Bomba de incêndio', name: 'Painel Bomba de Incêndio 20CV + Jockey 1CV', description: 'Comando manual e automático para sistemas de combate a incêndio em 380 V.', image: '/images/painel-bomba-incendio-20cv-jockey-1cv-380v-frente.png', to: '/produtos/painel-bomba-incendio-20cv-jockey-1cv-380v', price: 'R$ 2.128,00', installment: '3x de R$ 709,33' },
];

const applications = ['Bombas', 'Compressores', 'Ventiladores', 'Irrigação', 'Saneamento', 'Máquinas industriais'];

const manufacturingSteps = [
  { code: '01', title: 'Levantamento', detail: 'Entendimento da aplicação, cargas e condições de operação.' },
  { code: '02', title: 'Engenharia', detail: 'Definição técnica, dimensionamento e alinhamento do escopo.' },
  { code: '03', title: 'Fabricação', detail: 'Montagem, identificação e organização dos componentes.' },
  { code: '04', title: 'Validação', detail: 'Inspeções, testes funcionais e preparação para entrega.' },
];

const testimonials = [
  { name: 'Marcos P.', area: 'Comprador industrial', product: 'Painel Estrela-Triângulo', rating: 5, text: 'O painel chegou bem acabado, com os componentes identificados e a montagem interna organizada.' },
  { name: 'Juliana R.', area: 'Manutenção industrial', product: 'Painel com Soft Starter', rating: 5, text: 'A equipe confirmou tensão, potência e forma de acionamento antes de preparar o painel.' },
  { name: 'Eduardo L.', area: 'Integrador elétrico', product: 'Painel com Amperímetro', rating: 5, text: 'O painel veio com identificação clara e boa disposição dos componentes para a instalação.' },
  { name: 'Carlos M.', area: 'Irrigação', product: 'Painel para Bombas', rating: 4, text: 'Recebemos um painel compacto, bem protegido e adequado ao comando da bomba da aplicação.' },
  { name: 'Fernanda S.', area: 'Engenharia', product: 'Painel de Comando Personalizado', rating: 5, text: 'O atendimento ajudou a organizar os requisitos do projeto e deixou a especificação mais segura.' },
  { name: 'Rafael T.', area: 'Saneamento', product: 'Painel de Revezamento', rating: 5, text: 'O gabinete tem acabamento profissional e a organização interna facilitou a conferência em campo.' },
];

const frequentlyAskedQuestions = [
  { question: 'Qual é o prazo de entrega?', answer: 'O prazo depende do modelo, da configuração e da disponibilidade dos componentes. A previsão é confirmada no orçamento antes do fechamento do pedido.' },
  { question: 'Os painéis podem ser instalados em áreas externas?', answer: 'Sim, desde que o grau de proteção, a ventilação e os materiais sejam especificados para o ambiente. Nossa equipe ajuda a definir a configuração adequada.' },
  { question: 'Preciso trocar meu motor atual?', answer: 'Nem sempre. Avaliamos potência, tensão, corrente, regime de trabalho e aplicação para verificar a compatibilidade do motor com o painel ou acionamento.' },
  { question: 'Quais aplicações são recomendadas?', answer: 'As soluções atendem bombas, compressores, ventiladores, irrigação, saneamento e diferentes máquinas industriais.' },
  { question: 'Vocês fazem o dimensionamento técnico?', answer: 'Sim. Com os dados da carga e da instalação, a equipe orienta a seleção e prepara uma proposta compatível com a aplicação.' },
];

const heroSlides = [
  {
    theme: 'gray',
    eyebrow: 'Painéis e automação industrial',
    title: 'O painel certo para sua operação industrial.',
    description: 'Compre painéis para partida, acionamento e automação com orientação técnica e configuração dimensionada para sua aplicação.',
    tags: ['Partida de motores', 'Proteção elétrica', 'Automação industrial'],
    image: '/images/logopc.png',
    imageAlt: 'Painel elétrico industrial em gabinete fechado',
    badge: 'Painel Estrela-Triângulo',
    to: '/produtos/painel-estrela-triangulo',
  },
  {
    theme: 'yellow',
    eyebrow: 'Controle preciso e eficiência',
    title: 'Mais controle para cada etapa do processo.',
      description: 'Painéis de comando com inversor de frequência para controlar velocidade, proteger o conjunto e adequar a operação ao processo.',
    tags: ['Controle de velocidade', 'Economia de energia', 'Maior vida útil'],
    image: '/images/painel-estrela-triangulo-15cv-fechado-logo.png',
    imageAlt: 'Painel de comando industrial cinza com acionamento manual e automático',
      badge: 'Painel com Inversor',
    to: '/produtos?categoria=inversor-de-frequencia',
  },
  {
    theme: 'red',
    eyebrow: 'Proteção para sistemas de incêndio',
    title: 'Painel de comando para bomba de incêndio.',
    description: 'Acionamento estrela-triângulo confiável para sua bomba de incêndio, com proteção, parada de emergência e componentes industriais.',
    tags: ['Pronto para emergências', 'Proteção IP54', 'Comando seguro'],
    image: '/images/painel-bomba-incendio-10cv-220v-vermelho.png',
    imageAlt: 'Painel de comando vermelho para bomba de incêndio',
    badge: 'Painel para Bomba de Incêndio',
    to: '/produtos/painel-estrela-triangulo-bomba-incendio-10cv-220v',
  },
];

export default function HomePage() {
  const [activeHeroSlide, setActiveHeroSlide] = useState(0);
  const [activeTestimonial, setActiveTestimonial] = useState(0);

  useEffect(() => {
    const timer = window.setInterval(() => {
      setActiveHeroSlide(current => (current + 1) % heroSlides.length);
    }, 6500);
    return () => window.clearInterval(timer);
  }, []);

  useEffect(() => {
    const timer = window.setInterval(() => {
      setActiveTestimonial(current => (current + 1) % testimonials.length);
    }, 4000);
    return () => window.clearInterval(timer);
  }, []);

  const heroSlide = heroSlides[activeHeroSlide];
  const testimonialRating = testimonials.reduce((total, testimonial) => total + testimonial.rating, 0) / testimonials.length;
  const visibleTestimonials = [0, 1, 2].map(offset => testimonials[(activeTestimonial + offset) % testimonials.length]);

  return <div className="shop-home">
    <section className={`storefront-hero storefront-hero--${heroSlide.theme}`} aria-roledescription="carrossel" aria-label="Destaques de produtos">
      <div className="container storefront-hero__grid" key={activeHeroSlide}>
        <div className="storefront-hero__content">
          <span>{heroSlide.eyebrow}</span>
          <h1>{heroSlide.title}</h1>
          <p>{heroSlide.description}</p>
          <div className="storefront-hero__specs">{heroSlide.tags.map(tag => <span key={tag}>{tag}</span>)}</div>
          <div className="storefront-hero__actions"><ButtonLink to={heroSlide.to}>Ver solução</ButtonLink><ButtonLink to="/orcamento" variant="secondary">Montar meu projeto</ButtonLink></div>
        </div>
        <Link className="storefront-hero__product" to={heroSlide.to} aria-label={`Conhecer ${heroSlide.badge}`}>
          <span className="storefront-hero__arc storefront-hero__arc--one"/><span className="storefront-hero__arc storefront-hero__arc--two"/><span className="storefront-hero__arc storefront-hero__arc--three"/>
          <img src={heroSlide.image} alt={heroSlide.imageAlt} />
          <span className="storefront-hero__badge"><small>Em destaque</small><strong>{heroSlide.badge}</strong></span>
        </Link>
      </div>
    </section>

    <aside className="storefront-benefits" aria-label="Benefícios da compra"><div className="container storefront-benefits__inner"><div><Icon name="truck" size={38}/><p><strong>Frete Grátis</strong><span>para o Sudeste</span></p></div><div><Icon name="discount" size={38}/><p><strong>Pague com Pix</strong><span>Desconto de 5% à vista</span></p></div><div><Icon name="creditCard" size={38}/><p><strong>Pague com Cartão</strong><span>Em até 3x sem juros</span></p></div><div><Icon name="shield" size={38}/><p><strong>Segurança</strong><span>Seus dados protegidos</span></p></div></div></aside>

    <section className="shop-categories" aria-labelledby="popular-title">
      <div className="container">
        <header className="shop-section-heading"><div><span>Mais buscados</span><h2 id="popular-title">Produtos mais procurados.</h2></div><Link to="/produtos">Ver todo o catálogo <span aria-hidden="true">→</span></Link></header>
        <div className="shop-category-grid shop-category-grid--popular">{popularProducts.map(product => <Link to={product.to} key={product.code} className="shop-category-card"><span>{product.code}</span><div><strong>{product.title}</strong><small>{product.detail}</small></div><b aria-hidden="true">→</b></Link>)}</div>
      </div>
    </section>

    <section className="shop-products" aria-labelledby="products-title">
      <div className="container">
        <header className="shop-section-heading"><div><span>Produtos em destaque</span><h2 id="products-title">Painéis prontos para comprar.</h2></div><p>Compare modelos, preços e configurações para encontrar o painel adequado à sua aplicação.</p></header>
        <div className="shop-product-grid">{products.map(product => <article className="shop-product" key={product.name}>
          <Link className="shop-product__image" to={product.to}><img src={product.image} alt={product.name} loading="lazy"/>{product.badge && <span>{product.badge}</span>}</Link>
          <div className="shop-product__content"><small>{product.category}</small><h3><Link to={product.to}>{product.name}</Link></h3><p>{product.description}</p><div className="shop-product__price"><span><small>Valor</small><strong>{product.price}</strong><small>{product.installment} sem juros</small></span><Link to={product.to} aria-label={`Ver ${product.name}`}>→</Link></div></div>
        </article>)}</div>
      </div>
    </section>

    <section className="manufacturing-flow" aria-labelledby="manufacturing-flow-title">
      <div className="container">
        <header className="manufacturing-flow__heading">
          <span>Como trabalhamos</span>
          <h2 id="manufacturing-flow-title">Fluxo de Fabricação</h2>
          <p>Do levantamento inicial aos testes finais, cada painel passa por uma sequência técnica clara e controlada.</p>
        </header>
        <ol className="manufacturing-flow__steps">
          {manufacturingSteps.map((step, index) => <li className={`manufacturing-flow__step manufacturing-flow__step--${index + 1}`} key={step.code}>
            <span aria-hidden="true">{step.code}</span>
            <div><h3>{step.title}</h3><p>{step.detail}</p></div>
          </li>)}
        </ol>
      </div>
    </section>

    <section className="shop-application" aria-labelledby="application-title">
      <div className="container shop-application__grid">
        <div><span>Compre pela aplicação</span><h2 id="application-title">Comece pelo equipamento que precisa controlar.</h2><p>Ajudamos a relacionar carga, acionamento e proteção antes de fechar o pedido.</p></div>
        <div className="shop-application__links">{applications.map(application => <Link to={`/produtos?aplicacao=${encodeURIComponent(application.toLowerCase())}`} key={application}>{application}<span aria-hidden="true">→</span></Link>)}</div>
      </div>
    </section>

    <section className="shop-testimonials" aria-labelledby="testimonials-title" aria-roledescription="carrossel"><div className="container"><header className="shop-testimonials__heading"><div><span>Feedbacks de clientes</span><h2 id="testimonials-title">Quem compra nossos painéis de comando recomenda.</h2><p>Opiniões sobre atendimento, acabamento e organização dos painéis entregues.</p><div className="shop-testimonials__rating"><strong>{testimonialRating.toFixed(1).replace('.', ',')}</strong><span aria-label={`${testimonialRating.toFixed(1).replace('.', ',')} de 5 estrelas`}>★★★★★</span><small>Média das {testimonials.length} avaliações exibidas</small></div></div></header><div className="shop-testimonials__viewport" aria-live="off"><div className="shop-testimonials__grid" key={activeTestimonial}>{visibleTestimonials.map(testimonial => <article className="testimonial-card" key={testimonial.name}><div className="testimonial-card__top"><span>{testimonial.product}</span><div className="testimonial-card__stars" aria-label={`${testimonial.rating} de 5 estrelas`}>{[1, 2, 3, 4, 5].map(star => <i className={star <= testimonial.rating ? 'active' : ''} aria-hidden="true" key={star}>★</i>)}</div></div><p>“{testimonial.text}”</p><footer className="testimonial-card__person"><span aria-hidden="true">{testimonial.name.split(' ').map(part => part.charAt(0)).join('').slice(0, 2)}</span><div><strong>{testimonial.name}</strong><small>{testimonial.area}</small></div><Icon name="check" size={17}/></footer></article>)}</div></div><div className="shop-testimonials__pages" aria-label="Selecionar depoimento">{testimonials.map((testimonial, index) => <button type="button" className={index === activeTestimonial ? 'active' : ''} aria-label={`Mostrar depoimento ${index + 1} de ${testimonials.length}`} aria-current={index === activeTestimonial ? 'true' : undefined} onClick={() => setActiveTestimonial(index)} key={testimonial.name}/>)}</div></div></section>

    <section className="shop-faq" aria-labelledby="faq-title"><div className="container shop-faq__inner"><header><span>FAQ</span><h2 id="faq-title">Perguntas frequentes</h2><p>Tudo o que você precisa saber antes de definir o painel ideal para sua aplicação.</p></header><div className="shop-faq__list">{frequentlyAskedQuestions.map(item => <details key={item.question}><summary>{item.question}<span aria-hidden="true"/></summary><p>{item.answer}</p></details>)}</div></div></section>

    <section className="shop-partner" aria-labelledby="partner-title">
      <div className="container shop-partner__card">
        <div className="shop-partner__brand"><span>Parceiros em destaque</span><video className="shop-partner__brand-video" src="/videos/marcas.mp4" aria-label="Marcas parceiras MR Drives e Metal Life" autoPlay loop muted playsInline preload="metadata"/></div>
        <div className="shop-partner__content"><span>Em parceria com MR Drives e Metal Life</span><h2 id="partner-title">Painéis de comando com componentes selecionados.</h2><p>Conte com apoio para especificar, cotar e escolher a configuração adequada do painel para sua aplicação.</p><div className="shop-partner__tags"><span>Painéis com inversor</span><span>Painéis com Soft Starter</span></div></div>
        <div className="shop-partner__actions"><ButtonLink to="/produtos?parceiro=mr-drives">Ver produtos</ButtonLink><a href={`https://wa.me/${companyConfig.whatsapp}`} target="_blank" rel="noreferrer"><Icon name="whatsapp" size={19}/> Falar no WhatsApp</a></div>
      </div>
    </section>
  </div>;
}
