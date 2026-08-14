export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  meta?: { page: number; per_page: number; total: number; total_pages: number };
  errors?: Record<string, string[]>;
  request_id?: string;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  summary: string;
  category?: string;
  applications?: string[];
  image_url?: string;
}
