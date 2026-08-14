import { FormEvent, useEffect, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { companyConfig } from "../../constants/company";
import { Icon } from "../common/Icon";
import { PaymentIcon } from "../common/PaymentIcon";
import { ShippingCalculator } from "../common/ShippingCalculator";
import { useAuth } from "../../auth/AuthContext";

const productNavigation = [
  {
    label: "Painel Estrela-Triângulo",
    sections: [
      {
        title: "Painel Estrela-Triângulo Econômico",
        options: ["15CV · 220V · Man/Aut.", "380V"],
      },
      { title: "Painel Estrela-Triângulo Padrão", options: ["220V", "380V"] },
      { title: "Painel com Amperímetro", options: ["220V", "380V"] },
    ],
    featured: {
      name: "Painel Estrela-Triângulo 15CV 220V",
      image: "/images/painel-estrela-triangulo-15cv-principal.png",
      to: "/produtos/painel-estrela-triangulo",
    },
  },
  {
    label: "Painel com Soft Starter WEG",
    sections: [{ title: "Tensão de alimentação", options: ["220V", "380V"] }],
    featured: {
      name: "Painel com Soft Starter WEG",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?categoria=soft-starter",
    },
  },
  {
    label: "Painel com Inversor de Frequência",
    sections: [{ title: "Tensão de alimentação", options: ["220V", "380V"] }],
    featured: {
      name: "Painel com Inversor de Frequência",
      image: "/images/hero-painel-industrial-v2.jpg",
      to: "/produtos?categoria=inversor-de-frequencia",
    },
  },
  {
    label: "Painel Bomba de Incêndio",
    sections: [
      {
        title: "Painel Bomba de Incêndio Econômico",
        options: ["220V", "380V"],
      },
      { title: "Painel Bomba de Incêndio Padrão", options: ["220V", "380V"] },
      { title: "Painel Bomba de Incêndio Vermelho", options: ["220V", "380V"] },
      { title: "Bomba Principal e Jockey", options: ["220V", "380V"] },
    ],
    featured: {
      name: "Painel para Bomba de Incêndio",
      image: "/images/hero-painel-comando-poster.jpg",
      to: "/produtos?categoria=incendio",
    },
  },
  {
    label: "Painel para Irrigação",
    sections: [{ title: "Tensão de alimentação", options: ["220V", "380V"] }],
    featured: {
      name: "Painel para Irrigação",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?aplicacao=irrigacao",
    },
  },
  {
    label: "Painel Revezamento de Bombas",
    sections: [{ title: "Tensão de alimentação", options: ["220V", "380V"] }],
    featured: {
      name: "Painel para Revezamento de Bombas",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?categoria=revezamento",
    },
  },
  {
    label: "Mais Categorias",
    sections: [
      {
        title: "Outras soluções",
        options: [
          "Painel para Estação Elevatória",
          "Painel de Partida Direta",
          "Painel com Tomadas Industriais",
          "Painel para Canteiro de Obras",
          "Acessórios para Painéis Elétricos",
          "Quadro de Comando para Bomba de Poço",
        ],
      },
    ],
    featured: undefined,
  },
];

export function PublicLayout() {
  const { user, loading: authLoading } = useAuth();
  const [open, setOpen] = useState(false);
  const [openProductMenu, setOpenProductMenu] = useState<string | null>(null);
  const [search, setSearch] = useState("");
  const [quoteProduct, setQuoteProduct] = useState("Painel Estrela-Triângulo");
  const [quotePhone, setQuotePhone] = useState("");
  const [quoteSummary, setQuoteSummary] = useState("");
  const navigate = useNavigate();
  const { pathname, search: locationSearch } = useLocation();

  useEffect(() => {
    window.scrollTo({ top: 0, left: 0, behavior: "auto" });
  }, [pathname, locationSearch]);

  const scrollToTop = () =>
    window.scrollTo({ top: 0, left: 0, behavior: "auto" });

  const submitSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const term = search.trim();
    navigate(
      term ? `/produtos?busca=${encodeURIComponent(term)}` : "/produtos",
    );
    setOpen(false);
  };

  const submitQuickQuote = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const message = [
      `Olá! Gostaria de solicitar um orçamento.`,
      `Produto: ${quoteProduct}`,
      `Meu WhatsApp: ${quotePhone}`,
      `Resumo: ${quoteSummary}`,
    ].join("\n");
    window.open(
      `https://wa.me/${companyConfig.whatsapp}?text=${encodeURIComponent(message)}`,
      "_blank",
      "noopener,noreferrer",
    );
  };

  return (
    <>
      <header className="header commerce-header">
        <div className="container commerce-header__main">
          <NavLink
            to="/"
            className="commerce-brand"
            aria-label={`${companyConfig.name}, página inicial`}
          >
            <img
              className="commerce-brand__logo"
              src="/brand/painel-de-comando-logo-v2.png"
              alt="Painel de Comando"
            />
          </NavLink>

          <ShippingCalculator />

          <form
            className="commerce-search"
            role="search"
            onSubmit={submitSearch}
          >
            <label className="sr-only" htmlFor="store-search">
              Buscar no catálogo
            </label>
            <input
              id="store-search"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Qual painel ou componente você procura?"
            />
            <button type="submit" aria-label="Buscar">
              <Icon name="search" size={19} />
            </button>
          </form>

          <div className="commerce-header__main-actions">
            <span className="commerce-action">
              <Icon name="truck" size={25} />
              <span>
                <small>Entregamos</small>
                <strong>Todo o Brasil</strong>
              </span>
            </span>
            <NavLink className="commerce-action" to="/contato">
              <Icon name="headset" size={25} />
              <span>
                <small>Precisa de ajuda?</small>
                <strong>Atendimento técnico</strong>
              </span>
            </NavLink>
            <a
              className="commerce-action commerce-action--whatsapp"
              href={`https://wa.me/${companyConfig.whatsapp}?text=${encodeURIComponent("Olá! Gostaria de atendimento sobre os painéis de comando.")}`}
              target="_blank"
              rel="noreferrer"
            >
              <Icon name="whatsapp" size={25} />
              <span>
                <small>Fale agora</small>
                <strong>Chamar no WhatsApp</strong>
              </span>
            </a>
            <NavLink className="commerce-action commerce-account" to="/conta">
              <Icon name="user" size={25} />
              <span>
                <small>{user ? "Minha conta" : "Bem-vindo"}</small>
                <strong>
                  {authLoading
                    ? "Verificando conta…"
                    : user
                      ? `Olá, ${user.name.split(" ")[0]}`
                      : "Entrar / Criar conta"}
                </strong>
              </span>
            </NavLink>
          </div>

          <button
            className="menu-button"
            type="button"
            aria-expanded={open}
            aria-controls="store-menu"
            onClick={() => setOpen(!open)}
          >
            Menu
          </button>
        </div>

        <div
          id="store-menu"
          className={
            open ? "commerce-menus commerce-menus--open" : "commerce-menus"
          }
        >
          <nav className="product-nav" aria-label="Navegação principal">
            <div className="product-nav__track">
              <NavLink
                className="product-nav__home"
                to="/"
                end
                onClick={() => {
                  setOpen(false);
                  setOpenProductMenu(null);
                }}
              >
                Início
              </NavLink>
              {productNavigation.map((group) => {
                const isMenuOpen = openProductMenu === group.label;
                const hasPublishedProduct =
                  group.label === "Painel Estrela-Triângulo";
                return (
                  <div
                    className={
                      isMenuOpen
                        ? "product-nav__item product-nav__item--open"
                        : "product-nav__item"
                    }
                    key={group.label}
                    onMouseEnter={() => setOpenProductMenu(group.label)}
                    onMouseLeave={() => setOpenProductMenu(null)}
                  >
                    <button
                      type="button"
                      aria-expanded={isMenuOpen}
                      onClick={() =>
                        setOpenProductMenu(isMenuOpen ? null : group.label)
                      }
                    >
                      {group.label}
                      <span aria-hidden="true" />
                    </button>
                    <div
                      className={`product-nav__menu${group.featured ? "" : " product-nav__menu--compact"}`}
                    >
                      <div className="product-nav__options">
                        {group.sections.map((section) => (
                          <section key={section.title}>
                            <strong>{section.title}</strong>
                            {section.options.map((option) => (
                              <NavLink
                                key={`${section.title}-${option}`}
                                to={
                                  hasPublishedProduct
                                    ? "/produtos/painel-estrela-triangulo"
                                    : `/produtos?busca=${encodeURIComponent(`${group.label} ${option}`)}`
                                }
                                onClick={() => {
                                  setOpen(false);
                                  setOpenProductMenu(null);
                                }}
                              >
                                {option}
                                <span aria-hidden="true">→</span>
                              </NavLink>
                            ))}
                          </section>
                        ))}
                      </div>
                      {group.featured && (
                        <article className="product-nav__featured">
                          <NavLink
                            to={group.featured.to}
                            onClick={() => {
                              setOpen(false);
                              setOpenProductMenu(null);
                            }}
                          >
                            <img src={group.featured.image} alt="" />
                            <strong>{group.featured.name}</strong>
                          </NavLink>
                          {hasPublishedProduct ? (
                            <>
                              <span>R$ 1.247,00</span>
                              <small>3x de R$ 415,67 sem juros</small>
                            </>
                          ) : (
                            <>
                              <span>Sob consulta</span>
                              <small>Configuração conforme a aplicação</small>
                            </>
                          )}
                          <NavLink
                            className="product-nav__buy"
                            to={group.featured.to}
                            onClick={() => {
                              setOpen(false);
                              setOpenProductMenu(null);
                            }}
                          >
                            Ver produto
                          </NavLink>
                        </article>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </nav>
        </div>
      </header>

      <main id="conteudo">
        <Outlet />
      </main>

      {pathname === "/" && (
        <section className="quick-quote" aria-labelledby="quick-quote-title">
          <div className="container quick-quote__grid">
            <div className="quick-quote__content">
              <span>Contato</span>
              <h2 id="quick-quote-title">
                Orçamento rápido, sem formulário gigante.
              </h2>
              <p>
                Escolha uma solução, deixe seu WhatsApp e envie um pedido direto
                para nossa equipe técnica.
              </p>
              <div className="quick-quote__location">
                <Icon name="pin" size={22} />
                <div>
                  <small>Localização da empresa</small>
                  <strong>{companyConfig.name}</strong>
                  <span>{companyConfig.address}</span>
                  <a
                    href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(companyConfig.address)}`}
                    target="_blank"
                    rel="noreferrer"
                  >
                    Ver no Google Maps →
                  </a>
                </div>
              </div>
            </div>
            <form className="quick-quote__form" onSubmit={submitQuickQuote}>
              <label htmlFor="quick-product">Produto</label>
              <select
                id="quick-product"
                value={quoteProduct}
                onChange={(event) => setQuoteProduct(event.target.value)}
              >
                <option>Painel Estrela-Triângulo</option>
                <option>Painel com Soft Starter</option>
                <option>Painel com Inversor de Frequência</option>
                <option>Painel para Bombas</option>
                <option>Projeto personalizado</option>
              </select>
              <label htmlFor="quick-phone">WhatsApp</label>
              <input
                id="quick-phone"
                type="tel"
                value={quotePhone}
                onChange={(event) => setQuotePhone(event.target.value)}
                placeholder="+55 11 99999-9999"
                required
              />
              <label htmlFor="quick-summary">Resumo do pedido</label>
              <textarea
                id="quick-summary"
                value={quoteSummary}
                onChange={(event) => setQuoteSummary(event.target.value)}
                placeholder="Ex.: motor 3 cv, rede 220 V, bomba de recalque"
                required
              />
              <div className="quick-quote__actions">
                <button className="button button--primary" type="submit">
                  Enviar pedido
                </button>
                <NavLink
                  className="button quick-quote__complete"
                  to="/orcamento"
                >
                  Formulário completo
                </NavLink>
              </div>
            </form>
          </div>
        </section>
      )}

      <footer className="footer">
        <div className="container footer__intro">
          <h2>
            Painel de Comando: soluções industriais para comprar com segurança
          </h2>
          <strong>
            Painéis elétricos, acionamentos e automação com suporte técnico em
            um só lugar
          </strong>
          <p>
            Encontre soluções para partida, proteção e controle de motores
            industriais. Compare aplicações, consulte especificações e solicite
            apoio especializado antes de concluir seu pedido.
          </p>
          <nav aria-label="Atalhos do rodapé">
            <NavLink to="/produtos">Todos os produtos</NavLink>
            <NavLink to="/produtos?categoria=paineis">
              Painéis completos
            </NavLink>
            <NavLink to="/segmentos">Aplicações industriais</NavLink>
            <NavLink to="/projetos">Projetos especiais</NavLink>
            <NavLink to="/blog">Conteúdo técnico</NavLink>
            <NavLink to="/contato">Ajuda técnica</NavLink>
          </nav>
        </div>

        <div className="container footer__main">
          <div className="footer__brand">
            <img
              className="footer__logo"
              src="/brand/painel-de-comando-logo-v2.png"
              alt="Painel de Comando"
            />
            <p>
              Engenharia aplicada, compra assistida e suporte especializado para
              aplicações industriais.
            </p>
          </div>

          <section className="footer__column">
            <h3>Atendimento</h3>
            <a
              className="footer__contact"
              href={`https://wa.me/${companyConfig.whatsapp}`}
              target="_blank"
              rel="noreferrer"
            >
              <Icon name="whatsapp" size={18} />
              <span>
                <small>WhatsApp comercial</small>
                {companyConfig.whatsappLabel}
              </span>
            </a>
            <a
              className="footer__contact"
              href={`mailto:${companyConfig.email}`}
            >
              <Icon name="mail" size={18} />
              <span>
                <small>E-mail</small>
                {companyConfig.email}
              </span>
            </a>
            <NavLink className="footer__contact" to="/contato">
              <Icon name="headset" size={18} />
              <span>
                <small>Suporte técnico</small>Abrir atendimento
              </span>
            </NavLink>
          </section>

          <section className="footer__column">
            <h3>Produtos e suporte</h3>
            <NavLink to="/produtos">Todos os produtos</NavLink>
            <NavLink to="/produtos?categoria=paineis">
              Painéis elétricos
            </NavLink>
            <NavLink to="/produtos?categoria=inversor-de-frequencia">
              Inversores de frequência
            </NavLink>
            <NavLink to="/produtos?categoria=soft-starter">
              Soft starters
            </NavLink>
            <NavLink to="/projetos">Projetos realizados</NavLink>
            <NavLink to="/blog">Guias e conteúdos</NavLink>
          </section>

          <section className="footer__column">
            <h3>Para compradores</h3>
            <NavLink to="/trocas-e-devolucoes" onClick={scrollToTop}>
              Trocas e devoluções
            </NavLink>
            <NavLink to="/garantia" onClick={scrollToTop}>
              Garantia
            </NavLink>
            <NavLink to="/politica-de-privacidade" onClick={scrollToTop}>
              Privacidade
            </NavLink>
            <NavLink to="/contato" onClick={scrollToTop}>
              Central de ajuda
            </NavLink>
          </section>

          <section className="footer__column footer__column--offset">
            <h3>Institucional</h3>
            <NavLink to="/">Sobre o Painel CMD</NavLink>
            <NavLink to="/segmentos">Segmentos atendidos</NavLink>
            <NavLink to="/projetos">Engenharia e projetos</NavLink>
            <NavLink to="/contato">Fale conosco</NavLink>
          </section>

          <section className="footer__column">
            <h3>Guia de compras</h3>
            <NavLink to="/produtos">Como comprar</NavLink>
            <NavLink to="/orcamento">Compra assistida</NavLink>
            <NavLink to="/contato">Prazos e atendimento</NavLink>
            <NavLink to="/termos-de-uso">Condições comerciais</NavLink>
          </section>

          <section className="footer__column footer__trust">
            <h3>Compra e segurança</h3>
            <div>
              <Icon name="lock" size={18} />
              <span>
                <strong>Privacidade protegida</strong>
                <small>Tratamento de dados conforme a LGPD</small>
              </span>
            </div>
            <div>
              <Icon name="shield" size={18} />
              <span>
                <strong>Compra protegida</strong>
                <small>Atendimento antes e depois do pedido</small>
              </span>
            </div>
            <div>
              <Icon name="check" size={18} />
              <span>
                <strong>Especificação técnica</strong>
                <small>Confira as condições de cada produto</small>
              </span>
            </div>
          </section>
        </div>

        <div className="container footer__commerce">
          <div className="footer__payment-area">
            <strong>Pague com</strong>
            <div className="footer__payments">
              <span className="payment-card" aria-label="Mastercard">
                <PaymentIcon name="mastercard" />
              </span>
              <span className="payment-card" aria-label="Visa">
                <PaymentIcon name="visa" />
              </span>
              <span
                className="payment-card payment-card--brand-wide"
                aria-label="Hipercard"
              >
                <PaymentIcon name="hipercard" />
              </span>
              <span className="payment-card" aria-label="Elo">
                <PaymentIcon name="elo" />
              </span>
              <span
                className="payment-card payment-card--labeled payment-card--wide"
                aria-label="Pagamento parcelado"
              >
                <PaymentIcon name="installment" />
                <b>Pagamento parcelado</b>
              </span>
              <span className="payment-card" aria-label="Pix">
                <PaymentIcon name="pix" />
              </span>
              <span className="payment-card" aria-label="PayPal">
                <PaymentIcon name="paypal" />
              </span>
              <span
                className="payment-card payment-card--brand-wide"
                aria-label="Google Pay"
              >
                <PaymentIcon name="googlepay" />
              </span>
              <span
                className="payment-card payment-card--labeled"
                aria-label="Boleto"
              >
                <PaymentIcon name="boleto" />
                <b>Boleto</b>
              </span>
              <span
                className="payment-card payment-card--brand-wide"
                aria-label="PicPay"
              >
                <PaymentIcon name="picpay" />
              </span>
            </div>
            <small>Condições confirmadas no fechamento do pedido.</small>
          </div>
          <div className="footer__social">
            <strong>Siga a gente</strong>
            <div aria-label="Redes sociais a configurar">
              <span aria-label="Instagram">
                <Icon name="instagram" size={22} />
              </span>
              <span aria-label="Facebook">
                <Icon name="facebook" size={22} />
              </span>
              <span aria-label="TikTok">
                <Icon name="tiktok" size={22} />
              </span>
            </div>
          </div>
        </div>

        <div className="footer__legal">
          <div className="container">
            <span>
              <strong>{companyConfig.legalName}</strong> · CNPJ{" "}
              {companyConfig.cnpj} · {companyConfig.address} ·{" "}
              {companyConfig.deliveryNotice}
            </span>
            <nav>
              <NavLink to="/politica-de-privacidade">Privacidade</NavLink>
              <NavLink to="/termos-de-uso">Termos de uso</NavLink>
              <NavLink to="/admin/login">Área administrativa</NavLink>
              <span>
                © {new Date().getFullYear()} {companyConfig.name}
              </span>
            </nav>
          </div>
        </div>
      </footer>
    </>
  );
}
