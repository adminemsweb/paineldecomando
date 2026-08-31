import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { AnalyticsTracker } from './AnalyticsTracker';

afterEach(() => vi.unstubAllGlobals());

describe('AnalyticsTracker', () => {
  it('registra visualização e clique de produto sem dados pessoais', async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response(null, { status: 201 }));
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter initialEntries={['/produtos/painel-teste']}>
        <AnalyticsTracker />
        <a href="/produtos/outro-painel" onClick={event => event.preventDefault()}>Outro painel</a>
      </MemoryRouter>,
    );

    await waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(2));
    fireEvent.click(screen.getByRole('link', { name: 'Outro painel' }));
    await waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(3));

    const payloads = fetchMock.mock.calls.map(([, options]) => JSON.parse(String(options?.body)));
    expect(payloads.map(payload => payload.event_type)).toEqual(['page_view', 'product_view', 'product_click']);
    expect(payloads[1]).toMatchObject({ product_slug: 'painel-teste', device_type: 'tablet' });
    expect(payloads[2]).toMatchObject({ product_slug: 'outro-painel', target_url: '/produtos/outro-painel' });
    expect(payloads[0]).not.toHaveProperty('ip');
  });
});
