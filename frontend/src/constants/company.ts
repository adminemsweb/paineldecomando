export const companyConfig = {
  name: import.meta.env.VITE_COMPANY_NAME ?? 'Painel CMD',
  shortName: import.meta.env.VITE_COMPANY_SHORT_NAME ?? 'PDC',
  legalName: import.meta.env.VITE_COMPANY_LEGAL_NAME ?? 'SMARTFLOW TECNOLOGIA EIRELI',
  cnpj: import.meta.env.VITE_COMPANY_CNPJ ?? '19.252.656/0001-20',
  phone: import.meta.env.VITE_COMPANY_PHONE ?? '+55 11 96919-5102',
  whatsapp: import.meta.env.VITE_COMPANY_WHATSAPP ?? '5511969195102',
  whatsappLabel: import.meta.env.VITE_COMPANY_WHATSAPP_LABEL ?? '+55 11 96919-5102',
  email: import.meta.env.VITE_COMPANY_EMAIL ?? 'contato@paineldecomando.com.br',
  address: import.meta.env.VITE_COMPANY_ADDRESS ?? 'ENDEREÇO A CONFIGURAR',
  hours: import.meta.env.VITE_COMPANY_HOURS ?? 'HORÁRIO A CONFIGURAR',
  deliveryNotice: import.meta.env.VITE_COMPANY_DELIVERY_NOTICE ?? 'Entregas exclusivamente em território brasileiro.',
} as const;
