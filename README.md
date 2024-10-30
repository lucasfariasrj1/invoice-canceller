# Invoice Canceller - Módulo WHMCS

Este módulo para WHMCS permite cancelar faturas não pagas e seus pedidos relacionados com base em um período selecionado de dias após o vencimento.

## Funcionalidades

- Cancela faturas com status "Não Paga" (Unpaid) que ultrapassaram um período selecionado desde o vencimento.
- Cancela automaticamente os pedidos relacionados às faturas não pagas.
- Exibe uma lista de faturas e pedidos que serão cancelados antes da confirmação.
- Interface simples e fácil de usar dentro do painel administrativo do WHMCS.

## Instalação

1. Baixe ou clone o repositório para o diretório `modules/addons/` do seu WHMCS:

   
   ```bash
   git clone https://github.com/lucasfariasrj1/invoice-canceller.git
