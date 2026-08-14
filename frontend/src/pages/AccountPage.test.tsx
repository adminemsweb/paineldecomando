import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AccountPage } from './PublicPages';
import { AuthProvider } from '../auth/AuthContext';

const unauthenticated = () => new Response(JSON.stringify({ success: false, data: null, message: 'Sessão não autenticada.' }), { status: 401, headers: { 'Content-Type': 'application/json' } });

afterEach(() => { cleanup(); vi.restoreAllMocks(); window.history.replaceState(null, '', '/'); });

describe('AccountPage', () => {
  it('alterna entre entrada e criacao de conta', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(unauthenticated());
    render(<MemoryRouter><AuthProvider><AccountPage/></AuthProvider></MemoryRouter>);

    expect(await screen.findByRole('heading', { name: 'Entre na sua conta' })).toBeInTheDocument();
    fireEvent.click(screen.getByRole('tab', { name: 'Criar conta' }));

    expect(screen.getByRole('heading', { name: 'Crie sua conta' })).toBeInTheDocument();
    expect(screen.getByLabelText(/Nome completo/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Telefone/i)).toBeInTheDocument();
  });

  it('cadastra o cliente e exibe os dados retornados pela API', async () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch')
      .mockResolvedValueOnce(unauthenticated())
      .mockResolvedValueOnce(new Response(JSON.stringify({ success: true, data: { id: 7, name: 'Maria Cliente', email: 'maria@empresa.com.br', company: 'Empresa Teste', phone: '11999999999', address: null }, message: 'Conta criada com sucesso.' }), { status: 201, headers: { 'Content-Type': 'application/json' } }));
    render(<MemoryRouter><AuthProvider><AccountPage/></AuthProvider></MemoryRouter>);
    await screen.findByRole('heading', { name: 'Entre na sua conta' });
    fireEvent.click(screen.getByRole('tab', { name: 'Criar conta' }));
    fireEvent.change(screen.getByLabelText(/Nome completo/i), { target: { value: 'Maria Cliente' } });
    fireEvent.change(screen.getByLabelText(/Empresa/i), { target: { value: 'Empresa Teste' } });
    fireEvent.change(screen.getByLabelText(/Telefone/i), { target: { value: '(11) 99999-9999' } });
    fireEvent.change(screen.getByLabelText(/E-mail/i), { target: { value: 'maria@empresa.com.br' } });
    fireEvent.change(screen.getByLabelText(/Senha/i), { target: { value: 'Senha123' } });
    fireEvent.click(screen.getByRole('checkbox'));
    fireEvent.click(screen.getByRole('button', { name: /Criar minha conta/i }));

    expect(await screen.findByRole('heading', { name: 'Olá, Maria.' })).toBeInTheDocument();
    expect(screen.getByDisplayValue('maria@empresa.com.br')).toBeInTheDocument();
    await waitFor(() => expect(fetchSpy).toHaveBeenCalledTimes(2));
    expect(fetchSpy.mock.calls[1][0]).toContain('/auth/register');
  });

  it('mantem a aba de endereco selecionada ao recarregar pela URL', async () => {
    window.history.replaceState(null, '', '/conta#endereco-principal');
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({ success: true, data: { id: 8, name: 'Julia Cliente', email: 'julia@empresa.com.br', company: null, phone: '15999999999', address: { recipientName: 'Julia Cliente', postalCode: '18056450', street: 'Rua Teste', number: '83', complement: null, district: 'Centro', city: 'Sorocaba', state: 'SP' } } }), { status: 200, headers: { 'Content-Type': 'application/json' } }));
    render(<MemoryRouter><AuthProvider><AccountPage/></AuthProvider></MemoryRouter>);

    const addressTab = await screen.findByRole('tab', { name: 'Endereço principal' });
    expect(addressTab).toHaveAttribute('aria-selected', 'true');
    expect(screen.getByRole('heading', { name: 'Endereço principal' })).toBeInTheDocument();
  });
});
