import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { trackAnalytics } from '../../services/analytics';

function productSlugFromPath(path: string) {
  return path.match(/^\/produtos\/([^/?#]+)/)?.[1];
}

export function AnalyticsTracker() {
  const { pathname, search } = useLocation();

  useEffect(() => {
    if (pathname.startsWith('/admin')) return;
    const path = `${pathname}${search}`;
    trackAnalytics({ eventType: 'page_view', path });
    const productSlug = productSlugFromPath(pathname);
    if (productSlug) trackAnalytics({ eventType: 'product_view', path, productSlug });
  }, [pathname, search]);

  useEffect(() => {
    const recordClick = (event: MouseEvent) => {
      const target = event.target;
      if (!(target instanceof Element)) return;
      const anchor = target.closest<HTMLAnchorElement>('a[href]');
      if (!anchor) return;
      let url: URL;
      try { url = new URL(anchor.href, window.location.origin); }
      catch { return; }
      const currentSlug = productSlugFromPath(window.location.pathname);
      const cardLink = anchor.closest('article')?.querySelector<HTMLAnchorElement>('a[href^="/produtos/"]');
      const cardSlug = cardLink ? productSlugFromPath(new URL(cardLink.href, window.location.origin).pathname) : undefined;
      const productSlug = currentSlug ?? cardSlug;
      if (url.hostname === 'wa.me' || url.hostname.endsWith('.wa.me')) {
        trackAnalytics({ eventType: 'whatsapp_click', productSlug, targetUrl: url.origin + url.pathname });
      } else if (url.origin === window.location.origin && url.pathname.startsWith('/orcamento')) {
        trackAnalytics({ eventType: 'quote_click', productSlug, targetUrl: url.pathname });
      } else {
        const clickedSlug = productSlugFromPath(url.pathname);
        if (url.origin === window.location.origin && clickedSlug) trackAnalytics({ eventType: 'product_click', productSlug: clickedSlug, targetUrl: url.pathname });
      }
    };
    document.addEventListener('click', recordClick, true);
    return () => document.removeEventListener('click', recordClick, true);
  }, []);

  return null;
}
