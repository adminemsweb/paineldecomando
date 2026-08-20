import { cleanup, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { ListingPage } from './PublicPages';

afterEach(() => {
  cleanup();
  vi.restoreAllMocks();
});

describe('catálogo de produtos', () => {
  it('filtra a linha selecionada e aponta os cards para o produto', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({
      data: [
        { id: 3, name: 'Painel Estrela Triângulo 15CV', slug: 'painel-estrela-triangulo', category_name: 'Painéis de partida', image_url: '/estrela.png', price_cents: 124700, stock_status: 'in_stock', stock_quantity: 5 },
        { id: 4, name: 'Painel com Soft Starter', slug: 'painel-com-soft-starter', category_name: 'Soft Starter', image_url: '/soft.png', stock_status: 'on_demand' },
      ],
    }), { status: 200, headers: { 'Content-Type': 'application/json' } }));

    render(<MemoryRouter initialEntries={['/produtos?linha=estrela-triangulo']}><ListingPage kind="produtos"/></MemoryRouter>);

    expect(screen.getByRole('heading', { level: 1, name: 'Painéis Estrela-Triângulo' })).toBeInTheDocument();
    await waitFor(() => expect(screen.getByRole('heading', { name: 'Painel Estrela Triângulo 15CV' })).toBeInTheDocument());
    expect(screen.queryByRole('heading', { name: 'Painel com Soft Starter' })).not.toBeInTheDocument();
    expect(screen.getAllByRole('link', { name: /Painel Estrela Triângulo 15CV/i })[0]).toHaveAttribute('href', '/produtos/painel-estrela-triangulo');
    expect(screen.getByText('1 produto encontrado')).toBeInTheDocument();
  });
});
