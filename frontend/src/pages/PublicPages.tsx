import { FormEvent, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ButtonLink } from '../components/common/ButtonLink';
import { companyConfig } from '../constants/company';

const labels: Record<string, { eyebrow: string; title: string; body: string }> = {
  empresa: { eyebrow: 'Quem somos', title: 'Engenharia organizada em torno da sua necessidade', body: 'Esta área receberá história, estrutura, capacidade técnica, missão, visão, valores e processo de qualidade após validação das informações empresariais.' },
  produtos: { eyebrow: 'Catálogo técnico', title: 'Produtos e soluções', body: 'Consulte famílias de painéis, automação, acionamento e distribuição. O catálogo será conectado à API e não possui preços, carrinho ou pagamento.' },
  servicos: { eyebrow: 'Atuação técnica', title: 'Serviços', body: 'Projetos, montagem, programação, retrofit, instalação, comissionamento, manutenção e assistência técnica.' },
  segmentos: { eyebrow: 'Aplicações', title: 'Segmentos atendidos', body: 'Soluções orientadas às necessidades de indústria, saneamento, irrigação, construção, mineração, alimentos, agronegócio e infraestrutura.' },
  projetos: { eyebrow: 'Experiência aplicada', title: 'Projetos realizados', body: 'Estudos de caso serão publicados somente com informações e imagens autorizadas.' },
  blog: { eyebrow: 'Conteúdo técnico', title: 'Conhecimento para decisões mais seguras', body: 'Artigos sobre painéis, acionamentos, automação, manutenção e boas práticas de projeto.' },
};

export function ListingPage({ kind }: { kind: keyof typeof labels }) {
  const copy = labels[kind];
  return <><PageHero {...copy}/><section className="section"><div className="container empty-state"><span aria-hidden="true">⌁</span><h2>Conteúdo demonstrativo</h2><p>Os registros desta seção virão da API e serão administráveis. A base de rota, carregamento e integração está pronta para a próxima etapa.</p><ButtonLink to="/orcamento">Solicitar uma solução</ButtonLink></div></section></>;
}

export function DetailPage({ kind }: { kind: string }) {
  const { slug } = useParams();
  return <><PageHero eyebrow="Detalhe técnico" title={slug?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') ?? kind} body={`Página individual de ${kind}, preparada para receber conteúdo da API.`}/><section className="section"><div className="container split"><div><h2>Informações da solução</h2><p>Características, aplicações, benefícios, especificações, mídia e relações serão exibidos aqui após o cadastro administrativo.</p></div><div><ButtonLink to={`/orcamento?interesse=${encodeURIComponent(slug ?? kind)}`}>Pedir orçamento</ButtonLink></div></div></section></>;
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

