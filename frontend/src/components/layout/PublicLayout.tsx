import { FormEvent, useEffect, useState } from "react";
import { Link, NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { companyConfig } from "../../constants/company";
import { Icon } from "../common/Icon";
import { PaymentIcon } from "../common/PaymentIcon";
import { ShippingCalculator } from "../common/ShippingCalculator";
import { useAuth } from "../../auth/AuthContext";

const productNavigation = [
  {
    label: "Estrela-Triângulo",
    to: "/produtos?linha=estrela-triangulo",
    sections: [
      { title: "Econômico", to: "/produtos?linha=estrela-triangulo&subcategoria=economico", voltages: ["220V", "380V"] },
      { title: "Padrão", to: "/produtos?linha=estrela-triangulo&subcategoria=padrao", voltages: ["220V", "380V"] },
      { title: "Com Amperímetro", to: "/produtos?linha=estrela-triangulo&subcategoria=com-amperimetro", voltages: ["220V", "380V"] },
    ],
    featured: {
      name: "Painel Estrela-Triângulo Econômico",
      image: "/images/painel-estrela-triangulo-15cv-principal.png",
      to: "/produtos/painel-estrela-triangulo",
    },
  },
  {
    label: "Soft Starter",
    to: "/produtos?linha=soft-starter",
    sections: [
      {
        title: "SSW07 30A",
        to: "/produtos?linha=soft-starter&busca=SSW07%2030A",
        variants: [
          { label: "220V · 10CV", to: "/produtos/painel-soft-starter-ssw07-30a-10cv-220v" },
          { label: "380V · 20CV", to: "/produtos/painel-soft-starter-ssw07-30a-20cv-380v" },
        ],
      },
      {
        title: "SSW07 45A",
        to: "/produtos?linha=soft-starter&busca=SSW07%2045A",
        variants: [
          { label: "220V · 15CV", to: "/produtos/painel-soft-starter-ssw07-45a-15cv-220v" },
          { label: "380V · 30CV", to: "/produtos/painel-soft-starter-ssw07-45a-30cv-380v" },
        ],
      },
      {
        title: "SSW07 61A",
        to: "/produtos?linha=soft-starter&busca=SSW07%2061A",
        variants: [
          { label: "220V · 20CV", to: "/produtos/painel-soft-starter-ssw07-61a-20cv-220v" },
          { label: "380V · 40CV", to: "/produtos/painel-soft-starter-ssw07-61a-40cv-380v" },
        ],
      },
      {
        title: "SSW07 85A",
        to: "/produtos?linha=soft-starter&busca=SSW07%2085A",
        variants: [
          { label: "220V · 30CV", to: "/produtos/painel-soft-starter-ssw07-85a-30cv-220v" },
          { label: "380V · 30CV", to: "/produtos/painel-soft-starter-ssw07-85a-30cv-380v" },
        ],
      },
      {
        title: "Para Irrigação",
        to: "/produtos?linha=soft-starter&busca=irriga%C3%A7%C3%A3o",
        variants: [
          { label: "220V · 50CV", to: "/produtos/painel-irrigacao-soft-starter-ssw07-50cv-220v" },
          { label: "220V · 60CV", to: "/produtos/painel-irrigacao-soft-starter-ssw07-60cv-220v" },
          { label: "220V · 125CV", to: "/produtos/painel-irrigacao-soft-starter-ssw07-125cv-220v" },
        ],
      },
    ],
    featured: {
      name: "Painel com Soft Starter",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?categoria=soft-starter",
    },
  },
  {
    label: "Inversor de Frequência",
    to: "/produtos?linha=inversor-de-frequencia",
    sections: [{ title: "Painel com Inversor de Frequência", to: "/produtos?linha=inversor-de-frequencia", voltages: ["220V", "380V"] }],
    featured: {
      name: "Painel com Inversor de Frequência",
      image: "/images/hero-painel-industrial-v2.jpg",
      to: "/produtos?categoria=inversor-de-frequencia",
    },
  },
  {
    label: "Bomba de Incêndio",
    to: "/produtos?linha=bomba-de-incendio",
    sections: [
      { title: "Econômico", to: "/produtos?linha=bomba-de-incendio", voltages: ["220V", "380V"] },
      { title: "Padrão", to: "/produtos?linha=bomba-de-incendio", voltages: ["220V", "380V"] },
      { title: "Caixa Vermelha", to: "/produtos?linha=bomba-de-incendio", voltages: ["220V", "380V"] },
      { title: "Principal e Jockey", to: "/produtos?linha=bomba-de-incendio", voltages: ["220V", "380V"] },
    ],
    featured: {
      name: "Painel para Bomba de Incêndio",
      image: "/images/hero-painel-comando-poster.jpg",
      to: "/produtos?categoria=incendio",
    },
  },
  {
    label: "Irrigação",
    to: "/produtos?linha=irrigacao",
    sections: [{ title: "Painel para Irrigação", to: "/produtos?linha=irrigacao", voltages: ["220V", "380V"] }],
    featured: {
      name: "Painel para Irrigação",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?aplicacao=irrigacao",
    },
  },
  {
    label: "Revezamento de Bombas",
    to: "/produtos?linha=revezamento",
    sections: [{ title: "Painel para Revezamento de Bombas", to: "/produtos?linha=revezamento", voltages: ["220V", "380V"] }],
    featured: {
      name: "Painel para Revezamento de Bombas",
      image: "/images/montagem-painel-industrial-v2.jpg",
      to: "/produtos?categoria=revezamento",
    },
  },
  {
    label: "Todos os Produtos",
    to: "/produtos",
    sections: [],
    featured: undefined,
  },
  {
    label: "Mais Categorias",
    to: "/produtos?categoria=mais-categorias",
    sections: [
      { title: "Painel para Estação Elevatória", to: "/produtos?busca=Painel%20para%20Esta%C3%A7%C3%A3o%20Elevat%C3%B3ria" },
      { title: "Painel de Partida Direta", to: "/produtos?busca=Painel%20de%20Partida%20Direta" },
      { title: "Painel com Tomadas Industriais", to: "/produtos?busca=Painel%20com%20Tomadas%20Industriais" },
      { title: "Painel para Canteiro de Obras", to: "/produtos?busca=Painel%20para%20Canteiro%20de%20Obras" },
      { title: "Acessórios para Painéis Elétricos", to: "/produtos?busca=Acess%C3%B3rios%20para%20Pain%C3%A9is%20El%C3%A9tricos" },
      { title: "Quadro de Comando para Bomba de Poço", to: "/produtos?busca=Quadro%20de%20Comando%20para%20Bomba%20de%20Po%C3%A7o" },
    ],
    featured: undefined,
  },
];

export function PublicLayout() {
  const { user, loading: authLoading } = useAuth();
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState("");
  const [quoteProduct, setQuoteProduct] = useState("Painel Estrela-Triângulo");
  const [quotePhone, setQuotePhone] = useState("");
  const [quoteSummary, setQuoteSummary] = useState("");
  const navigate = useNavigate();
  const { pathname, search: locationSearch } = useLocation();
  const selectedLine = new URLSearchParams(locationSearch).get("linha");
  const activeProductGroup = pathname === "/produtos"
    ? productNavigation.find((group) => group.to === "/produtos"
      ? selectedLine === null
      : new URLSearchParams(group.to.split("?")[1]).get("linha") === selectedLine)
    : undefined;

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
            className={open ? "menu-button menu-button--open" : "menu-button"}
            type="button"
            aria-expanded={open}
            aria-controls="store-menu"
            aria-label={open ? "Fechar menu" : "Abrir menu"}
            onClick={() => {
              setOpen(!open);
            }}
          >
            <span className="menu-button__icon" aria-hidden="true">
              <span />
              <span />
              <span />
            </span>
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
                }}
              >
                Início
              </NavLink>
              {productNavigation.map((group) => (
                  <div className="product-nav__item" key={group.label}>
                    <Link
                      className={activeProductGroup?.label === group.label ? "product-nav__trigger active" : "product-nav__trigger"}
                      to={group.to}
                      onClick={() => {
                        setOpen(false);
                      }}
                    >
                      {group.label}
                    </Link>
                  </div>
                ))}
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
            <NavLink to="/painel-de-comando-eletrico">Painel de comando elétrico</NavLink>
            <NavLink to="/paineis-eletricos-sob-medida">Painéis sob medida</NavLink>
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
            <NavLink to="/montagem-de-paineis-eletricos-industriais">Montagem de painéis</NavLink>
            <NavLink to="/painel-de-comando-sorocaba">Atendimento em Sorocaba</NavLink>
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
