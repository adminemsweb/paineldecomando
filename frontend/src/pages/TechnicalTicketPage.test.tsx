import { fireEvent, render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { TechnicalTicketPage } from './TechnicalTicketPage';

describe('TechnicalTicketPage', () => {
  it('abre o WhatsApp com dados do formulário', () => {
    const open = vi.spyOn(window, 'open').mockImplementation(() => null);
    render(<TechnicalTicketPage/>);
    fireEvent.change(screen.getByLabelText('Nome'), { target: { value: 'Ana' } });
    fireEvent.change(screen.getByLabelText('E-mail'), { target: { value: 'ana@example.com' } });
    fireEvent.change(screen.getByLabelText('WhatsApp'), { target: { value: '11999999999' } });
    fireEvent.change(screen.getByLabelText('Aplicação'), { target: { value: 'Bomba industrial' } });
    fireEvent.change(screen.getByLabelText('Mensagem'), { target: { value: 'Preciso de suporte' } });
    fireEvent.click(screen.getByRole('button', { name: /Abrir atendimento/ }));
    expect(open).toHaveBeenCalledOnce();
    expect(open.mock.calls[0][0]).toContain('Nome%3A%20Ana');
  });
});
