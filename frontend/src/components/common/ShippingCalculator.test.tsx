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
});
