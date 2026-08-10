import { FormEvent, useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { companyConfig } from '../../constants/company';

const navigation = [
  ['/', 'Início'],
  ['/produtos', 'Comprar painéis'],
  ['/segmentos', 'Aplicações'],
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
    <header className="header commerce-header">
      <div className="container commerce-header__main">
        <NavLink to="/" className="commerce-brand" aria-label={`${companyConfig.name}, página inicial`}>
          <span className="commerce-brand__mark"><img src="/images/logopc.png" alt="" /></span>
          <span className="commerce-brand__name"><strong>Painel de Comando</strong><small>E-commerce industrial</small></span>
        </NavLink>

        <form className="commerce-search" role="search" onSubmit={submitSearch}>
          <label className="sr-only" htmlFor="store-search">Buscar no catálogo</label>
          <input id="store-search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Qual painel ou componente você procura?" />
          <button type="submit" aria-label="Buscar"><span aria-hidden="true">⌕</span></button>
        </form>

        <div className="commerce-actions">
          <NavLink to="/contato"><small>Precisa de ajuda?</small><strong>Atendimento técnico</strong></NavLink>
          <NavLink to="/orcamento"><small>Projeto especial</small><strong>Solicitar orçamento</strong></NavLink>
          <NavLink className="commerce-cart" to="/carrinho" aria-label="Carrinho com zero itens"><span aria-hidden="true">0</span><strong>Carrinho</strong></NavLink>
          <div className="commerce-owner"><small>Parceiro técnico</small><img src="/brand/mr-drives-logo.png" alt="MR Drives"/></div>
        </div>

        <button className="menu-button" type="button" aria-expanded={open} aria-controls="store-menu" onClick={() => setOpen(!open)}>Menu</button>
      </div>

      <nav id="store-menu" className={open ? 'category-nav category-nav--open' : 'category-nav'} aria-label="Navegação principal">
        <div className="container category-nav__inner">
          {navigation.map(([to, label]) => <NavLink end key={to} to={to} onClick={() => setOpen(false)} className={({ isActive }) => `${to === '/produtos' ? 'category-nav__products' : ''}${isActive ? ' active' : ''}`.trim()}>{label}{to === '/produtos' && <span aria-hidden="true">⌄</span>}</NavLink>)}
        </div>
      </nav>
    </header>

    <main id="conteudo"><Outlet /></main>

    <footer className="footer"><div className="container footer__grid"><div className="footer__brand"><div><span className="footer__brand-mark"><img src="/images/logopc.png" alt=""/></span><span><strong>Painel de Comando</strong><small>E-commerce industrial</small></span></div><p>Compra de painéis elétricos, acionamentos, componentes e soluções de automação para aplicações industriais.</p><div className="footer__owner"><span>Em parceria com</span><img src="/brand/mr-drives-logo.png" alt="MR Drives"/></div></div><div><strong>Compre online</strong><p><NavLink to="/produtos">Todos os produtos</NavLink><br/><NavLink to="/segmentos">Aplicações</NavLink><br/><NavLink to="/carrinho">Meu carrinho</NavLink></p></div><div><strong>Atendimento</strong><p>{companyConfig.phone}<br/>{companyConfig.email}<br/>{companyConfig.address}</p></div></div><div className="container footer__bottom"><span>© {new Date().getFullYear()} {companyConfig.name} · E-commerce em parceria com a MR Drives</span><span><NavLink to="/politica-de-privacidade">Privacidade</NavLink> · <NavLink to="/termos-de-uso">Termos</NavLink> · <NavLink to="/admin/login">Área administrativa</NavLink></span></div></footer>
  </>;
}
