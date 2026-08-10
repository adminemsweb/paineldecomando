import { useState } from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import { companyConfig } from '../../constants/company';
import { ButtonLink } from '../common/ButtonLink';

const links = [
  ['/produtos', 'Soluções'], ['/servicos', 'Serviços'], ['/segmentos', 'Segmentos'],
  ['/projetos', 'Projetos'], ['/empresa', 'Empresa'], ['/blog', 'Conteúdo'],
];

export function PublicLayout() {
  const [open, setOpen] = useState(false);
  return <>
    <div className="topbar"><div className="container topbar__inner"><span>Engenharia aplicada</span><span>Projetos sob medida</span><span>Documentação técnica</span></div></div>
    <header className="header">
      <div className="container header__inner">
        <NavLink to="/" className="brand" aria-label={`${companyConfig.name}, página inicial`}><img src="/brand/painel-de-comando-logo.png" alt=""/><strong className="brand__name">{companyConfig.name}</strong></NavLink>
        <button className="menu-button" type="button" aria-expanded={open} aria-controls="main-menu" onClick={() => setOpen(!open)}>Menu</button>
        <nav id="main-menu" className={open ? 'nav nav--open' : 'nav'} aria-label="Navegação principal">
          {links.map(([to, label]) => <NavLink key={to} to={to} onClick={() => setOpen(false)} className={({ isActive }) => isActive ? 'active' : ''}>{label}</NavLink>)}
        </nav>
        <div className="header__cta"><ButtonLink to="/orcamento">Iniciar projeto</ButtonLink></div>
      </div>
    </header>
    <main id="conteudo"><Outlet /></main>
    <footer className="footer"><div className="container footer__grid"><div><strong>{companyConfig.name}</strong><p>Engenharia e soluções elétricas personalizadas para aplicações industriais.</p></div><div><strong>Navegue</strong><p><NavLink to="/produtos">Soluções</NavLink><br/><NavLink to="/empresa">Empresa</NavLink><br/><NavLink to="/contato">Contato</NavLink></p></div><div><strong>Contato</strong><p>{companyConfig.phone}<br/>{companyConfig.email}<br/>{companyConfig.address}</p></div></div><div className="container footer__bottom"><span>© {new Date().getFullYear()} {companyConfig.name}</span><span><NavLink to="/politica-de-privacidade">Privacidade</NavLink> · <NavLink to="/termos-de-uso">Termos</NavLink> · <NavLink to="/admin/login">Área administrativa</NavLink></span></div></footer>
  </>;
}
