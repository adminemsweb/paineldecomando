import { Component, type ErrorInfo, type ReactNode } from 'react';

type State = { hasError: boolean };

export class ErrorBoundary extends Component<{ children: ReactNode }, State> {
  state: State = { hasError: false };

  static getDerivedStateFromError(): State { return { hasError: true }; }

  componentDidCatch(error: Error, info: ErrorInfo) {
    if (import.meta.env.DEV) console.error('Falha de renderização', error, info);
  }

  render() {
    if (!this.state.hasError) return this.props.children;
    return <main className="fatal-error" role="alert"><div><span>Não foi possível carregar esta tela</span><h1>O site encontrou um erro inesperado.</h1><p>Atualize a página para tentar novamente. Se o problema continuar, entre em contato com o atendimento.</p><button className="button button--primary" type="button" onClick={() => window.location.reload()}>Atualizar página</button></div></main>;
  }
}
