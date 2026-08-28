import { cleanup, render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it } from 'vitest';
import CommercialLandingPage, { type CommercialLandingKind } from './CommercialLandingPage';

afterEach(cleanup);

describe('CommercialLandingPage', () => {
  const cases: Array<[CommercialLandingKind, string]> = [
    ['comando', 'Painel de comando elétrico industrial'],
    ['sob-medida', 'Painéis elétricos industriais sob medida'],
    ['montagem', 'Montagem de painéis elétricos industriais'],
    ['sorocaba', 'Painel de comando em Sorocaba e atendimento para todo o Brasil'],
  ];

  it.each(cases)('renderiza a página %s com título e conversão', (kind, heading) => {
    render(<MemoryRouter><CommercialLandingPage kind={kind}/></MemoryRouter>);

    expect(screen.getByRole('heading', { level: 1, name: heading })).toBeInTheDocument();
    expect(screen.getAllByRole('link', { name: /solicitar orçamento|pedir orçamento técnico/i }).length).toBeGreaterThan(0);
    expect(screen.getByRole('heading', { name: 'Compare painéis de comando por aplicação' })).toBeInTheDocument();
  });

  it('leva o comprador às principais linhas do catálogo', () => {
    render(<MemoryRouter><CommercialLandingPage kind="comando"/></MemoryRouter>);

    expect(screen.queryByText(/motores/i)).not.toBeInTheDocument();
    expect(screen.getByRole('link', { name: /painel com soft starter/i })).toHaveAttribute('href', '/produtos?linha=soft-starter');
    expect(screen.getByRole('link', { name: /painel para irrigação/i })).toHaveAttribute('href', '/produtos?linha=irrigacao');
  });
});
