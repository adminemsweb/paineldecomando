import type { ApiResponse } from '../types/api';

const API_URL = import.meta.env.VITE_API_URL ?? '/api/v1';

export class ApiError extends Error {
  constructor(message: string, public status: number, public errors?: Record<string, string[]>, public requestId?: string) {
    super(message);
    this.name = 'ApiError';
  }
}

type ApiRequestOptions = RequestInit & { timeoutMs?: number };

export async function apiRequest<T>(path: string, options: ApiRequestOptions = {}): Promise<ApiResponse<T>> {
  const { timeoutMs = 12_000, ...requestOptions } = options;
  const controller = new AbortController();
  const timeout = window.setTimeout(() => controller.abort(), timeoutMs);
  try {
    const response = await fetch(`${API_URL}${path}`, {
      credentials: 'include',
      ...requestOptions,
      headers: requestOptions.body instanceof FormData || requestOptions.body == null ? requestOptions.headers : { 'Content-Type': 'application/json', ...requestOptions.headers },
      signal: controller.signal,
    });
    const text = await response.text();
    let payload: ApiResponse<T>;
    try { payload = JSON.parse(text) as ApiResponse<T>; }
    catch { throw new ApiError('A API retornou uma resposta inválida.', response.status, undefined, response.headers.get('X-Request-ID') ?? undefined); }
    if (!response.ok) throw new ApiError(payload.message ?? 'Não foi possível concluir a solicitação.', response.status, payload.errors, payload.request_id);
    return payload;
  } catch (error) {
    if (error instanceof ApiError) throw error;
    if (error instanceof DOMException && error.name === 'AbortError') throw new ApiError('A solicitação demorou mais que o esperado.', 408);
    throw new ApiError('Não foi possível conectar ao servidor.', 0);
  } finally {
    window.clearTimeout(timeout);
  }
}
