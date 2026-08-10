import { NavLink, Outlet } from 'react-router-dom';
import { companyConfig } from '../../constants/company';

const adminLinks = [['/admin', 'Dashboard'], ['/admin/produtos', 'Produtos'], ['/admin/categorias', 'Categorias'], ['/admin/segmentos', 'Segmentos'], ['/admin/servicos', 'Serviços'], ['/admin/projetos', 'Projetos'], ['/admin/posts', 'Posts'], ['/admin/leads', 'Leads'], ['/admin/configuracoes', 'Configurações']];

export function AdminLayout() {
  return <div className="admin-shell"><aside className="admin-sidebar"><strong>{companyConfig.shortName} Admin</strong><nav aria-label="Administração">{adminLinks.map(([to, label]) => <NavLink key={to} to={to} end={to === '/admin'}>{label}</NavLink>)}</nav><NavLink to="/">Ver site</NavLink></aside><main id="conteudo" className="admin-content"><Outlet /></main></div>;
}
