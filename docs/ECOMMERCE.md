# Arquitetura do e-commerce Painel de Comando

O nome do site e da operação digital é **Painel de Comando**. A **MR Drives** aparece como empresa responsável e chancela institucional da loja.

## Estratégia comercial

A loja será híbrida porque o catálogo combina itens padronizados e painéis dimensionados para cada aplicação.

- **Compra direta:** produto com SKU, configuração fechada, preço, estoque ou prazo e regras de frete cadastrados.
- **Cotação técnica:** produto cujo valor depende de tensão, potência, proteções, ambiente ou opcionais.
- **A partir de:** somente quando existir uma configuração-base comercialmente válida e aprovada.

Cada produto deve declarar seu modo de venda. A interface nunca deve exibir preço, disponibilidade, garantia, frete ou certificação sem dados aprovados.

## Jornada

1. Busca por produto, aplicação, potência ou tipo de acionamento.
2. Comparação de configurações e especificações técnicas.
3. Compra direta ou envio de dados para dimensionamento.
4. Identificação do cliente e endereço.
5. Cálculo de frete e prazo.
6. Pagamento pelo provedor escolhido.
7. Confirmação, acompanhamento e comunicação de status.

## Domínios de dados necessários

- Catálogo: produtos, categorias, imagens, especificações, variantes e SKUs.
- Comercial: listas de preço, promoções, impostos e regras por canal.
- Estoque: saldo, reserva, prazo de produção e disponibilidade.
- Carrinho: itens, quantidades, configurações e validade.
- Cliente: conta, pessoas, empresas, documentos e endereços.
- Pedidos: itens imutáveis, totais, status, histórico e cancelamento.
- Pagamentos: provedor, tentativa, estado, idempotência, estorno e conciliação.
- Entrega: volumes, transportadora, cotação, rastreio e eventos.
- Cotação: dados elétricos, aplicação, anexos, revisões e conversão em pedido.

## Decisões pendentes

- Provedor de pagamento e formas aceitas.
- ERP ou sistema responsável por estoque, faturamento e nota fiscal.
- Transportadoras, regiões atendidas e regras de frete.
- Política de garantia, troca, devolução e cancelamento.
- Produtos que permitem compra direta e suas variações válidas.
- Preços, impostos, prazos e disponibilidade aprovados.
- Dados oficiais de contato e textos jurídicos.

## Fases

1. Identidade Painel de Comando com chancela MR Drives, navegação comercial, busca e páginas de produto.
2. Administração de SKUs, preços, estoque e imagens.
3. Carrinho persistente e identificação do cliente.
4. Frete, pagamento, pedidos e notificações.
5. Integrações fiscais/ERP, testes de segurança e operação assistida.
