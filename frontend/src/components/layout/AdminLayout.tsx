import { useEffect, useState } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { companyConfig } from '../../constants/company';
import { apiRequest } from '../../services/api';
import type { AdminUser } from '../../pages/AdminPages';

const adminLinks = [['/admin', 'Visão geral'], ['/admin/produtos', 'Produtos'], ['/admin/categorias', 'Categorias'], ['/admin/segmentos', 'Segmentos'], ['/admin/servicos', 'Serviços'], ['/admin/projetos', 'Projetos'], ['/admin/posts', 'Posts'], ['/admin/leads', 'Leads'], ['/admin/configuracoes', 'Configurações']];

export function AdminLayout() {
  const navigate = useNavigate();
  const [user, setUser] = useState<AdminUser | null>(null);
  const [checking, setChecking] = useState(true);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    apiRequest<AdminUser>('/admin/auth/me').then(response => setUser(response.data)).catch(() => navigate('/admin/login', { replace: true })).finally(() => setChecking(false));
  }, [navigate]);

  async function logout() {
    await apiRequest<null>('/admin/auth/logout', { method: 'POST' }).catch(() => undefined);
    navigate('/admin/login', { replace: true });
  }

  if (checking) return <div className="admin-auth-loading" role="status">Verificando acesso…</div>;
  if (!user) return null;
  return <div className="admin-shell">
    <aside className={`admin-sidebar${menuOpen ? ' admin-sidebar--open' : ''}`}>
      <div className="admin-sidebar__brand"><span>PC</span><div><strong>{companyConfig.shortName}</strong><small>Painel administrativo</small></div><button type="button" onClick={() => setMenuOpen(false)} aria-label="Fechar menu">×</button></div>
      <nav aria-label="Administração">{adminLinks.map(([to, label]) => <NavLink key={to} to={to} end={to === '/admin'} onClick={() => setMenuOpen(false)}>{label}</NavLink>)}</nav>
      <div className="admin-sidebar__footer"><div><span>{user.name.charAt(0).toUpperCase()}</span><p><strong>{user.name}</strong><small>{user.email}</small></p></div><Link to="/">Ver site</Link><button type="button" onClick={logout}>Sair</button></div>
    </aside>
    <div className="admin-main"><header className="admin-mobile-header"><button type="button" onClick={() => setMenuOpen(true)} aria-label="Abrir menu">☰</button><strong>{companyConfig.shortName} Admin</strong><Link to="/">Ver site</Link></header><main id="conteudo" className="admin-content"><Outlet context={{ user }}/></main></div>
    {menuOpen && <button className="admin-menu-scrim" type="button" onClick={() => setMenuOpen(false)} aria-label="Fechar menu"/>}
  </div>;
}
