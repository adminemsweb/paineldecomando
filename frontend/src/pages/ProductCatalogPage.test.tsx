import { cleanup, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { DetailPage, ListingPage } from './PublicPages';

afterEach(() => {
  cleanup();
  vi.restoreAllMocks();
});

describe('catálogo de produtos', () => {
  it('filtra a linha selecionada e aponta os cards para o produto', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({
      data: [
        { id: 3, name: 'Painel Estrela Triângulo 15CV', slug: 'painel-estrela-triangulo', category_name: 'Painéis de partida', image_url: '/estrela.png', price_cents: 124700, installments: 3, stock_status: 'in_stock', stock_quantity: 5 },
        { id: 4, name: 'Painel com Soft Starter', slug: 'painel-com-soft-starter', category_name: 'Soft Starter', image_url: '/soft.png', stock_status: 'on_demand' },
      ],
    }), { status: 200, headers: { 'Content-Type': 'application/json' } }));

    render(<MemoryRouter initialEntries={['/produtos?linha=estrela-triangulo']}><ListingPage kind="produtos"/></MemoryRouter>);

    expect(screen.getByRole('heading', { level: 1, name: 'Painéis Estrela-Triângulo' })).toBeInTheDocument();
    await waitFor(() => expect(screen.getByRole('heading', { name: 'Painel Estrela Triângulo 15CV' })).toBeInTheDocument());
    expect(screen.queryByRole('heading', { name: 'Painel com Soft Starter' })).not.toBeInTheDocument();
    expect(screen.getAllByRole('link', { name: /Painel Estrela Triângulo 15CV/i })[0]).toHaveAttribute('href', '/produtos/painel-estrela-triangulo');
    expect(screen.getByText('1 produto encontrado')).toBeInTheDocument();
    expect(screen.getByText('ou 3x de R$ 415,67 sem juros')).toBeInTheDocument();
  });

  it('usa o layout completo de produto nos modelos Estrela-Triângulo', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({
      data: {
        id: 4,
        name: 'Painel Estrela Triângulo 15CV 380V Man/Aut. Eco',
        slug: 'painel-estrela-triangulo-15cv-380v',
        brand: 'Painel de Comando',
        category_name: 'Painéis de partida',
        reference_code: 'PAINEL-E.T-15CV+380-MAN-AUT.ECO',
        model: 'Painel Estrela Triângulo',
        image_url: '/estrela.png',
        price_cents: 115400,
        installments: 3,
        stock_status: 'in_stock',
        stock_quantity: 5,
        lead_time: 'Disponível em 3 dias úteis',
        warranty_days: 365,
      },
    }), { status: 200, headers: { 'Content-Type': 'application/json' } }));

    render(<MemoryRouter initialEntries={['/produtos/painel-estrela-triangulo-15cv-380v']}><Routes><Route path="/produtos/:slug" element={<DetailPage kind="produto"/>}/></Routes></MemoryRouter>);

    await waitFor(() => expect(screen.getByRole('heading', { level: 1, name: 'Painel Estrela Triângulo 15CV 380V Man/Aut. Eco | Painel de Comando' })).toBeInTheDocument());
    expect(screen.getByText('R$ 1.154,00')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Descrição geral' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Diminuir quantidade' })).toBeInTheDocument();
  });

  it('exibe o painel de bomba de incêndio com preço, categoria e vídeo próprios', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({
      data: {
        id: 9,
        name: 'Painel Estrela Triângulo para Bomba de Incêndio 10CV 220V',
        slug: 'painel-estrela-triangulo-bomba-incendio-10cv-220v',
        brand: 'Painel de Comando',
        category_name: 'Bomba de Incêndio',
        reference_code: 'PAINEL-10CV-220V-CAIXA-VERMELHA',
        model: 'Painel Estrela Triângulo',
        image_url: '/images/painel-bomba-incendio-10cv-220v.png',
        video_urls: ['/videos/painelincendio.mp4'],
        price_cents: 215000,
        installments: 3,
        stock_status: 'on_demand',
        lead_time: 'Disponível em 3 dias úteis',
        warranty_days: 365,
      },
    }), { status: 200, headers: { 'Content-Type': 'application/json' } }));

    render(<MemoryRouter initialEntries={['/produtos/painel-estrela-triangulo-bomba-incendio-10cv-220v']}><Routes><Route path="/produtos/:slug" element={<DetailPage kind="produto"/>}/></Routes></MemoryRouter>);

    await waitFor(() => expect(screen.getByRole('heading', { level: 1, name: 'Painel Estrela Triângulo para Bomba de Incêndio 10CV 220V | Painel de Comando' })).toBeInTheDocument());
    expect(screen.getByRole('link', { name: 'Bomba de Incêndio' })).toHaveAttribute('href', '/produtos?linha=bomba-de-incendio');
    expect(screen.getByText('R$ 2.150,00')).toBeInTheDocument();
    expect(screen.getByText('ou 3x de R$ 716,67 sem juros')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Vídeo 1' })).toBeInTheDocument();
  });
});
