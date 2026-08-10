import { Link } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';

const solutions = [
  { code: '01', title: 'Painéis de comando', text: 'Acionamento, proteção e controle para máquinas e processos industriais.', tag: 'Comando' },
  { code: '02', title: 'CCM e distribuição', text: 'Centros de controle de motores, QGBT e quadros organizados para a operação.', tag: 'Potência' },
  { code: '03', title: 'Inversores e soft-starters', text: 'Partidas controladas e velocidade ajustada às necessidades da aplicação.', tag: 'Acionamento' },
  { code: '04', title: 'Automação com CLP e IHM', text: 'Lógica, supervisão e interfaces desenvolvidas para tornar o processo mais claro.', tag: 'Automação' },
  { code: '05', title: 'Bombas e sistemas críticos', text: 'Soluções para saneamento, irrigação, pressurização e combate a incêndio.', tag: 'Aplicação' },
  { code: '06', title: 'Retrofit e projetos especiais', text: 'Modernização e engenharia personalizada para requisitos fora do padrão.', tag: 'Sob medida' },
];

const steps = [
  ['01', 'Diagnóstico', 'Entendimento da carga, processo, ambiente, operação e pontos críticos.'],
  ['02', 'Engenharia', 'Arquitetura elétrica, especificação, diagramas e critérios de proteção.'],
  ['03', 'Fabricação', 'Montagem, identificação e organização pensadas para manutenção.'],
  ['04', 'Validação', 'Verificações, documentação e preparação para uma implantação segura.'],
];

const sectors = ['Indústria', 'Saneamento', 'Irrigação', 'Máquinas', 'Infraestrutura', 'Agronegócio'];

export default function HomePage() {
  return <>
    <section className="hero-v3">
      <div className="hero-v3__media">
        <img src="/images/hero-painel-industrial-v2.jpg" alt="Painel elétrico industrial aberto em ambiente de engenharia" fetchPriority="high" />
        <span className="hero-v3__scan" />
      </div>
      <div className="container hero-v3__content">
        <div className="hero-v3__copy">
          <span className="v3-kicker"><i /> Engenharia elétrica e automação industrial</span>
          <h1>Seu processo não pode parar.</h1>
          <p>Projetamos e fabricamos painéis elétricos sob medida para transformar requisitos de campo em uma solução segura, organizada e pronta para operar.</p>
          <div className="button-row">
            <ButtonLink to="/orcamento">Analisar meu projeto</ButtonLink>
            <ButtonLink to="/produtos" variant="secondary">Explorar soluções</ButtonLink>
          </div>
        </div>
        <div className="hero-v3__proof" aria-label="Escopo de entrega">
          <div><span>01</span><strong>Engenharia aplicada</strong></div>
          <div><span>02</span><strong>Montagem criteriosa</strong></div>
          <div><span>03</span><strong>Entrega documentada</strong></div>
        </div>
      </div>
      <a className="hero-v3__scroll" href="#solucoes"><span>Descobrir</span><i /></a>
    </section>

    <section className="decision-strip" aria-label="Compromissos técnicos">
      <div className="container decision-strip__inner">
        <strong>Da necessidade à solução</strong>
        <span>Projeto sob medida</span><span>Componentes especificados</span><span>Organização para manutenção</span><span>Suporte à implantação</span>
      </div>
    </section>

    <section className="solutions-v3" id="solucoes">
      <div className="container">
        <header className="v3-heading">
          <div><span className="v3-kicker"><i /> Encontre sua solução</span><h2>Engenharia para o ponto crítico da sua operação.</h2></div>
          <div className="v3-heading__aside"><p>Em vez de adaptar um produto genérico, começamos pela aplicação, pelas cargas e pela forma como sua equipe precisa operar.</p><Link to="/produtos">Ver portfólio completo <span aria-hidden="true">→</span></Link></div>
        </header>
        <div className="solutions-v3__grid">
          {solutions.map(({ code, title, text, tag }) => <Link className="solution-v3" to="/produtos" key={title}>
            <span>{code}</span><div><small>{tag}</small><h3>{title}</h3><p>{text}</p></div><b aria-hidden="true">↗</b>
          </Link>)}
        </div>
      </div>
    </section>

    <section className="technical-proof">
      <div className="container technical-proof__grid">
        <div className="technical-proof__intro">
          <span className="v3-kicker v3-kicker--light"><i /> O que sustenta a entrega</span>
          <h2>Um painel não termina na porta do quadro.</h2>
          <p>Uma boa solução também precisa facilitar instalação, operação, diagnóstico e manutenção ao longo do tempo.</p>
          <ButtonLink to="/servicos" variant="secondary">Conhecer serviços</ButtonLink>
        </div>
        <div className="technical-proof__list">
          <article><span>01</span><div><h3>Leitura do processo</h3><p>O projeto parte do funcionamento real da aplicação, não apenas de uma lista de componentes.</p></div></article>
          <article><span>02</span><div><h3>Arquitetura coerente</h3><p>Proteção, acionamento e controle são definidos como partes de um único sistema.</p></div></article>
          <article><span>03</span><div><h3>Manutenção em foco</h3><p>Identificação, organização interna e documentação tornam futuras intervenções mais objetivas.</p></div></article>
          <article><span>04</span><div><h3>Decisão transparente</h3><p>Escopo e premissas claros para sua equipe avaliar a solução com segurança.</p></div></article>
        </div>
      </div>
    </section>

    <section className="process-v3">
      <div className="process-v3__image"><img src="/images/montagem-painel-industrial-v2.jpg" alt="Montagem técnica e organização de cabos em painel elétrico" loading="lazy" /><span /></div>
      <div className="process-v3__content">
        <span className="v3-kicker v3-kicker--light"><i /> Método de trabalho</span>
        <h2>Clareza do primeiro levantamento à entrega.</h2>
        <p>Cada etapa reduz incertezas e aproxima a engenharia da realidade de quem instala, opera e mantém.</p>
        <ol>{steps.map(([number, title, text]) => <li key={number}><span>{number}</span><div><strong>{title}</strong><p>{text}</p></div></li>)}</ol>
        <ButtonLink to="/empresa" variant="secondary">Conheça nossa abordagem</ButtonLink>
      </div>
    </section>

    <section className="sectors-v4">
      <div className="container">
        <div className="sectors-v4__heading"><span className="v3-kicker"><i /> Onde atuamos</span><h2>Aplicações que exigem controle, disponibilidade e segurança.</h2></div>
        <div className="sectors-v4__list">{sectors.map((sector, index) => <Link to="/segmentos" key={sector}><span>0{index + 1}</span><strong>{sector}</strong><b>→</b></Link>)}</div>
      </div>
    </section>

    <section className="project-brief">
      <div className="container project-brief__grid">
        <div><span className="v3-kicker v3-kicker--light"><i /> Comece com contexto</span><h2>Conte o desafio. A engenharia começa a partir daí.</h2></div>
        <div className="project-brief__card"><strong>Para uma análise mais objetiva, envie:</strong><ul><li>Aplicação e objetivo do painel</li><li>Potência, tensão e quantidade de cargas</li><li>Forma de acionamento e automação desejada</li><li>Condições do ambiente de instalação</li></ul><ButtonLink to="/orcamento">Solicitar análise técnica</ButtonLink><Link to="/contato">Prefere conversar primeiro? Fale com a equipe →</Link></div>
      </div>
    </section>
  </>;
}
