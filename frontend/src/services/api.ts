import type { ApiResponse } from '../types/api';

const API_URL = import.meta.env.VITE_API_URL ?? '/api/v1';

export class ApiError extends Error {
  constructor(message: string, public status: number, public errors?: Record<string, string[]>) {
    super(message);
  }
}

export async function apiRequest<T>(path: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
  const controller = new AbortController();
  const timeout = window.setTimeout(() => controller.abort(), 12_000);
  try {
    const response = await fetch(`${API_URL}${path}`, {
      credentials: 'include',
      ...options,
      headers: options.body instanceof FormData ? options.headers : { 'Content-Type': 'application/json', ...options.headers },
      signal: controller.signal,
    });
    const payload = (await response.json()) as ApiResponse<T>;
    if (!response.ok) throw new ApiError(payload.message ?? 'Não foi possível concluir a solicitação.', response.status, payload.errors);
    return payload;
  } finally {
    window.clearTimeout(timeout);
  }
}

