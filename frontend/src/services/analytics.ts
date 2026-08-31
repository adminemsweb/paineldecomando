const API_URL = import.meta.env.VITE_API_URL ?? '/api/v1';

export type AnalyticsEvent = {
  eventType: 'page_view' | 'product_view' | 'product_click' | 'search' | 'whatsapp_click' | 'quote_click';
  path?: string;
  productSlug?: string;
  searchTerm?: string;
  resultCount?: number;
  targetUrl?: string;
};

let memorySession = '';
const recentEvents = new Map<string, number>();

function sessionId() {
  if (memorySession) return memorySession;
  try {
    const stored = window.localStorage.getItem('pdc_analytics_session');
    if (stored) return memorySession = stored;
    memorySession = globalThis.crypto?.randomUUID?.() ?? `${Date.now().toString(16)}-${Math.random().toString(16).slice(2)}`;
    window.localStorage.setItem('pdc_analytics_session', memorySession);
  } catch {
    memorySession = `${Date.now().toString(16)}-${Math.random().toString(16).slice(2)}`;
  }
  return memorySession;
}

function deviceType() {
  const width = window.innerWidth;
  if (width < 768) return 'mobile';
  if (width < 1100) return 'tablet';
  return 'desktop';
}

function referrerOrigin() {
  if (!document.referrer) return undefined;
  try { return new URL(document.referrer).origin; }
  catch { return undefined; }
}

export function trackAnalytics(event: AnalyticsEvent) {
  if (window.location.pathname.startsWith('/admin')) return;
  const signature = JSON.stringify(event);
  const now = Date.now();
  if (now - (recentEvents.get(signature) ?? 0) < 1200) return;
  recentEvents.set(signature, now);
  void fetch(`${API_URL}/analytics/events`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      event_type: event.eventType,
      session_id: sessionId(),
      path: event.path ?? window.location.pathname,
      product_slug: event.productSlug,
      search_term: event.searchTerm,
      result_count: event.resultCount,
      target_url: event.targetUrl,
      referrer: referrerOrigin(),
      device_type: deviceType(),
    }),
    keepalive: true,
  }).catch(() => undefined);
}
