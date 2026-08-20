import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AppRoutes } from './AppRoutes';

describe('rotas administrativas', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('direciona /admin para a tela de login', async () => {
    render(<MemoryRouter initialEntries={['/admin']}><AppRoutes/></MemoryRouter>);

    expect(await screen.findByRole('heading', { name: 'Painel administrativo' })).toBeInTheDocument();
    expect(screen.getByLabelText('E-mail')).toBeInTheDocument();
    expect(screen.getByLabelText('Senha')).toBeInTheDocument();
  });

  it('bloqueia uma rota interna sem sessão administrativa', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({ success: false, message: 'Autenticação necessária.' }), { status: 401, headers: { 'Content-Type': 'application/json' } }));

    render(<MemoryRouter initialEntries={['/admin/dashboard']}><AppRoutes/></MemoryRouter>);

    expect(await screen.findByRole('heading', { name: 'Painel administrativo' })).toBeInTheDocument();
  });
});
