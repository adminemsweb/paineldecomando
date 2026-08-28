import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it } from 'vitest';
import HomePage from './HomePage';

afterEach(cleanup);

describe('HomePage testimonials', () => {
  it('destaca caminhos comerciais sem repetir o catálogo visualmente', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);

    expect(screen.getByRole('heading', { name: 'Comece pela necessidade da sua operação.' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /encontrar meu painel/i })).toHaveAttribute('href', '/painel-de-comando-eletrico');
    expect(screen.getByRole('link', { name: /atendimento em Sorocaba/i })).toHaveAttribute('href', '/painel-de-comando-sorocaba');
  });

  it('mostra painéis reais com preço e link para a página do produto', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);

    expect(screen.getByRole('heading', { name: 'Painéis prontos para comprar.' })).toBeInTheDocument();
    expect(screen.getByRole('img', { name: 'Painel Bomba de Incêndio 10CV 220V' })).toHaveAttribute('src', '/images/painel-bomba-incendio-10cv-220v-vermelho.png');
    expect(screen.getByText('R$ 2.150,00')).toBeInTheDocument();
    expect(screen.getByRole('img', { name: 'Painel com Inversor CFW300 1CV 220V' })).toHaveAttribute('src', '/images/painel-inversor-cfw300-1cv-220v-principal.png');
    expect(screen.getByText('R$ 2.205,00')).toBeInTheDocument();
    expect(screen.getAllByRole('link', { name: 'Painel com Inversor CFW300 1CV 220V' })).toEqual(expect.arrayContaining([
      expect.objectContaining({ pathname: '/produtos/painel-inversor-cfw300-1cv-220v-mono' }),
    ]));
  });

  it('avanca o carrossel e mantem o conteudo focado em paineis de comando', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);

    expect(screen.getByText(/O painel chegou bem acabado/i)).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: /painéis de comando recomenda/i })).toBeInTheDocument();

    fireEvent.click(screen.getByRole('button', { name: 'Mostrar depoimento 2 de 6' }));

    expect(screen.getByText(/painel compacto, bem protegido/i)).toBeInTheDocument();
  });

  it('nao exibe os controles superiores do carrossel', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);
    expect(screen.queryByRole('button', { name: 'Pausar carrossel automático' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Depoimento anterior' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Próximo depoimento' })).not.toBeInTheDocument();
  });

  it('reproduz o vídeo das marcas parceiras automaticamente, sem som e em loop', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);
    const partnerVideo = screen.getByLabelText('Marcas parceiras MR Drives e Metal Life');

    expect(partnerVideo).toHaveAttribute('src', '/videos/marcas.mp4');
    expect(partnerVideo).toHaveAttribute('autoplay');
    expect(partnerVideo).toHaveAttribute('loop');
    expect(partnerVideo).toHaveProperty('muted', true);
    expect(partnerVideo).toHaveAttribute('playsinline');
  });

  it('apresenta as quatro etapas do fluxo de fabricação', () => {
    render(<MemoryRouter><HomePage/></MemoryRouter>);

    expect(screen.getByRole('heading', { name: 'Fluxo de Fabricação' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Levantamento' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Engenharia' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Fabricação' })).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Validação' })).toBeInTheDocument();
  });
});
