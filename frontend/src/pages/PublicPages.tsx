import { FormEvent, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { companyConfig } from '../constants/company';

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
  if (kind === 'produtos') return <StorefrontListing copy={copy}/>;
  return <><PageHero {...copy}/><section className="section"><div className="container empty-state"><span aria-hidden="true">⌁</span><h2>Conteúdo demonstrativo</h2><p>Os registros desta seção virão da API e serão administráveis. A base de rota, carregamento e integração está pronta para a próxima etapa.</p><ButtonLink to="/orcamento">Solicitar uma solução</ButtonLink></div></section></>;
}

export function DetailPage({ kind }: { kind: string }) {
  const { slug } = useParams();
  if (kind === 'produto' && slug === 'painel-estrela-triangulo') return <StarDeltaProduct/>;
  return <><PageHero eyebrow="Detalhe técnico" title={slug?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') ?? kind} body={`Página individual de ${kind}, preparada para receber conteúdo da API.`}/><section className="section"><div className="container split"><div><h2>Informações da solução</h2><p>Características, aplicações, benefícios, especificações, mídia e relações serão exibidos aqui após o cadastro administrativo.</p></div><div><ButtonLink to={`/orcamento?interesse=${encodeURIComponent(slug ?? kind)}`}>Pedir orçamento</ButtonLink></div></div></section></>;
}

function StorefrontListing({ copy }: { copy: { eyebrow: string; title: string; body: string } }) {
  return <><PageHero {...copy}/><section className="store-catalog"><div className="container store-catalog__layout">
    <aside className="store-filters"><span>Filtrar por</span><h2>Encontre a configuração certa</h2><label>Aplicação<select defaultValue=""><option value="">Todas</option><option>Bombas</option><option>Compressores</option><option>Ventiladores</option><option>Máquinas industriais</option></select></label><label>Tipo de acionamento<select defaultValue=""><option value="">Todos</option><option>Estrela-triângulo</option><option>Soft starter</option><option>Inversor de frequência</option></select></label><p>Precisa dimensionar tensão, potência ou proteção?</p><ButtonLink to="/orcamento" variant="secondary">Falar com a engenharia</ButtonLink></aside>
    <div className="store-results"><div className="store-results__heading"><div><span>Catálogo em expansão</span><strong>1 solução publicada</strong></div><select aria-label="Ordenar produtos" defaultValue="relevancia"><option value="relevancia">Mais relevantes</option><option value="nome">Nome</option></select></div>
      <article className="store-product-card"><Link className="store-product-card__image" to="/produtos/painel-estrela-triangulo"><img src="/images/hero-painel-comando-poster.jpg" alt="Gabinete de painel elétrico industrial"/><span>Projeto configurável</span></Link><div className="store-product-card__body"><small>Painéis de partida</small><h2><Link to="/produtos/painel-estrela-triangulo">Painel Estrela-Triângulo</Link></h2><p>Acionamento de motores elétricos com redução da corrente de partida e proteção da carga.</p><ul><li>Bombas e compressores</li><li>Proteção contra sobrecarga</li><li>Configuração conforme a aplicação</li></ul><div><span><small>Preço</small><strong>Sob consulta</strong></span><ButtonLink to="/produtos/painel-estrela-triangulo">Ver produto</ButtonLink></div></div></article>
    </div>
  </div></section></>;
}

function StarDeltaProduct() {
  const applications = ['Bombas industriais', 'Compressores', 'Ventiladores', 'Máquinas de médio e grande porte'];
  const benefits = ['Redução da corrente de partida', 'Menor impacto na rede elétrica', 'Maior vida útil do motor', 'Proteção contra sobrecarga', 'Operação segura e confiável'];
  return <>
    <div className="product-breadcrumb"><div className="container"><Link to="/">Início</Link><span>/</span><Link to="/produtos">Painéis de partida</Link><span>/</span><strong>Painel Estrela-Triângulo</strong></div></div>
    <section className="product-detail"><div className="container product-detail__grid">
      <div className="product-gallery"><div className="product-gallery__stage"><img src="/images/hero-painel-comando-poster.jpg" alt="Painel Estrela-Triângulo em gabinete industrial fechado"/></div><div className="product-gallery__note"><span>Imagem de referência</span><p>A disposição e os componentes variam conforme o dimensionamento do projeto.</p></div></div>
      <div className="product-summary"><span className="product-summary__category">Painéis de partida</span><h1>Painel Estrela-Triângulo</h1><p className="product-summary__lead">Partida de motores elétricos com corrente reduzida, proteção adequada e menor impacto na rede.</p><div className="product-summary__status"><span>Configuração sob medida</span><strong>Preço sob consulta</strong><small>O valor depende de potência, tensão, proteções e opcionais.</small></div><div className="product-specs"><div><small>Tensão</small><strong>Conforme o projeto</strong></div><div><small>Potência</small><strong>Dimensionada para o motor</strong></div><div><small>Acionamento</small><strong>Estrela-triângulo</strong></div><div><small>Aplicação</small><strong>Uso industrial</strong></div></div><div className="product-summary__actions"><ButtonLink to="/orcamento?interesse=painel-estrela-triangulo">Solicitar cotação</ButtonLink><ButtonLink to="/contato" variant="secondary">Tirar dúvida técnica</ButtonLink></div><p className="product-summary__assurance">Antes da fabricação, a equipe confirma os dados do motor e as condições da instalação.</p></div>
    </div></section>
    <section className="product-information"><div className="container product-information__grid"><div><span className="eyebrow">Sobre a solução</span><h2>Partida controlada para preservar motor e instalação.</h2><p>O painel realiza a partida do motor inicialmente em estrela e, após o tempo configurado, faz a transição para triângulo. Essa estratégia reduz a corrente durante a partida e contribui para uma operação mais estável.</p></div><div><article><h3>Aplicações</h3><ul>{applications.map(item => <li key={item}>{item}</li>)}</ul></article><article><h3>Vantagens</h3><ul>{benefits.map(item => <li key={item}>{item}</li>)}</ul></article></div></div></section>
  </>;
}

export function CartPage() {
  return <section className="cart-page"><div className="container"><div className="cart-page__heading"><span className="eyebrow">Sua seleção</span><h1>Carrinho</h1></div><div className="cart-empty"><span aria-hidden="true">00</span><div><h2>Seu carrinho está vazio.</h2><p>Os produtos padronizados aparecerão aqui quando preços, estoque e condições comerciais forem cadastrados. Para um painel sob medida, inicie uma cotação técnica.</p><div className="button-row"><ButtonLink to="/produtos">Explorar produtos</ButtonLink><ButtonLink to="/orcamento" variant="secondary">Solicitar cotação</ButtonLink></div></div></div></div></section>;
}

export function CompanyPage() { return <><PageHero {...labels.empresa}/><section className="section"><div className="container cards"><article className="card"><h2>Missão</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article><article className="card"><h2>Visão</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article><article className="card"><h2>Valores</h2><p>TEXTO INSTITUCIONAL A VALIDAR.</p></article></div></section></>; }

export function ContactPage() { return <><PageHero eyebrow="Contato" title="Converse com nossa equipe" body="Envie sua dúvida ou solicitação. Para projetos, use o formulário de orçamento técnico."/><section className="section"><div className="container split"><div><h2>Canais de atendimento</h2><p>{companyConfig.phone}<br/>{companyConfig.email}<br/>{companyConfig.address}<br/>{companyConfig.hours}</p></div><SimpleForm type="contact"/></div></section></>; }

export function QuotePage() { return <><PageHero eyebrow="Orçamento técnico" title="Conte sua necessidade" body="Quanto mais contexto você fornecer, mais objetiva poderá ser a análise inicial."/><section className="section"><div className="container form-wrap"><SimpleForm type="quote"/></div></section></>; }

function SimpleForm({ type }: { type: 'contact' | 'quote' }) {
  const [sent, setSent] = useState(false);
  const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); setSent(true); };
  if (sent) return <div className="notice" role="status"><strong>Dados validados no front-end.</strong><p>O envio definitivo será conectado ao endpoint da API na etapa pública. Nenhum dado foi armazenado nesta demonstração.</p></div>;
  return <form className="form" onSubmit={submit}><label>Nome<input name="name" required autoComplete="name"/></label><label>Empresa<input name="company" autoComplete="organization"/></label><label>E-mail<input name="email" type="email" required autoComplete="email"/></label><label>Telefone<input name="phone" required autoComplete="tel"/></label>{type === 'quote' && <><label>Produto ou solução<input name="interest"/></label><label>Descrição da necessidade<textarea name="message" required rows={6}/></label><label className="checkbox"><input name="consent" type="checkbox" required/> Li e concordo com a Política de Privacidade.</label></>}{type === 'contact' && <label>Mensagem<textarea name="message" required rows={5}/></label>}<button className="button button--primary" type="submit">Enviar solicitação</button></form>;
}

export function LegalPage({ terms = false }: { terms?: boolean }) { return <><PageHero eyebrow="Informações legais" title={terms ? 'Termos de uso' : 'Política de privacidade'} body="TEXTO PROVISÓRIO — revisão jurídica obrigatória antes da publicação."/><section className="section"><article className="container legal"><h2>{terms ? 'Uso deste site' : 'Tratamento de dados'}</h2><p>Este documento é um modelo inicial e deverá ser adaptado à operação real, aos fornecedores, às bases legais, aos prazos de retenção e aos canais do titular.</p><h2>Contato</h2><p>Para solicitações relacionadas a dados pessoais, utilize o canal {companyConfig.email} após sua confirmação.</p></article></section></>; }

export function NotFoundPage() { return <section className="section not-found"><div className="container"><span className="eyebrow">Erro 404</span><h1>Esta página saiu do circuito.</h1><p>O endereço pode ter mudado. Volte ao início ou consulte nossas soluções.</p><div className="button-row"><ButtonLink to="/">Voltar ao início</ButtonLink><ButtonLink to="/produtos" variant="secondary">Ver soluções</ButtonLink></div></div></section>; }

export function LoginPage() { return <section className="login"><form className="form login__card" onSubmit={(e) => e.preventDefault()}><Link to="/" className="brand"><span>{companyConfig.shortName}</span>{companyConfig.name}</Link><h1>Acesso administrativo</h1><p>Autenticação será conectada à API protegida.</p><label>E-mail<input type="email" required autoComplete="username"/></label><label>Senha<input type="password" required autoComplete="current-password"/></label><button className="button button--primary" type="submit">Entrar</button><Link to="/">Voltar ao site</Link></form></section>; }

export function AdminPage({ section = 'Dashboard' }: { section?: string }) { return <><div className="admin-heading"><div><span className="eyebrow">Administração</span><h1>{section}</h1></div>{section !== 'Dashboard' && <button className="button button--primary" type="button">Adicionar registro</button>}</div><div className="admin-cards"><article><span>Conteúdo</span><strong>—</strong><small>Aguardando conexão com API</small></article><article><span>Status</span><strong>Base pronta</strong><small>Dados demonstrativos</small></article></div><section className="admin-panel"><h2>{section === 'Dashboard' ? 'Atividades recentes' : `Gerenciamento de ${section.toLowerCase()}`}</h2><p>O layout administrativo, navegação e rota estão funcionais. Autenticação, tabelas e CRUDs pertencem à Etapa 4.</p></section></>; }

function PageHero({ eyebrow, title, body }: { eyebrow: string; title: string; body: string }) { return <section className="page-hero"><div className="container"><span className="eyebrow eyebrow--light">{eyebrow}</span><h1>{title}</h1><p>{body}</p></div></section>; }
