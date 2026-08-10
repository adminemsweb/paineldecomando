import { lazy, Suspense } from 'react';
import { Navigate, Route, Routes } from 'react-router-dom';
import { AdminLayout } from '../components/layout/AdminLayout';
import { PublicLayout } from '../components/layout/PublicLayout';
import { AdminPage, CartPage, CompanyPage, ContactPage, DetailPage, LegalPage, ListingPage, LoginPage, NotFoundPage, QuotePage } from '../pages/PublicPages';

const HomePage = lazy(() => import('../pages/HomePage'));
const sections = ['Produtos', 'Categorias', 'Segmentos', 'Serviços', 'Projetos', 'Posts', 'Leads', 'Configurações'];

export function AppRoutes() {
  return <Suspense fallback={<div className="page-loader" role="status">Carregando…</div>}><Routes>
    <Route element={<PublicLayout/>}>
      <Route index element={<HomePage/>}/><Route path="empresa" element={<CompanyPage/>}/>
      <Route path="produtos" element={<ListingPage kind="produtos"/>}/><Route path="produtos/:slug" element={<DetailPage kind="produto"/>}/>
      <Route path="servicos" element={<ListingPage kind="servicos"/>}/><Route path="servicos/:slug" element={<DetailPage kind="serviço"/>}/>
      <Route path="segmentos" element={<ListingPage kind="segmentos"/>}/><Route path="segmentos/:slug" element={<DetailPage kind="segmento"/>}/>
      <Route path="projetos" element={<ListingPage kind="projetos"/>}/><Route path="projetos/:slug" element={<DetailPage kind="projeto"/>}/>
      <Route path="blog" element={<ListingPage kind="blog"/>}/><Route path="blog/:slug" element={<DetailPage kind="artigo"/>}/>
      <Route path="orcamento" element={<QuotePage/>}/><Route path="contato" element={<ContactPage/>}/>
      <Route path="carrinho" element={<CartPage/>}/>
      <Route path="politica-de-privacidade" element={<LegalPage/>}/><Route path="termos-de-uso" element={<LegalPage terms/>}/><Route path="*" element={<NotFoundPage/>}/>
    </Route>
    <Route path="admin/login" element={<LoginPage/>}/>
    <Route path="admin" element={<AdminLayout/>}><Route index element={<AdminPage/>}/>{sections.map(section => <Route key={section} path={section.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '')} element={<AdminPage section={section}/>}/>)}</Route>
    <Route path="/solucoes" element={<Navigate to="/produtos" replace/>}/>
  </Routes></Suspense>;
}
