import { FormEvent, useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { companyConfig } from '../../constants/company';

const categories = [
  ['/produtos/painel-estrela-triangulo', 'Estrela-triângulo'],
  ['/produtos?categoria=soft-starter', 'Soft starter'],
  ['/produtos?categoria=inversor-de-frequencia', 'Inversor de frequência'],
  ['/produtos?categoria=bombas', 'Painéis para bombas'],
  ['/produtos?categoria=automacao', 'Automação industrial'],
];

const institutionalLinks = [
  ['/empresa', 'A empresa'],
  ['/servicos', 'Serviços'],
  ['/projetos', 'Projetos'],
  ['/blog', 'Conteúdo técnico'],
  ['/contato', 'Contato'],
];

export function PublicLayout() {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const navigate = useNavigate();

  const submitSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const term = search.trim();
    navigate(term ? `/produtos?busca=${encodeURIComponent(term)}` : '/produtos');
    setOpen(false);
  };

  return <>
    <div className="commerce-topbar">
      <div className="container commerce-topbar__inner">
        <span>Painéis elétricos, acionamentos e automação industrial</span>
        <nav aria-label="Navegação institucional">
          {institutionalLinks.map(([to, label]) => <NavLink key={to} to={to}>{label}</NavLink>)}
        </nav>
      </div>
    </div>

    <header className="header commerce-header">
      <div className="container commerce-header__main">
        <NavLink to="/" className="commerce-brand" aria-label={`${companyConfig.name}, página inicial`}>
          <img src="/brand/painel-de-comando-logo.png" alt="" />
          <span><strong>Painel de Comando</strong><small>Loja técnica industrial</small></span>
        </NavLink>

        <form className="commerce-search" role="search" onSubmit={submitSearch}>
          <label className="sr-only" htmlFor="store-search">Buscar no catálogo</label>
          <input id="store-search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Qual painel ou componente você procura?" />
          <button type="submit">Buscar</button>
        </form>

        <div className="commerce-actions">
          <NavLink to="/contato"><small>Precisa de ajuda?</small><strong>Atendimento técnico</strong></NavLink>
          <NavLink to="/orcamento"><small>Projeto especial</small><strong>Solicitar orçamento</strong></NavLink>
          <NavLink className="commerce-cart" to="/carrinho" aria-label="Carrinho com zero itens"><span aria-hidden="true">0</span><strong>Carrinho</strong></NavLink>
          <div className="commerce-owner"><small>Uma empresa</small><img src="/brand/mr-drives-logo.png" alt="MR Drives"/></div>
        </div>

        <button className="menu-button" type="button" aria-expanded={open} aria-controls="store-menu" onClick={() => setOpen(!open)}>Menu</button>
      </div>

      <nav id="store-menu" className={open ? 'category-nav category-nav--open' : 'category-nav'} aria-label="Categorias de produtos">
        <div className="container category-nav__inner">
          {categories.map(([to, label]) => <NavLink end key={to} to={to} onClick={() => setOpen(false)}>{label}</NavLink>)}
          <NavLink end className="category-nav__all" to="/produtos" onClick={() => setOpen(false)}>Todos os produtos <span aria-hidden="true">→</span></NavLink>
        </div>
      </nav>
    </header>

    <main id="conteudo"><Outlet /></main>

    <footer className="footer"><div className="container footer__grid"><div className="footer__brand"><div><img src="/brand/painel-de-comando-logo.png" alt=""/><span><strong>Painel de Comando</strong><small>Loja técnica industrial</small></span></div><p>Painéis elétricos, acionamentos e soluções de automação para aplicações industriais.</p><div className="footer__owner"><span>Uma empresa</span><img src="/brand/mr-drives-logo.png" alt="MR Drives"/></div></div><div><strong>Navegue</strong><p><NavLink to="/produtos">Produtos</NavLink><br/><NavLink to="/empresa">A empresa</NavLink><br/><NavLink to="/contato">Contato</NavLink></p></div><div><strong>Atendimento</strong><p>{companyConfig.phone}<br/>{companyConfig.email}<br/>{companyConfig.address}</p></div></div><div className="container footer__bottom"><span>© {new Date().getFullYear()} {companyConfig.name} · MR Drives</span><span><NavLink to="/politica-de-privacidade">Privacidade</NavLink> · <NavLink to="/termos-de-uso">Termos</NavLink> · <NavLink to="/admin/login">Área administrativa</NavLink></span></div></footer>
  </>;
}
