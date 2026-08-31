import { cleanup, render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AdminDashboardPage } from './AdminPages';

afterEach(() => {
  cleanup();
  vi.restoreAllMocks();
});

describe('dashboard administrativo', () => {
  it('apresenta as métricas em linguagem clara e renderiza a série temporal', async () => {
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

    expect(await screen.findByText('Resumo do período')).toBeInTheDocument();
    expect(screen.getByText('pessoas diferentes')).toBeInTheDocument();
    expect(screen.getByText('O que os números mostram')).toBeInTheDocument();
    expect(screen.getByRole('img', { name:'Gráfico de acessos e painéis visualizados por dia' })).toBeInTheDocument();
    expect(screen.getByText('painel para bomba')).toBeInTheDocument();
  });
});
