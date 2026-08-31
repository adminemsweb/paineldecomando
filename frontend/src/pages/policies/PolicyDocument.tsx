import { companyConfig } from '../../constants/company';

export type PolicyKind = 'returns' | 'warranty' | 'privacy';
type PolicySection = { title: string; paragraphs: string[]; points?: string[] };

const policyContent: Record<PolicyKind, { eyebrow: string; title: string; subtitle: string; sections: PolicySection[] }> = {
  returns: { eyebrow: 'Trocas e atendimento ao consumidor', title: 'Trocas, Devoluções e Arrependimento', subtitle: 'Solicitações tratadas com transparência e conforme as regras do comércio eletrônico brasileiro.', sections: [
    { title: 'Direito de arrependimento', paragraphs: ['Em compras concluídas fora do estabelecimento comercial, o consumidor pode solicitar o arrependimento em até 7 dias corridos, contados da assinatura ou do recebimento do produto, conforme aplicável.'] },
    { title: 'Como solicitar', paragraphs: [`Solicite pela Central de ajuda, pelo e-mail ${companyConfig.email} ou pelo WhatsApp. Enviaremos a confirmação e as orientações para coleta ou postagem.`] },
    { title: 'Conservação e envio', paragraphs: ['O produto deve ser devolvido com os acessórios, manuais, nota fiscal e componentes recebidos. A abertura necessária para avaliação não elimina, por si só, o direito de arrependimento.'] },
    { title: 'Avaria ou item divergente', paragraphs: ['Comunique imediatamente e envie fotos da embalagem, da etiqueta e do produto. Após análise, orientaremos a troca, a correção do envio ou a restituição, conforme o caso.'] },
    { title: 'Restituição', paragraphs: ['Após o recebimento e a conferência da devolução, a restituição será processada pelo meio aplicável à compra, observados os prazos da instituição financeira e a legislação vigente.'] },
  ] },
  warranty: { eyebrow: 'Garantia e suporte técnico', title: 'Política de Garantia', subtitle: 'Produtos industriais exigem instalação, parametrização e uso compatíveis com o manual técnico.', sections: [
    { title: 'Garantia legal e contratual', paragraphs: ['A garantia legal é preservada nos termos da legislação aplicável. Eventual garantia contratual adicional será indicada na proposta, na nota fiscal, no certificado ou na página do produto.'] },
    { title: 'Abertura do atendimento', paragraphs: ['Informe número do pedido ou nota fiscal, modelo, número de série, descrição da falha, aplicação e registros de instalação. A equipe poderá solicitar fotos, vídeos e parâmetros.'] },
    { title: 'Análise técnica', paragraphs: ['O produto poderá ser encaminhado para diagnóstico. O prazo e a logística serão informados no protocolo de atendimento.'] },
    { title: 'Exclusões', paragraphs: ['A garantia pode não cobrir danos decorrentes de ligação elétrica incompatível, instalação fora do manual, sobrecarga, curto externo, umidade em modelo sem proteção adequada, violação, queda ou intervenção não autorizada, após análise técnica.'] },
    { title: 'Solução', paragraphs: ['Confirmado defeito coberto, serão aplicadas as medidas cabíveis, como reparo, substituição ou restituição, respeitando a legislação aplicável.'] },
  ] },
  privacy: { eyebrow: 'Privacidade e proteção de dados', title: 'Política de Privacidade', subtitle: 'Apresentamos de forma transparente como tratamos dados de visitantes, clientes e contatos comerciais.', sections: [
    { title: 'Dados que podemos tratar', paragraphs: ['Coletamos informações relacionadas ao atendimento e à operação da loja quando necessárias para cadastro, faturamento, pagamento, entrega ou suporte.'], points: ['Cadastro: nome, sobrenome, e-mail e telefone.', 'Compra: CPF ou CNPJ, endereço, CEP e dados do pedido.', 'Navegação: páginas acessadas, produtos visualizados, termos pesquisados, cliques de interesse, categoria do dispositivo e um identificador aleatório do navegador.'] },
    { title: 'Como utilizamos os dados', paragraphs: ['Usamos os dados para atender solicitações, preparar propostas, processar pedidos, pagamentos e entregas, emitir documentos fiscais, prevenir fraudes e cumprir obrigações legais.', 'As métricas de navegação ajudam a entender quais painéis recebem mais interesse, quais buscas não encontram resultados e quais páginas do catálogo precisam ser melhoradas.'] },
    { title: 'Compartilhamento responsável', paragraphs: ['A Painel de Comando não vende dados pessoais. O compartilhamento é limitado ao necessário para executar o serviço ou cumprir obrigação legal.'], points: ['Instituições de pagamento e prevenção a fraude.', 'Transportadoras e operadores logísticos.', 'Prestadores de hospedagem, tecnologia e contabilidade.', 'Autoridades públicas, quando houver obrigação legal.'] },
    { title: 'Armazenamento e segurança', paragraphs: ['Adotamos controles técnicos e administrativos proporcionais aos riscos. Os dados são mantidos somente pelo período necessário à finalidade informada ou exigido por lei.', 'Os eventos de navegação usados no painel administrativo não armazenam o endereço IP. O identificador aleatório não contém nome, e-mail ou telefone e os eventos são eliminados após 180 dias.'] },
    { title: 'Seus direitos pela LGPD', paragraphs: [`Você pode solicitar confirmação de tratamento, acesso, correção e demais direitos aplicáveis pelo e-mail ${companyConfig.email}. Algumas informações podem ser preservadas para cumprimento de obrigação legal.`] },
    { title: 'Cookies, armazenamento local e preferências', paragraphs: ['Utilizamos cookies essenciais para funcionamento e segurança. O navegador também guarda localmente um identificador aleatório para produzir contagens de visitantes sem identificação direta. Recursos adicionais devem ser apresentados com informação e opção de escolha quando aplicável.'] },
  ] },
};

export function PolicyDocument({ kind }: { kind: PolicyKind }) {
  const policy = policyContent[kind];
  return <main className="policy-page"><section className="policy-hero"><div className="container"><span>{policy.eyebrow}</span><h1>{policy.title}</h1><p>{policy.subtitle}</p><small>Última atualização: 31/08/2026</small></div></section><section className="policy-body"><div className="container policy-layout"><aside><h2>Conteúdo da política</h2><nav>{policy.sections.map((section, index) => <a key={section.title} href={`#policy-${kind}-${index + 1}`}><b>{String(index + 1).padStart(2, '0')}</b>{section.title}</a>)}</nav></aside><article>{policy.sections.map((section, index) => <section id={`policy-${kind}-${index + 1}`} key={section.title}><h2>{section.title}</h2>{section.paragraphs.map(paragraph => <p key={paragraph}>{paragraph}</p>)}{section.points && <ol>{section.points.map((point, pointIndex) => <li key={point}><b>{index + 1}.{pointIndex + 1}</b><span>{point}</span></li>)}</ol>}</section>)}</article></div></section></main>;
}
