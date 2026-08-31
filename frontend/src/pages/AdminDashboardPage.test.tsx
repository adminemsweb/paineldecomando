import { cleanup, render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AdminDashboardPage } from './AdminPages';

afterEach(() => {
  cleanup();
  vi.restoreAllMocks();
});

describe('dashboard administrativo', () => {
  it('apresenta as métricas em linguagem clara quando ainda há poucos dados', async () => {
    vi.spyOn(globalThis, 'fetch').mockImplementation(async input => {
      const url = String(input);
      const data = url.includes('/admin/analytics') ? {
        period_days: 30,
        summary: { visitors:2, page_views:4, product_views:2, product_clicks:1, searches:1, conversions:1, whatsapp_clicks:1, quote_clicks:0, conversion_rate:50 },
        daily: [{ date:new Date().toISOString().slice(0, 10), page_views:4, product_views:2, conversions:1 }],
        products: [],
        searches: [{ term:'painel para bomba', searches:1, without_results:0 }],
        pages: [{ path:'/', views:4 }],
        devices: [{ device:'desktop', visitors:2 }],
      } : [];
      return new Response(JSON.stringify({ data }), { status:200, headers:{ 'Content-Type':'application/json' } });
    });

    render(<MemoryRouter><AdminDashboardPage/></MemoryRouter>);

    expect(await screen.findByText('Primeiros dados')).toBeInTheDocument();
    expect(screen.getByText('pessoas que acessaram')).toBeInTheDocument();
    expect(screen.getByText('Do acesso ao contato')).toBeInTheDocument();
    expect(screen.queryByRole('img', { name:'Gráfico de acessos e painéis visualizados por dia' })).not.toBeInTheDocument();
    expect(screen.getByText('painel para bomba')).toBeInTheDocument();
  });
});
