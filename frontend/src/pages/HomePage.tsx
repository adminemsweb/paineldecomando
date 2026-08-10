import { Link } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';

const categories = [
  { code: 'ET', title: 'Estrela-triângulo', detail: 'Partida com corrente reduzida', to: '/produtos/painel-estrela-triangulo' },
  { code: 'SS', title: 'Soft starter', detail: 'Partida e parada controladas', to: '/produtos?categoria=soft-starter' },
  { code: 'VF', title: 'Inversor de frequência', detail: 'Controle de velocidade', to: '/produtos?categoria=inversor-de-frequencia' },
  { code: 'PB', title: 'Painéis para bombas', detail: 'Controle e proteção de sistemas', to: '/produtos?categoria=bombas' },
  { code: 'CCM', title: 'Centro de controle', detail: 'Distribuição e acionamentos', to: '/produtos?categoria=ccm' },
  { code: 'AU', title: 'Automação', detail: 'CLP, IHM e integração', to: '/produtos?categoria=automacao' },
];

const products = [
  { category: 'Painéis de partida', name: 'Painel Estrela-Triângulo', description: 'Redução da corrente de partida para motores industriais.', image: '/images/hero-painel-comando-poster.jpg', to: '/produtos/painel-estrela-triangulo', badge: 'Em destaque' },
  { category: 'Acionamento', name: 'Painel com Soft Starter', description: 'Partidas e paradas suaves com proteção adequada à carga.', image: '/images/montagem-painel-industrial-v2.jpg', to: '/produtos?categoria=soft-starter' },
  { category: 'Controle de velocidade', name: 'Painel com Inversor de Frequência', description: 'Controle preciso de motores e adequação ao processo.', image: '/images/hero-painel-industrial-v2.jpg', to: '/produtos?categoria=inversor-de-frequencia' },
  { category: 'Sistemas de bombeamento', name: 'Painel para Bombas', description: 'Comando, proteção e alternância para operação confiável.', image: '/images/hero-painel-comando-poster.jpg', to: '/produtos?categoria=bombas' },
];

const applications = ['Bombas', 'Compressores', 'Ventiladores', 'Irrigação', 'Saneamento', 'Máquinas industriais'];

export default function HomePage() {
  return <div className="shop-home">
    <section className="shop-hero" aria-label="Destaques da loja">
      <div className="container shop-hero__grid">
        <article className="shop-hero__main">
          <video autoPlay loop muted playsInline preload="auto" poster="/images/hero-painel-comando-poster.jpg" aria-hidden="true">
            <source src="/videos/hero-painel-comando.mp4" type="video/mp4" />
          </video>
          <div className="shop-hero__overlay" />
          <div className="shop-hero__content">
            <span>Solução em destaque</span>
            <h1>Painel Estrela-Triângulo</h1>
            <p>Partida segura para motores industriais, dimensionada para sua aplicação.</p>
            <ButtonLink to="/produtos/painel-estrela-triangulo">Conhecer o produto</ButtonLink>
          </div>
        </article>

        <div className="shop-hero__offers">
          <article className="shop-offer shop-offer--primary"><span>Projeto especial?</span><h2>Monte o painel certo para sua operação.</h2><p>Envie os dados da carga e receba uma análise técnica.</p><Link to="/orcamento">Solicitar cotação <span aria-hidden="true">→</span></Link></article>
          <article className="shop-offer shop-offer--secondary"><span>Precisa escolher?</span><h2>Conte com atendimento técnico antes da compra.</h2><Link to="/contato">Falar com especialista <span aria-hidden="true">→</span></Link></article>
        </div>
      </div>
    </section>

    <section className="shop-categories" aria-labelledby="categories-title">
      <div className="container">
        <header className="shop-section-heading"><div><span>Compre por categoria</span><h2 id="categories-title">Encontre a solução pelo tipo de acionamento.</h2></div><Link to="/produtos">Ver todas as categorias <span aria-hidden="true">→</span></Link></header>
        <div className="shop-category-grid">{categories.map(category => <Link to={category.to} key={category.code} className="shop-category-card"><span>{category.code}</span><div><strong>{category.title}</strong><small>{category.detail}</small></div><b aria-hidden="true">→</b></Link>)}</div>
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

    <section className="shop-quote"><div className="container shop-quote__inner"><div><span>Compra assistida</span><h2>Não encontrou uma configuração pronta?</h2><p>Informe motor, tensão, potência e aplicação. A equipe prepara uma solução adequada ao seu projeto.</p></div><div><ButtonLink to="/orcamento">Solicitar cotação técnica</ButtonLink><Link to="/contato">Falar primeiro com a equipe</Link></div></div></section>
  </div>;
}
