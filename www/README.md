# 📸 Fotógrafo SaaS Platform

Um sistema enterprise unificado (ERP + E-commerce Fotográfico Direto) desenhado e arquitetado em **Laravel 11+ / PHP 8.4** para profissionais de fotografia. A plataforma permite estúdio escalar suas vendas automatizando galerias de prévias, precificações complexas, integração desacoplada com gateways de pagamento em tempo real, proteção autoral de imagens e relatórios forenses minuciosos.

---

## 🚀 Principais Features e Arquitetura

O sistema é dividido massivamente em dois mundos integrados:

### 💼 Painel Administrativo (O Estúdio)
* **Gestão de Fotografias e Galerias:** Sistema profissional de uploads rápidos com conversão automática, marca d'água em tempo real (proteção autoral), e painel de inspeção limpo baseado em Bootstrap 5 Modernizado.
* **Tabelas Organizadas Globalmente:** Todas as grids transacionais do sistema organizam lógicas globais descendentes (ID Decrescente). 
* **Modelos Flexíveis de Pacotes:** Gestão granular onde pacotes delimitam _Fotos Inclusas_, regras de _Valor de Foto Extra_ e associação automática as vendas.
* **Auditoria de Operações Globais (Forensic Box):** Todo o painel conta nativamente com o Tracker Institucional ([owen-it/laravel-auditing]). Toda ação arbitrária sobre atributos críticos de Clientes, Pacotes, Cobranças ou Fotos fica carimbada para auditorias (Valores Antigos x Valores Novos e dados de IP/Navegador/Horário do responsável).
* **Inspeção de Vendas & Estornos Inteligentes:** O "Visualizar Pedido" abriga a caixa preta da API de Recebimentos; Retentativas manuais de boletos/pix via interface sem ferir segurança PCI e ações de 'Cancelamento' orquestram chamadas de REST/Refunds diretas ao Gateway associado.

### 🎥 Portal B2C (O Cliente)
* **Interface Dinâmica:** Seu cliente mergulha num portal minimalista para escolher, de casa, como no e-commerce, as imagens que gostaria de revelar ou comprar fisicamente.
* **Checkout API-Agnostic (Transparente):** Integrador de Caixa robusto que abstrai o pagamento através do Padrão Factory (`PaymentGatewayFactory`). Aceitação de redes flexíveis via PIX, Boletos (dinâmicos) ou Cartões de Crédito autorizados sem redirecionamentos de página obscuros.
* **Cofre Segurado AES-256:** Mecanismo seguro em banco SQL (AES-256) validando `UserCards` mascarados. O servidor jamais retém logs vulneráveis de PIN/CVVs abertos do seu cliente sob a exigência PCI-DSS.
* **ZipEngine:** Quando a transação de compra é selada pelo banco, fotos cruas sem marca d'água são compactadas paralelamente numa prateleira efêmera na nuvem para o botão de 'Download' natural do seu cliente. Nenhuma mão humana envolvida na liberação.
* **Formulários Resilientes ao Cache:** Resoluções dinâmicas injetadas na Interface (Vue/Vanilla JS), evitando telas sem formulário para clientes não-faturados via dataset intermitente.

---

## 📦 Empacotamento e Subida para a Nuvem (Produção)

Este projeto usa contêineres Docker, o que significa absoluta portabilidade em Servidores Virtuais Linux (`Ubuntu 22.04 LTS` ou `Debian`). 

Siga essas etapas meticulosamente no seu Servidor Dedicado ou VPS:

### 1. Pré-Requisitos no Servidor Físico/Virtual
Você precisa apenas ter rodando no terminal as raízes do Docker:
* [Docker Server Engine](https://docs.docker.com/engine/install/)
* [Docker Compose V2](https://docs.docker.com/compose/install/)
* Porta `80`, `443` destrancadas no Firewall nativo (UFW).

### 2. Preparando a Plantaforma na Nuvem
Transfira ou Clone este repositório raiz na diretória mãe do servidor de nuvem (Ex: `/var/www/fotografo`). Logo após, conceda as permissões críticas da web:
```bash
# Na Raiz da Pasta do Repositório do Servidor
cp .env.example .env

# Confirme as variáveis no VIM ou NANO
nano .env 
```
*Atenção no `.env`: Ajuste suas chaves reais do MercadoPago/Asaas, coloque `APP_ENV=production` e `APP_DEBUG=false`*.

### 3. Orquestração, Migração e Builds
Construiremos todos os ambientes vitais, migraremos sua base segura pro MySQL 8 e compilaremos o front-end Typescript nativamente de dentro do Container isolado.

Subindo a máquina no container:
```bash
docker-compose up -d --build
```
Instalando pacotes massivos na arquitetura do Composer e compilando a casca:
```bash
# Gerando a Chave de Segurança Exclusiva Criptográfica
docker exec fotografo_app php artisan key:generate

# Pacotes do Backend
docker exec fotografo_app composer install --optimize-autoloader --no-dev

# Migrações Estruturadas (Geração do Banco MySQL incluindo a Tabela Auditoria)
docker exec fotografo_app sh -c "php artisan migrate --force && php artisan db:seed --force"

# Expondo o Drive de Fotos/Galerias à rede Aberta
docker exec fotografo_app php artisan storage:link

# Empacotando e Minificando Asset Pipeline (Vite.JS / Vue / Tailwind / Bootstrap)
docker exec fotografo_app sh -c "npm ci && npm run build"
```

### 4. Permissões Linux Cruciais de Host
A Web Server (Container Worker) usa tipicamente `www-data`. Proteja para não sofrer acessos negados no upload temporário das sessões.
```bash
sudo chown -R 33:33 storage bootstrap/cache public/build
sudo chmod -R 775 storage bootstrap/cache
```

### 5. Trabalhadores de Fundo em Produção (Gateways de Filas)
Seu sistema exige receber Pings e Webhooks das Operadoras Assíncronos (Avisos de "Pix Pago" do lado do gateway, Geração de Zips). É mandatória a execução de um processo Worker em segundo plano no Servidor para esvaziar filas do Redis ou do Banco.

Não rode manualment. Use um gerente daemon como o `Supervisor` nativo do Ubuntu no hospedeiro para monitorar ou configure no Docker File do Laravel para ter um processo amarrado `php artisan queue:work --tries=3 --timeout=90`. O motor fotográfico estagnará com a galeria na mão do cliente sem esse motor em Background.

### 6. Reverse Proxy Frontend (Certificações HTTPs Seguras - NGINX)
Bloqueie acesso do Docker exposto bruto. Crie num `nginx` Host local um Server Block mapeando e afunilando chamadas de domínio e de Criptografia TLS Let's Encrypt batendo de frente para sua rota interna `http://127.0.0.1:8000`.

**NUNCA utilize a malha sem Certificado SSL Padrão de Comércio (HTTPS)**, dados que os cartões e cofre AES no código de Checkout Controller não podem transitar inseguros na View HTML até suas controladoras.

---

### Observações do Faturamento / Manutenção
Logs do app moram em `storage/logs/laravel.log`. Logs da infraestrutura estão em `docker-compose logs -f app`. No ambiente de Produção Laravel, erros são ofuscados visualmente do usuário na camada VUE/Blade, você deve inspecionar sempre o painel corporativo ou ligar com sistemas remotos de debug (ex: sentry, flare).

Arquitetado para ser o elo perfeito entre Arte e Gestão de Cobranças.
Desenvolvido em Laravel.
