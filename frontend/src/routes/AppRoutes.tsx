import { lazy, Suspense } from 'react';
import { Navigate, Route, Routes } from 'react-router-dom';
import { AdminLayout } from '../components/layout/AdminLayout';
import { PublicLayout } from '../components/layout/PublicLayout';
import { AccountPage, BuyerPolicyPage, CartPage, CompanyPage, ContactPage, DetailPage, ForgotPasswordPage, LegalPage, ListingPage, NotFoundPage, QuotePage, ResetPasswordPage } from '../pages/PublicPages';
import { AdminCategoriesPage, AdminDashboardPage, AdminLoginPage, AdminPlaceholderPage, AdminProductsPage } from '../pages/AdminPages';
import CommercialLandingPage from '../pages/CommercialLandingPage';

const HomePage = lazy(() => import('../pages/HomePage'));
const sections = ['Produtos', 'Categorias', 'Segmentos', 'Serviços', 'Projetos', 'Posts', 'Leads', 'Configurações'];

export function AppRoutes() {
  return <Suspense fallback={<div className="page-loader" role="status">Carregando…</div>}><Routes>
    <Route element={<PublicLayout/>}>
      <Route index element={<HomePage/>}/><Route path="empresa" element={<CompanyPage/>}/>
      <Route path="painel-de-comando-eletrico" element={<CommercialLandingPage kind="comando"/>}/>
      <Route path="paineis-eletricos-sob-medida" element={<CommercialLandingPage kind="sob-medida"/>}/>
      <Route path="montagem-de-paineis-eletricos-industriais" element={<CommercialLandingPage kind="montagem"/>}/>
      <Route path="painel-de-comando-sorocaba" element={<CommercialLandingPage kind="sorocaba"/>}/>
      <Route path="produtos" element={<ListingPage kind="produtos"/>}/><Route path="produtos/:slug" element={<DetailPage kind="produto"/>}/>
      <Route path="servicos" element={<ListingPage kind="servicos"/>}/><Route path="servicos/:slug" element={<DetailPage kind="serviço"/>}/>
      <Route path="segmentos" element={<ListingPage kind="segmentos"/>}/><Route path="segmentos/:slug" element={<DetailPage kind="segmento"/>}/>
      <Route path="projetos" element={<ListingPage kind="projetos"/>}/><Route path="projetos/:slug" element={<DetailPage kind="projeto"/>}/>
      <Route path="blog" element={<ListingPage kind="blog"/>}/><Route path="blog/:slug" element={<DetailPage kind="artigo"/>}/>
      <Route path="orcamento" element={<QuotePage/>}/><Route path="contato" element={<ContactPage/>}/>
      <Route path="carrinho" element={<CartPage/>}/>
      <Route path="conta" element={<AccountPage/>}/>
      <Route path="conta/recuperar-senha" element={<ForgotPasswordPage/>}/>
      <Route path="conta/redefinir-senha" element={<ResetPasswordPage/>}/>
      <Route path="trocas-e-devolucoes" element={<BuyerPolicyPage kind="returns"/>}/><Route path="garantia" element={<BuyerPolicyPage kind="warranty"/>}/>
      <Route path="politica-de-privacidade" element={<LegalPage/>}/><Route path="termos-de-uso" element={<LegalPage terms/>}/><Route path="*" element={<NotFoundPage/>}/>
    </Route>
    <Route path="admin">
      <Route index element={<Navigate to="/admin/login" replace/>}/>
      <Route path="login" element={<AdminLoginPage/>}/>
      <Route element={<AdminLayout/>}>
        <Route path="dashboard" element={<AdminDashboardPage/>}/>
        <Route path="produtos" element={<AdminProductsPage/>}/>
        <Route path="categorias" element={<AdminCategoriesPage/>}/>
        {sections.filter(section => !['Produtos','Categorias'].includes(section)).map(section => <Route key={section} path={section.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '')} element={<AdminPlaceholderPage section={section}/>}/>)}
      </Route>
    </Route>
    <Route path="/solucoes" element={<Navigate to="/produtos" replace/>}/>
  </Routes></Suspense>;
}
