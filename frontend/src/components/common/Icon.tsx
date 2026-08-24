import type { ReactNode } from 'react';

type IconName = 'headset' | 'clipboard' | 'whatsapp' | 'cart' | 'user' | 'search' | 'arrow' | 'mail' | 'lock' | 'shield' | 'check' | 'pin' | 'instagram' | 'facebook' | 'tiktok' | 'truck' | 'discount' | 'creditCard' | 'partnership' | 'idCard' | 'map' | 'support' | 'verified' | 'save' | 'folder' | 'tag' | 'plus' | 'edit' | 'trash';

const paths: Record<IconName, ReactNode> = {
  headset: <><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h3v6H5a1 1 0 0 1-1-1v-5Zm16 0h-3v6h2a1 1 0 0 0 1-1v-5Z"/><path d="M17 19c0 1.1-.9 2-2 2h-3"/></>,
  clipboard: <><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5V3h6v1.5M8 10h8m-8 4h8m-8 4h5"/></>,
  whatsapp: <><path d="M20 11.6a8 8 0 0 1-11.8 7L4 20l1.4-4.1A8 8 0 1 1 20 11.6Z"/><path d="M9 8.5c.3 3 2 4.8 5.3 5.8l1-1.2 1.7.8c-.2 1.3-1 2-2.4 2-3.9-.1-7.2-3.2-7.3-7 0-1.2.7-2 1.7-2.2l.8 1.7-.8 1Z"/></>,
  cart: <><path d="M3 4h2l2.2 10.5h9.7L20 7H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></>,
  user: <><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></>,
  search: <><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></>,
  arrow: <><path d="M5 12h14m-5-5 5 5-5 5"/></>,
  mail: <><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></>,
  lock: <><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></>,
  shield: <><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></>,
  check: <><circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.2 2.2 4.8-5"/></>,
  pin: <><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></>,
  instagram: <><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></>,
  facebook: <path d="M14.5 21v-8h2.8l.4-3h-3.2V8.1c0-.9.3-1.6 1.7-1.6H18V3.8c-.8-.1-1.6-.2-2.4-.2-2.4 0-4.1 1.5-4.1 4.2V10H9v3h2.5v8h3Z" fill="currentColor" stroke="none"/>,
  tiktok: <><path d="M14 4v10.2a4 4 0 1 1-3.5-4"/><path d="M14 4c.6 2.7 2.1 4.2 5 4.5"/></>,
  truck: <><path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/><path d="M16 10v4h5"/></>,
  discount: <><path d="M20 12c0 4.4-3.6 8-8 8-1.4 0-2.8-.4-4-1l-5 2 2-5a8 8 0 1 1 15-4Z"/><path d="m8 16 8-8"/><circle cx="9" cy="9" r="1"/><circle cx="15" cy="15" r="1"/></>,
  creditCard: <><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M15 15h2m2 0h.1"/><path d="m6 6 9-3 2 3"/></>,
  partnership: <><path d="M10.6 13.4a4.5 4.5 0 0 0 6.4 0l2.1-2.1a4.5 4.5 0 0 0-6.4-6.4l-1.2 1.2"/><path d="M13.4 10.6a4.5 4.5 0 0 0-6.4 0l-2.1 2.1a4.5 4.5 0 0 0 6.4 6.4l1.2-1.2"/></>,
  idCard: <><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="2"/><path d="M5.5 16c.5-2 1.5-3 3-3s2.5 1 3 3M14 9h4m-4 4h4m-4 3h2.5"/></>,
  map: <><path d="m3.5 6 5-2.5 7 3 5-2.5v14l-5 2.5-7-3-5 2.5V6Z"/><path d="M8.5 3.5v14m7-11v14"/><circle cx="12" cy="10" r="1.8"/><path d="M12 11.8v2.4"/></>,
  support: <><path d="M4 12a8 8 0 0 1 16 0v4a2 2 0 0 1-2 2h-2v-6h4M4 12h4v6H6a2 2 0 0 1-2-2v-4Z"/><path d="M16 18c0 1.7-1.3 3-3 3h-2"/></>,
  verified: <><path d="m12 2.8 2.3 1.4 2.7-.1 1.2 2.4 2.3 1.5-.3 2.7 1.1 2.5-1.8 2-.4 2.7-2.6.8-1.6 2.2-2.6-.9-2.6.9-1.6-2.2-2.6-.8-.4-2.7-1.8-2 1.1-2.5-.3-2.7 2.3-1.5 1.2-2.4 2.7.1L12 2.8Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></>,
  save: <><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></>,
  folder: <><path d="M3 6.5h6l2-2h3.5l1.5 2H21v12.5H3Z"/><path d="M3 9h18"/></>,
  tag: <><path d="M20 13 13 20l-9-9V4h7Z"/><circle cx="8" cy="8" r="1.3"/></>,
  plus: <><path d="M12 5v14M5 12h14"/></>,
  edit: <><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.7 7.5 3 3"/></>,
  trash: <><path d="M4 7h16M9 7V4h6v3m3 0-1 14H7L6 7"/><path d="M10 11v6m4-6v6"/></>,
};

export function Icon({ name, size = 22 }: { name: IconName; size?: number }) {
  return <svg className="ui-icon" width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name]}</svg>;
}
