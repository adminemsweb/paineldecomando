import type { ReactNode } from 'react';
import { Link } from 'react-router-dom';

interface Props { to: string; children: ReactNode; variant?: 'primary' | 'secondary' }

export function ButtonLink({ to, children, variant = 'primary' }: Props) {
  return <Link className={`button button--${variant}`} to={to}>{children}</Link>;
}

