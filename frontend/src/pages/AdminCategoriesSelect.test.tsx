import { cleanup, fireEvent, render, screen, waitFor, within } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AdminCategoriesPage, AdminProductsPage } from './AdminPages';

afterEach(() => {
  cleanup();
  vi.restoreAllMocks();
});

describe('seletor de categorias do produto', () => {
  it('mostra categorias e subcategorias na mesma lista', async () => {
    vi.spyOn(globalThis, 'fetch').mockImplementation(async input => {
      const url = String(input);
      const data = url.includes('/admin/categories') ? [
        { id: 1, parent_id: null, name: 'Painéis de partida', slug: 'paineis-de-partida', status: 'published', sort_order: 1 },
        { id: 7, parent_id: 1, name: 'Painel Estrela Triângulo Econômico', slug: 'painel-estrela-triangulo-economico', status: 'published', sort_order: 1 },
        { id: 8, parent_id: 1, name: 'Painel Estrela Triângulo Padrão', slug: 'painel-estrela-triangulo-padrao', status: 'published', sort_order: 2 },
        { id: 2, parent_id: null, name: 'Painel com Soft Starter', slug: 'painel-com-soft-starter', status: 'published', sort_order: 2 },
      ] : [];
      return new Response(JSON.stringify({ data }), { status: 200, headers: { 'Content-Type': 'application/json' } });
    });

    render(<MemoryRouter><AdminProductsPage/></MemoryRouter>);
    await waitFor(() => expect(screen.getByText('Nenhum produto encontrado')).toBeInTheDocument());
    fireEvent.click(screen.getByRole('button', { name: '+ Novo produto' }));

    const categorySelect = await screen.findByRole('combobox', { name: 'Categoria' });
    expect(within(categorySelect).getByRole('option', { name: 'Estrela-Triângulo' })).toBeInTheDocument();
    expect(within(categorySelect).getByRole('option', { name: '↳ Painel Estrela Triângulo Econômico' })).toBeInTheDocument();
    expect(within(categorySelect).getByRole('option', { name: '↳ Painel Estrela Triângulo Padrão' })).toBeInTheDocument();
    expect(within(categorySelect).getByRole('option', { name: 'Soft Starter' })).toBeInTheDocument();

    fireEvent.change(categorySelect, { target: { value: '7' } });
    expect(screen.getByRole('combobox', { name: 'Subcategoria' })).toHaveValue('7');
  });
});

describe('gerenciamento de categorias', () => {
  it('cria uma subcategoria vinculada a uma categoria principal', async () => {
    const categories = [
      { id: 1, parent_id: null, name: 'Painéis de partida', slug: 'paineis-de-partida', status: 'published', sort_order: 1 },
    ];
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockImplementation(async (_input, init) => {
      if (init?.method === 'POST') {
        const body = JSON.parse(String(init.body));
        return new Response(JSON.stringify({ data: { id: 7, ...body } }), { status: 201, headers: { 'Content-Type': 'application/json' } });
      }
      return new Response(JSON.stringify({ data: categories }), { status: 200, headers: { 'Content-Type': 'application/json' } });
    });

    render(<MemoryRouter><AdminCategoriesPage/></MemoryRouter>);
    expect(await screen.findByText('Painéis de partida')).toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Subcategoria' }));
    expect(screen.getByRole('combobox', { name: 'Categoria principal' })).toHaveValue('1');
    fireEvent.change(screen.getByRole('textbox', { name: 'Nome' }), { target: { value: 'Painel Estrela Triângulo Econômico' } });
    fireEvent.click(screen.getByRole('button', { name: 'Salvar categoria' }));

    await waitFor(() => expect(fetchMock).toHaveBeenCalledWith(expect.stringContaining('/admin/categories'), expect.objectContaining({ method: 'POST' })));
    expect(await screen.findByText('Painel Estrela Triângulo Econômico')).toBeInTheDocument();
  });
});
