import { FormEvent, useState } from 'react';
import { ApiError, apiRequest } from '../../services/api';
import { Icon } from './Icon';

type ShippingOption = { service: string; code: string; price: string; days: number };
type ShippingQuote = { cep: string; address: string; city: string; uf: string; options: ShippingOption[] };

export function ShippingCalculator({ variant = 'header' }: { variant?: 'header' | 'product' }) {
  const [cep, setCep] = useState(() => localStorage.getItem('delivery-cep') ?? '');
  const [quote, setQuote] = useState<ShippingQuote | null>(() => {
    try { return JSON.parse(localStorage.getItem('delivery-quote') ?? 'null') as ShippingQuote | null; }
    catch { return null; }
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const updateCep = (value: string) => {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    setCep(digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits);
    setError('');
    setQuote(null);
    localStorage.removeItem('delivery-quote');
  };

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (cep.replace(/\D/g, '').length !== 8) { setError('Digite um CEP válido.'); return; }
    setLoading(true);
    setError('');
    try {
      const response = await apiRequest<ShippingQuote>('/shipping/quote', { method: 'POST', body: JSON.stringify({ cep }) });
      setQuote(response.data);
      localStorage.setItem('delivery-cep', cep);
      localStorage.setItem('delivery-quote', JSON.stringify(response.data));
    } catch (requestError) {
      setError(requestError instanceof ApiError ? requestError.message : 'Não foi possível consultar o frete.');
    } finally { setLoading(false); }
  };

  if (variant === 'product') return <form className="product-shipping" onSubmit={submit}>
    <label htmlFor="product-cep">Consultar prazo e entrega pelos Correios</label>
    <div><input id="product-cep" inputMode="numeric" autoComplete="postal-code" value={cep} onChange={(event) => updateCep(event.target.value)} placeholder="Informe seu CEP"/><button type="submit" disabled={loading}>{loading ? '...' : 'OK'}</button></div>
    {error && <small className="shipping-feedback shipping-feedback--error" role="alert">{error}</small>}
    {quote && <div className="shipping-result"><strong>{quote.address}</strong>{quote.options.map(option => <span key={option.code}><b>{option.service}</b><em>R$ {option.price}</em><small>{option.days > 0 ? `até ${option.days} dias úteis` : 'prazo sob consulta'}</small></span>)}</div>}
  </form>;

  return <div className="header-delivery">
    <div className="header-delivery__label"><Icon name="pin" size={21}/><span><small>Entregar em</small><strong title={quote?.address}>{quote ? `${quote.city}/${quote.uf}` : 'Informe seu CEP'}</strong></span></div>
    <form onSubmit={submit}><label className="sr-only" htmlFor="header-cep">CEP de entrega</label><input id="header-cep" inputMode="numeric" autoComplete="postal-code" value={cep} onChange={(event) => updateCep(event.target.value)} placeholder="00000-000"/><button type="submit" aria-label="Buscar CEP e calcular frete" disabled={loading}><Icon name="search" size={18}/></button></form>
    {error && <small className="header-delivery__feedback" role="alert">{error}</small>}
    {quote && quote.options.length > 0 && <small className="header-delivery__feedback header-delivery__feedback--success">Frete a partir de R$ {quote.options[0].price}</small>}
  </div>;
}
