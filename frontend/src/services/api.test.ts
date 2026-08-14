import { afterEach, describe, expect, it, vi } from 'vitest';
import { apiRequest } from './api';

describe('apiRequest', () => {
  afterEach(() => vi.unstubAllGlobals());

  it('retorna respostas JSON válidas', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(JSON.stringify({ success: true, data: { ok: true } }), { status: 200, headers: { 'Content-Type': 'application/json' } })));
    await expect(apiRequest<{ ok: boolean }>('/health')).resolves.toMatchObject({ data: { ok: true } });
  });

  it('preserva mensagem, status e request id de erros da API', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(JSON.stringify({ success: false, data: null, message: 'CEP inválido.', request_id: 'req-123' }), { status: 422 })));
    await expect(apiRequest('/shipping/quote')).rejects.toMatchObject({ message: 'CEP inválido.', status: 422, requestId: 'req-123' });
  });

  it('transforma resposta inválida em erro controlado', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response('<html>erro</html>', { status: 502, headers: { 'X-Request-ID': 'req-502' } })));
    await expect(apiRequest('/health')).rejects.toMatchObject({ status: 502, requestId: 'req-502' });
  });
});
