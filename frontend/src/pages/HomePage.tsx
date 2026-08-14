import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { Icon } from '../components/common/Icon';
import { companyConfig } from '../constants/company';

const popularProducts = [
  { code: '01', title: 'Painel Estrela-Triângulo', detail: 'Partida com redução de corrente', to: '/produtos/painel-estrela-triangulo' },
  { code: '02', title: 'Painel com Inversor', detail: 'Controle de velocidade e processo', to: '/produtos?categoria=inversor-de-frequencia' },
  { code: '03', title: 'Painel para Bombas', detail: 'Comando, proteção e revezamento', to: '/produtos?categoria=bombas' },
  { code: '04', title: 'Painel com Soft Starter', detail: 'Partidas e paradas controladas', to: '/produtos?categoria=soft-starter' },
];

const products = [
  { category: 'Painéis de partida', name: 'Painel Estrela-Triângulo', description: 'Redução da corrente de partida para motores industriais.', image: '/images/hero-painel-comando-poster.jpg', to: '/produtos/painel-estrela-triangulo', badge: 'Em destaque' },
  { category: 'Acionamento', name: 'Painel com Soft Starter', description: 'Partidas e paradas suaves com proteção adequada à carga.', image: '/images/montagem-painel-industrial-v2.jpg', to: '/produtos?categoria=soft-starter' },
  { category: 'Controle de velocidade', name: 'Painel com Inversor de Frequência', description: 'Controle preciso de motores e adequação ao processo.', image: '/images/hero-painel-industrial-v2.jpg', to: '/produtos?categoria=inversor-de-frequencia' },
  { category: 'Sistemas de bombeamento', name: 'Painel para Bombas', description: 'Comando, proteção e alternância para operação confiável.', image: '/images/hero-painel-comando-poster.jpg', to: '/produtos?categoria=bombas' },
];

const applications = ['Bombas', 'Compressores', 'Ventiladores', 'Irrigação', 'Saneamento', 'Máquinas industriais'];

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
    image: '/images/hero-painel-cutout.png',
    imageAlt: 'Painel industrial com inversor de frequência',
      badge: 'Painel com Inversor',
    to: '/produtos?categoria=inversor-de-frequencia',
  },
  {
    theme: 'navy',
    eyebrow: 'Engenharia sob medida',
    title: 'Seu projeto começa com uma boa especificação.',
    description: 'Conte com atendimento técnico para definir potência, tensão, proteções e recursos de comando antes de fechar seu pedido.',
    tags: ['Projeto personalizado', 'Suporte técnico', 'Compra assistida'],
    image: '/images/montagem-painel-industrial-v2.jpg',
    imageAlt: 'Montagem técnica de painel elétrico industrial',
    badge: 'Projeto Personalizado',
    to: '/orcamento',
  },
];

export default function HomePage() {
  const [activeHeroSlide, setActiveHeroSlide] = useState(0);
  const [activeTestimonial, setActiveTestimonial] = useState(0);
  const [testimonialPaused, setTestimonialPaused] = useState(false);

  useEffect(() => {
    const timer = window.setInterval(() => {
      setActiveHeroSlide(current => (current + 1) % heroSlides.length);
    }, 6500);
    return () => window.clearInterval(timer);
  }, []);

  useEffect(() => {
    if (testimonialPaused) return;
    const timer = window.setInterval(() => {
      setActiveTestimonial(current => (current + 1) % testimonials.length);
    }, 4000);
    return () => window.clearInterval(timer);
  }, [testimonialPaused]);

  const heroSlide = heroSlides[activeHeroSlide];
  const testimonialRating = testimonials.reduce((total, testimonial) => total + testimonial.rating, 0) / testimonials.length;
  const visibleTestimonials = [0, 1, 2].map(offset => testimonials[(activeTestimonial + offset) % testimonials.length]);
  const changeTestimonial = (direction: number) => setActiveTestimonial(current => (current + direction + testimonials.length) % testimonials.length);

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
        <header className="shop-section-heading"><div><span>Produtos em destaque</span><h2 id="products-title">Soluções para comprar ou cotar.</h2></div><p>Os valores serão exibidos quando configurações, prazos e condições comerciais forem aprovados.</p></header>
        <div className="shop-product-grid">{products.map(product => <article className="shop-product" key={product.name}>
          <Link className="shop-product__image" to={product.to}><img src={product.image} alt="" loading="lazy"/>{product.badge && <span>{product.badge}</span>}</Link>
          <div className="shop-product__content"><small>{product.category}</small><h3><Link to={product.to}>{product.name}</Link></h3><p>{product.description}</p><div className="shop-product__price"><span><small>Valor</small><strong>Sob consulta</strong></span><Link to={product.to} aria-label={`Ver ${product.name}`}>→</Link></div></div>
        </article>)}</div>
      </div>
    </section>

    <section className="shop-application" aria-labelledby="application-title">
      <div className="container shop-application__grid">
        <div><span>Compre pela aplicação</span><h2 id="application-title">Comece pelo equipamento que precisa controlar.</h2><p>Ajudamos a relacionar carga, acionamento e proteção antes de fechar o pedido.</p></div>
        <div className="shop-application__links">{applications.map(application => <Link to={`/produtos?aplicacao=${encodeURIComponent(application.toLowerCase())}`} key={application}>{application}<span aria-hidden="true">→</span></Link>)}</div>
      </div>
    </section>

    <section className="shop-testimonials" aria-labelledby="testimonials-title" aria-roledescription="carrossel"><div className="container"><header className="shop-testimonials__heading"><div><span>Feedbacks de clientes</span><h2 id="testimonials-title">Quem compra nossos painéis de comando recomenda.</h2><p>Opiniões sobre atendimento, acabamento e organização dos painéis entregues.</p><div className="shop-testimonials__rating"><strong>{testimonialRating.toFixed(1).replace('.', ',')}</strong><span aria-label={`${testimonialRating.toFixed(1).replace('.', ',')} de 5 estrelas`}>★★★★★</span><small>Média das {testimonials.length} avaliações exibidas</small></div></div><div className="shop-testimonials__controls"><span>{String(activeTestimonial + 1).padStart(2, '0')} / {String(testimonials.length).padStart(2, '0')}</span><button type="button" aria-label="Depoimento anterior" onClick={() => changeTestimonial(-1)}>←</button><button type="button" aria-label={testimonialPaused ? 'Continuar carrossel automático' : 'Pausar carrossel automático'} onClick={() => setTestimonialPaused(current => !current)}>{testimonialPaused ? '▶' : 'Ⅱ'}</button><button type="button" aria-label="Próximo depoimento" onClick={() => changeTestimonial(1)}>→</button></div></header><div className="shop-testimonials__viewport" aria-live={testimonialPaused ? 'polite' : 'off'}><div className="shop-testimonials__grid" key={activeTestimonial}>{visibleTestimonials.map(testimonial => <article className="testimonial-card" key={testimonial.name}><div className="testimonial-card__top"><span>{testimonial.product}</span><div className="testimonial-card__stars" aria-label={`${testimonial.rating} de 5 estrelas`}>{[1, 2, 3, 4, 5].map(star => <i className={star <= testimonial.rating ? 'active' : ''} aria-hidden="true" key={star}>★</i>)}</div></div><p>“{testimonial.text}”</p><footer className="testimonial-card__person"><span aria-hidden="true">{testimonial.name.split(' ').map(part => part.charAt(0)).join('').slice(0, 2)}</span><div><strong>{testimonial.name}</strong><small>{testimonial.area}</small></div><Icon name="check" size={17}/></footer></article>)}</div></div><div className="shop-testimonials__pages" aria-label="Selecionar depoimento">{testimonials.map((testimonial, index) => <button type="button" className={index === activeTestimonial ? 'active' : ''} aria-label={`Mostrar depoimento ${index + 1} de ${testimonials.length}`} aria-current={index === activeTestimonial ? 'true' : undefined} onClick={() => setActiveTestimonial(index)} key={testimonial.name}/>)}</div></div></section>

    <section className="shop-faq" aria-labelledby="faq-title"><div className="container shop-faq__inner"><header><span>FAQ</span><h2 id="faq-title">Perguntas frequentes</h2><p>Tudo o que você precisa saber antes de definir o painel ideal para sua aplicação.</p></header><div className="shop-faq__list">{frequentlyAskedQuestions.map(item => <details key={item.question}><summary>{item.question}<span aria-hidden="true"/></summary><p>{item.answer}</p></details>)}</div></div></section>

    <section className="shop-partner" aria-labelledby="partner-title">
      <div className="container shop-partner__card">
        <div className="shop-partner__brand"><span>Parceiros em destaque</span><div className="shop-partner__brand-logos"><img className="shop-partner__mr-logo" src="/brand/mr-drives-logo-transparent.png" alt="MR Drives"/><i aria-hidden="true"/><img className="shop-partner__metal-logo" src="/brand/metal-life-logo.png" alt="Metal Life"/></div></div>
        <div className="shop-partner__content"><span>Em parceria com MR Drives e Metal Life</span><h2 id="partner-title">Painéis de comando com componentes selecionados.</h2><p>Conte com apoio para especificar, cotar e escolher a configuração adequada do painel para sua aplicação.</p><div className="shop-partner__tags"><span>Painéis com inversor</span><span>Painéis com Soft Starter</span></div></div>
        <div className="shop-partner__actions"><ButtonLink to="/produtos?parceiro=mr-drives">Ver produtos</ButtonLink><a href={`https://wa.me/${companyConfig.whatsapp}`} target="_blank" rel="noreferrer"><Icon name="whatsapp" size={19}/> Falar no WhatsApp</a></div>
      </div>
    </section>
  </div>;
}
