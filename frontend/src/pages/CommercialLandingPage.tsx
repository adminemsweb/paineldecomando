import { Link } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { Icon } from '../components/common/Icon';
import { companyConfig } from '../constants/company';

export type CommercialLandingKind = 'comando' | 'sob-medida' | 'montagem' | 'sorocaba';

type LandingContent = {
  eyebrow: string;
  title: string;
  intro: string;
  lead: string;
  benefits: Array<{ title: string; text: string }>;
  applications: string[];
  steps: Array<{ title: string; text: string }>;
  questions: Array<{ question: string; answer: string }>;
};

const content: Record<CommercialLandingKind, LandingContent> = {
  comando: {
    eyebrow: 'Controle, proteção e acionamento',
    title: 'Painel de comando elétrico para motores e máquinas',
    intro: 'Encontre painéis de comando elétrico para partida, proteção e controle de motores em aplicações industriais, sistemas de bombeamento e irrigação.',
    lead: 'Escolha um modelo do catálogo ou solicite uma configuração de acordo com potência, tensão, corrente e forma de acionamento da sua aplicação.',
    benefits: [
      { title: 'Partida de motores', text: 'Soluções com partida direta, estrela-triângulo, soft starter e inversor de frequência.' },
      { title: 'Proteção elétrica', text: 'Configurações voltadas à proteção do motor e à operação segura do equipamento.' },
      { title: 'Comando local ou remoto', text: 'Opções de acionamento compatíveis com diferentes necessidades de operação e processo.' },
    ],
    applications: ['Bombas hidráulicas', 'Sistemas de irrigação', 'Compressores', 'Ventiladores', 'Máquinas industriais', 'Saneamento'],
    steps: [
      { title: 'Informe a aplicação', text: 'Explique qual equipamento será controlado e como ele deve operar.' },
      { title: 'Confirme os dados elétricos', text: 'Potência, tensão, corrente e regime de trabalho orientam a seleção.' },
      { title: 'Compare a solução', text: 'A equipe apresenta a configuração e as condições comerciais adequadas.' },
    ],
    questions: [
      { question: 'Como escolher o painel de comando correto?', answer: 'A escolha considera principalmente aplicação, potência do motor, tensão da rede, corrente nominal, quantidade de partidas e forma de acionamento.' },
      { question: 'O painel já chega pronto para instalação?', answer: 'O produto é preparado para integração à aplicação descrita. A instalação deve ser realizada por profissional habilitado, seguindo o projeto e as normas aplicáveis.' },
      { question: 'É possível solicitar uma configuração diferente?', answer: 'Sim. Quando um modelo de catálogo não atende ao projeto, é possível solicitar uma análise para configuração sob medida.' },
    ],
  },
  'sob-medida': {
    eyebrow: 'Configuração para cada aplicação',
    title: 'Painéis elétricos industriais sob medida',
    intro: 'Desenvolvimento de painéis elétricos sob medida para máquinas, motores, bombas e processos que exigem uma configuração diferente dos modelos padronizados.',
    lead: 'O levantamento técnico organiza os requisitos do projeto antes da proposta, reduzindo incompatibilidades entre carga, rede elétrica, comando e proteção.',
    benefits: [
      { title: 'Escopo alinhado', text: 'Levantamento da aplicação, cargas, comandos, intertravamentos e condições de operação.' },
      { title: 'Configuração documentada', text: 'Definição dos requisitos técnicos e comerciais antes do início da preparação do painel.' },
      { title: 'Integração ao processo', text: 'Alternativas para comando, sinalização, proteção e acionamento conforme a necessidade informada.' },
    ],
    applications: ['Máquinas especiais', 'Linhas de processo', 'Bombas e reservatórios', 'Irrigação de grande porte', 'Retrofit de comandos', 'Automação industrial'],
    steps: [
      { title: 'Levantamento', text: 'Recebemos os dados elétricos, funcionais e ambientais da aplicação.' },
      { title: 'Definição técnica', text: 'Organizamos a arquitetura do comando, proteções e componentes necessários.' },
      { title: 'Proposta', text: 'Você recebe o escopo, o valor e a previsão de atendimento para aprovação.' },
    ],
    questions: [
      { question: 'Quais informações são necessárias para o orçamento?', answer: 'Informe a aplicação, potência e tensão dos motores, quantidade de cargas, forma de acionamento, local de instalação e funções esperadas.' },
      { question: 'Vocês adaptam um painel já existente?', answer: 'A possibilidade depende do estado, da documentação e do escopo. Envie fotos e dados do painel para uma avaliação inicial.' },
      { question: 'O orçamento de painel sob medida é imediato?', answer: 'Projetos especiais precisam de análise. O prazo da proposta varia conforme a quantidade de cargas e a complexidade funcional.' },
    ],
  },
  montagem: {
    eyebrow: 'Da especificação à validação',
    title: 'Montagem de painéis elétricos industriais',
    intro: 'Montagem de painéis elétricos industriais para comando, acionamento e proteção, com organização dos componentes e verificação funcional antes da entrega.',
    lead: 'Atendemos modelos de catálogo e solicitações especiais, sempre a partir dos dados técnicos fornecidos para a aplicação.',
    benefits: [
      { title: 'Organização interna', text: 'Distribuição dos componentes e identificação voltadas à conferência e à instalação.' },
      { title: 'Componentes industriais', text: 'Seleção compatível com a função, a potência e as condições previstas no escopo.' },
      { title: 'Verificação funcional', text: 'Inspeções e testes previstos antes da preparação do equipamento para envio.' },
    ],
    applications: ['Painéis de partida', 'Painéis com soft starter', 'Painéis com inversor', 'Comando de bombas', 'Revezamento de bombas', 'Painéis para irrigação'],
    steps: [
      { title: 'Análise do escopo', text: 'Conferência dos dados do projeto e dos requisitos de funcionamento.' },
      { title: 'Montagem', text: 'Preparação do gabinete, componentes, conexões, comando e identificação.' },
      { title: 'Conferência e entrega', text: 'Verificação do conjunto e preparação segura para transporte.' },
    ],
    questions: [
      { question: 'Vocês montam painéis a partir de um projeto existente?', answer: 'Sim, o projeto pode ser analisado para verificar escopo, documentação, componentes e condições de fornecimento.' },
      { question: 'Quais tipos de painéis são montados?', answer: 'O catálogo inclui partidas de motores, soft starters, inversores de frequência, bombas, irrigação e revezamento, além de configurações especiais sob análise.' },
      { question: 'A instalação em campo está incluída?', answer: 'As condições de cada fornecimento são apresentadas na proposta. A instalação elétrica deve sempre ser executada por profissionais habilitados.' },
    ],
  },
  sorocaba: {
    eyebrow: 'Atendimento comercial em Sorocaba',
    title: 'Painel de comando em Sorocaba e atendimento para todo o Brasil',
    intro: 'Atendimento em Sorocaba para seleção e orçamento de painéis de comando elétrico, com entrega de produtos para aplicações industriais, bombas e irrigação em todo o Brasil.',
    lead: `Nossa referência de atendimento fica em ${companyConfig.address}. Fale com a equipe para confirmar a solução, o prazo e as condições de entrega para sua cidade.`,
    benefits: [
      { title: 'Atendimento direto', text: 'Contato por telefone, e-mail ou WhatsApp para organizar os dados da aplicação.' },
      { title: 'Compra assistida', text: 'Apoio comercial para comparar potência, tensão, acionamento e opções de catálogo.' },
      { title: 'Entrega nacional', text: companyConfig.deliveryNotice },
    ],
    applications: ['Indústrias de Sorocaba e região', 'Integradores elétricos', 'Agronegócio e irrigação', 'Sistemas de bombeamento', 'Construtoras', 'Manutenção industrial'],
    steps: [
      { title: 'Envie os dados', text: 'Compartilhe a aplicação, potência, tensão e cidade de entrega.' },
      { title: 'Receba a orientação', text: 'A equipe ajuda a localizar um produto ou organizar a cotação especial.' },
      { title: 'Confirme o pedido', text: 'Prazo, pagamento e entrega são apresentados antes do fechamento.' },
    ],
    questions: [
      { question: 'Vocês atendem somente Sorocaba?', answer: `Não. ${companyConfig.deliveryNotice} O atendimento comercial também pode ser realizado por telefone, e-mail e WhatsApp.` },
      { question: 'Posso solicitar orçamento pelo WhatsApp?', answer: `Sim. Envie os dados do equipamento para ${companyConfig.whatsappLabel} e informe potência, tensão, aplicação e cidade.` },
      { question: 'Existe painel pronto para compra?', answer: 'Sim. O catálogo apresenta modelos com características e preços. Projetos que exigem alterações seguem para cotação técnica.' },
    ],
  },
};

const catalogLinks = [
  { title: 'Painel estrela-triângulo', to: '/produtos?linha=estrela-triangulo' },
  { title: 'Painel com soft starter', to: '/produtos?linha=soft-starter' },
  { title: 'Painel com inversor de frequência', to: '/produtos?linha=inversor-de-frequencia' },
  { title: 'Painel para irrigação', to: '/produtos?linha=irrigacao' },
  { title: 'Painel para bomba de incêndio', to: '/produtos?linha=bomba-de-incendio' },
  { title: 'Revezamento de bombas', to: '/produtos?linha=revezamento' },
];

export default function CommercialLandingPage({ kind }: { kind: CommercialLandingKind }) {
  const page = content[kind];
  const whatsappMessage = encodeURIComponent(`Olá! Gostaria de solicitar um orçamento de ${page.title.toLowerCase()}.`);

  return <div className="commercial-landing">
    <section className="commercial-hero">
      <div className="container commercial-hero__grid">
        <div>
          <nav aria-label="Navegação estrutural"><Link to="/">Início</Link><span>/</span><span>{page.eyebrow}</span></nav>
          <span className="eyebrow">{page.eyebrow}</span>
          <h1>{page.title}</h1>
          <p>{page.intro}</p>
          <div className="commercial-hero__actions">
            <ButtonLink to="/orcamento">Solicitar orçamento</ButtonLink>
            <a className="button button--secondary" href={`https://wa.me/${companyConfig.whatsapp}?text=${whatsappMessage}`} target="_blank" rel="noreferrer"><Icon name="whatsapp" size={19}/> Falar no WhatsApp</a>
          </div>
        </div>
        <aside aria-label="Informações de atendimento">
          <strong>Compra técnica assistida</strong>
          <p>{page.lead}</p>
          <ul><li>Atendimento: {companyConfig.hours}</li><li>Garantia informada em cada produto</li><li>{companyConfig.deliveryNotice}</li></ul>
        </aside>
      </div>
    </section>

    <section className="commercial-benefits section" aria-labelledby="commercial-benefits-title">
      <div className="container">
        <header className="commercial-heading"><span>Como podemos ajudar</span><h2 id="commercial-benefits-title">Solução definida a partir da sua aplicação</h2><p>Uma boa especificação começa pelos dados corretos do equipamento e da instalação.</p></header>
        <div className="commercial-card-grid">{page.benefits.map((benefit, index) => <article key={benefit.title}><span>{String(index + 1).padStart(2, '0')}</span><h3>{benefit.title}</h3><p>{benefit.text}</p></article>)}</div>
      </div>
    </section>

    <section className="commercial-applications section" aria-labelledby="commercial-applications-title">
      <div className="container commercial-applications__grid">
        <div><span className="eyebrow">Aplicações atendidas</span><h2 id="commercial-applications-title">Painéis para diferentes equipamentos e processos</h2><p>Use estas aplicações como ponto de partida. A configuração final depende dos dados elétricos e funcionais do projeto.</p></div>
        <ul>{page.applications.map(application => <li key={application}><Icon name="check" size={18}/>{application}</li>)}</ul>
      </div>
    </section>

    <section className="commercial-process section" aria-labelledby="commercial-process-title">
      <div className="container">
        <header className="commercial-heading"><span>Próximos passos</span><h2 id="commercial-process-title">Do primeiro contato à proposta</h2></header>
        <ol>{page.steps.map((step, index) => <li key={step.title}><span>{index + 1}</span><div><h3>{step.title}</h3><p>{step.text}</p></div></li>)}</ol>
      </div>
    </section>

    <section className="commercial-catalog section" aria-labelledby="commercial-catalog-title">
      <div className="container">
        <header className="commercial-heading"><span>Catálogo técnico</span><h2 id="commercial-catalog-title">Compare painéis de comando por aplicação</h2><p>Veja modelos, características, potência, tensão e preço antes de solicitar ajuda.</p></header>
        <div>{catalogLinks.map(item => <Link to={item.to} key={item.to}>{item.title}<span aria-hidden="true">→</span></Link>)}</div>
      </div>
    </section>

    <section className="shop-faq" aria-labelledby="commercial-faq-title"><div className="container shop-faq__inner"><header><span>Dúvidas frequentes</span><h2 id="commercial-faq-title">Antes de solicitar o orçamento</h2><p>Informações para agilizar a seleção e a cotação do painel.</p></header><div className="shop-faq__list">{page.questions.map(item => <details key={item.question}><summary>{item.question}<span aria-hidden="true"/></summary><p>{item.answer}</p></details>)}</div></div></section>

    <section className="commercial-cta"><div className="container"><div><span>Precisa de uma configuração específica?</span><h2>Envie os dados do motor ou da aplicação.</h2><p>Informe potência, tensão, tipo de equipamento, forma de acionamento e cidade de entrega.</p></div><ButtonLink to="/orcamento">Pedir orçamento técnico</ButtonLink></div></section>
  </div>;
}
