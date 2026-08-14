import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { PolicyDocument } from './PolicyDocument';

describe('PolicyDocument', () => {
  it('renderiza garantia com índice navegável', () => {
    render(<PolicyDocument kind="warranty"/>);
    expect(screen.getByRole('heading', { name: 'Política de Garantia' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /Abertura do atendimento/ })).toHaveAttribute('href', '#policy-warranty-2');
    expect(screen.getByRole('heading', { name: 'Exclusões' })).toBeInTheDocument();
  });
});
