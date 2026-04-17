# 📸 Fotógrafo SaaS Platform - Advanced Enterprise Architecture

Um ecossistema **ERP & E-commerce Fotográfico** desenhado rigorosamente sob os padrões corporativos de nuvem. Arquitetado em **Laravel 11+ / PHP 8.4**, esta plataforma transcende um simples gerenciador de estúdio: é um distribuidor automatizado e inteligente de ativos digitais, projetado para fotografias volumétricas, faturamento resiliente e infraestrutura elástica Multicloud.

---

## 🚀 Principais Features e Motores Internos

Este sistema divide-se em interfaces granulares e blindadas, servindo tanto o Estúdio Moderno quanto o Cliente Final através dos seguintes módulos core:

### 💼 1. O Motor Administrativo (Estúdio & Backoffice)
* **Gestão de Fotografias Massivas e Engine de Marcas d'Água:** Upload seguro, otimização visual (JPEG/WEBP) on-the-fly via filas assíncronas do Redis, aplicando _Watermarks_ (Marca D'água) que protegem a propriedade intelectual instantaneamente.
* **Componentes de UX Modernizados:** Todas as tabelas transacionais usam ordenação descendente forense global (`DESC`), painéis de estatísticas dinâmicas e modais polidos construídos paralelamente sobre Bootstrap 5 com transições ágeis.
* **Modelos Agregados de Pacotização:** O estúdio restringe a volumetria da Venda. Pacotes configuram "Fotos Inclusas" base do fotógrafo e regras complexas de "Valor Residual de Imagem Extra".
* **Auditoria de Conformidade Legal e Tracker (LGPD):** Operado sob o paradigma forense via integração profunda com o pacote institucional `owen-it/laravel-auditing`. Edições arbitrárias, retentativas de faturamento e modificações na taxonomia dos clientes são rigidamente trackeadas. Os "valores antigos" vs "valores novos" formam um histórico imutável (Com IP, Browser e Timestamp exato da fraude ou modificação).
* **Inspeção de Payload do Faturamento:** O "Visualizar Pedido" abriga uma Caixa Preta com todo histórico financeiro e registros brutos transacionais (JSON) provenientes da Operadora Financeira no formato de tentativas persistidas (`OrderAttempt`).

### 🎥 2. O Portal do Cliente (Varejo B2C)
* **Design Fluid & Dinâmico (SPA-Like):** Portal minimalista e luxuoso para o cliente interagir, selecionar imagens e fechar orçamentos diretamente.
* **Payment Multiplexer API-Agnostic:** Transações fluidas e descorrentizadas via Padrão Factory (`PaymentGatewayFactory`). Aceitação transparente de **PIX Dinâmico**, **Boleto Bancário** e **Cartões de Crédito**, desacoplado das operadoras (funciona via plug and play com Asaas, MercadoPago, etc). Nada de redirecionamentos frustrantes. Tudo é transparente (White-label).
* **AES-256 PCI-DSS Vault (Cofre Local Blindado):** Fator fundamental do Checkout: Cartões do cliente são retidos num cofre protegido via arquitetura `Illuminate\Support\Facades\Crypt`. O servidor **nunca mantém** CVVs abertos, expondo visualmente apenas pontas de checagem seguras (Masked PANs `**** **** **** 1234`).

### ☁️ 3. Motor Operacional Flexível (Multi-Cloud Lifecycle)
O grande gargalo do ramo fotográfico moderno é o espaço caro em Disco (HD). Resolvemos isso com o coração desta arquitetura: o **Workflow Cloud-Agnostic**.
* **Armazenamento Híbrido (Hot x Cold):** O servidor armazena provisoriamente suas fotos cruas gigantes (RAWs). Com um clique manual "Arquivar", os arquivos de 50MB são disparados assincronamente (Jobs) para a **Google Drive API** ou baldes **Amazon S3 / R2**. As *thumbnails públicas* rápidas, contudo, permanecem no disco SSD principal garantindo o máximo de FPS na navegação.
* **Native OAuth 2.0 Engine:** O painel elimina a necessidade do Playground Developer ao integrar o fluxo primário OAuth 2.0. O fotógrafo aperta um botão, dá o consentimento no Gmail, e nosso CallBack Handler interno gera o *Refresh Token Vitalício*, ancorando silenciosamente a API de 15GB nativa sem stress.
* **Recompilador Oculto B2C (Downsync Dinâmico):** Se um cliente baixar ou encomendar imagens de uma sessão antiga que "já habita na Nuvem", o Checkout faz a engenharia reversa. O Laravel em processamento resgata o _Cold Storage_ dos baldes (Downsync provisório), funde o `Ensaio_Completo_X.zip`, sinaliza via WebSocket/Controller que a transação foi enlaçada e cospe a RAM/Disco temporário apagando-os. Retenha apenas ZIPs finalizados.
* **Lixeira Purgatória Programada (Garbage Collector):** Para controlar a escalada física residual dos `ZIPs`, orquestramos no kernel um cron Job rígido (`php artisan storage:clean-zips`). Todos os dias, de madrugada, varre os encerramentos ephemerais apagando sem piedade compilações acima de 30 dias.

---

## 📦 Infraestrutura, Deploy e Escalabilidade (Produção Linux)

Sistema alicerçado sobre **Docker Containers**. Escala sem falhas tanto em Servidores Privados Virtuais (VPS), Bare Metal ou Orquestradores como o Kubernetes.

### Passo 1. Configurando o Terreno
Você precisará do servidor limpo Ubuntu 22.04 LTS (ou afim) e das raízes instaladas:
* [Docker Engine Restrito](https://docs.docker.com/engine/install/) e [Docker Compose V2](https://docs.docker.com/compose/install/)
* Portas de Borda Abertas no UFW (`80`, `443` e se aplicável `22` para o seu SSH Seguro).

Clone o ecossistema na diretória nativa `/var/www/fotografo`:
```bash
cp .env.example .env
nano .env # (Substitua as Secret Keys dos Hubs - Mude APP_ENV=production, APP_DEBUG=false)
```

### Passo 2. Orquestração Nuclear de Containeres
Suba as docas e inicialize a malha estrutural. O Composer e o Node isolados irão trabalhar em conjunto e construirão todos os contornos da aplicação sem colidir com versões legadas do seu Hospedeiro.

```bash
docker-compose up -d --build

# Geração Específica de Encriptação e Componentes Backend
docker exec fotografo_app php artisan key:generate
docker exec fotografo_app composer install --optimize-autoloader --no-dev

# Criação Limpa das Tabelas Nativas (Incluindo Laravel Audits Base)
docker exec fotografo_app sh -c "php artisan migrate --force && php artisan db:seed --force"

# Expondo o Disco de Galerias/ZIPs Públicos 
docker exec fotografo_app php artisan storage:link

# Compilação Ágil do Frontend Asset Pipeline (Vite / CSS / TS)
docker exec fotografo_app sh -c "npm ci && npm run build"
```

### Passo 3. Aceleração Fóton e Permissões Seguras (Host Machine)
Deixe tudo selado ao Linux de Segurança Pura (Para o manipulador do Webserver - `www-data`):
```bash
# Permissões Mínimas Absolutas no Cache do Laravel
sudo chown -R 33:33 storage bootstrap/cache public/build
sudo chmod -R 775 storage bootstrap/cache
```
Também no Host Machine ou via Painel, instale o **SupervisorDaemon** orquestrando em background a vida inteira do motor fotográfico para não estrangular requisições PHP de timeout curto (Zips e Thumbnails são renderizados em segundo plano!).

A regra mestre do cron em seu Linux deve ser mapeada:
```bash
* * * * * cd /var/www/fotografo && docker exec fotografo_app php artisan schedule:run >> /dev/null 2>&1
```

### Passo 4. Tráfego Edge & Reverse Proxy de Segurança HTTPS (Nginx local ou Caddy)
Você é o controlador de rotas. O Docker sobe na porta *X*. Configure seu Bloco Servidor (Túnel Nginx ou Load Balancer CloudFlare) focando em **Forçar Certificações Criptografadas TSL (HTTPS)**. Todo e qualquer roteiro AES-256 e de Cobrança Transparente exige restrição de SSL Ativa para preservar Cifras e Dados Cartográficos sensíveis do ecossistema.

---

💼 **Arquiteto da Ferramenta**: O ecossistema nasceu como uma sinfonia absoluta mesclando alto desempenho de rede de imagens robustas (GDrive/Amazon AWS), processamento local de vetores complexos, cofre forte PCI minimalista, auditoria nativa persistente e pagamentos contínuos. Construído e polido para orações em alta disponibilidade de Estúdios de Nível A.
