import { fireEvent, render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { ShippingCalculator } from './ShippingCalculator';

describe('ShippingCalculator', () => {
  it('valida CEP antes de chamar a API', () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch');
    render(<ShippingCalculator variant="product"/>);
    fireEvent.change(screen.getByLabelText(/consultar prazo/i), { target: { value: '123' } });
    fireEvent.click(screen.getByRole('button', { name: 'OK' }));
    expect(screen.getByRole('alert')).toHaveTextContent('Digite um CEP válido.');
    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('aplica a máscara do CEP', () => {
    render(<ShippingCalculator variant="product"/>);
    const input = screen.getByLabelText(/consultar prazo/i) as HTMLInputElement;
    fireEvent.change(input, { target: { value: '18056450' } });
    expect(input.value).toBe('18056-450');
  });

  it('consulta cidade e UF no cabecalho sem solicitar cotacao de frete', async () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(JSON.stringify({
      success: true,
      data: {
        cep: '18056450',
        address: 'Jardim Simus, Sorocaba - SP',
        city: 'Sorocaba',
        uf: 'SP',
        options: [],
      },
      message: 'CEP encontrado.',
    }), { status: 200, headers: { 'Content-Type': 'application/json' } }));

    render(<ShippingCalculator/>);
    fireEvent.change(screen.getByLabelText(/CEP de entrega/i), { target: { value: '18056450' } });
    fireEvent.click(screen.getByRole('button', { name: /buscar CEP/i }));

    expect(await screen.findByText(/Entrega selecionada: Sorocaba\/SP/i)).toBeInTheDocument();
    expect(fetchSpy).toHaveBeenCalledWith(
      expect.stringContaining('/shipping/cep'),
      expect.objectContaining({ method: 'POST' }),
    );
  });
});
