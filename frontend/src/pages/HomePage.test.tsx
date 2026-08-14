import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it } from 'vitest';
import HomePage from './HomePage';

afterEach(cleanup);

describe('HomePage testimonials', () => {
  it('avanca o carrossel e mantem o conteudo focado em paineis de comando', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);

    expect(screen.getByText(/O painel chegou bem acabado/i)).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: /painéis de comando recomenda/i })).toBeInTheDocument();

    fireEvent.click(screen.getByRole('button', { name: 'Próximo depoimento' }));

    expect(screen.getByText(/painel compacto, bem protegido/i)).toBeInTheDocument();
  });

  it('permite pausar e continuar o movimento automatico', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);
    const pauseButton = screen.getByRole('button', { name: 'Pausar carrossel automático' });

    fireEvent.click(pauseButton);

    expect(screen.getByRole('button', { name: 'Continuar carrossel automático' })).toBeInTheDocument();
  });
});
