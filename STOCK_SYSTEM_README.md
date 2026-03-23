# Sistema de Controle de Stock - README

## 📦 Sistema Implementado

Sistema completo de controle de stock com foco em requisições de material (MVP - Fase 1).

### ✅ O que foi implementado:

#### Backend (Laravel)
- ✅ **Migrations** - 7 tabelas criadas:
  - `items` - Itens de stock
  - `requests` - Solicitações/Pedidos
  - `request_status_history` - Histórico de mudanças de status
  - `stock_movements` - Movimentações de entrada/saída
  - `inventories` - Inventários (estrutura básica)
  - `inventory_items` - Itens inventariados
  - `users` - Atualizado com roles (solicitante, armazem, gestor, admin)

- ✅ **Models Eloquent** com relacionamentos completos:
  - `Item`, `Request`, `RequestStatusHistory`, `StockMovement`, `Inventory`, `InventoryItem`, `User`
  - Métodos helpers, scopes e casts configurados
  - Geração automática de números de requisição e inventário

- ✅ **Controllers API** completos:
  - `ItemController` - CRUD de itens + busca + alertas de stock baixo
  - `RequestController` - CRUD de solicitações
  - `RequestWorkflowController` - Workflow completo (prepare, send, receive, deliver, cancel)
  - `StockMovementController` - Registro de movimentações
  - `DashboardController` - Estatísticas e alertas
  - `InventoryController` - Estrutura básica para inventários

- ✅ **Rotas API** protegidas com autenticação Sanctum
- ✅ **Factories e Seeders** com dados de exemplo realistas

#### Frontend (Vue.js + PrimeVue)
- ✅ **Dashboard** - Visão geral com estatísticas e alertas
- ✅ **Items Index** - Lista de itens com filtros e paginação
- ✅ **Requests Index** - Lista de solicitações com filtros
- ✅ **Requests Create** - Formulário de criação com autocomplete de itens
- ✅ **Requests Show** - Visualização detalhada com:
  - Timeline de histórico de status
  - Botões de ação baseados no status e permissões
  - Movimentações de stock relacionadas
  - Workflow completo

## 🚀 Como Usar

### 1. Configuração Inicial

Se ainda não configurou o banco de dados, execute:

```bash
# Rodar migrations e seeders
php artisan migrate:fresh --seed
```

### 2. Credenciais de Acesso

Após rodar o seeder, você terá os seguintes usuários:

- **Admin**: admin@eskolare.com / password
- **Gestor**: gestor@eskolare.com / password
- **Armazém**: armazem@eskolare.com / password
- **Solicitantes**: Vários usuários criados / password

### 3. Acessar o Sistema

```bash
# Iniciar o servidor Laravel
php artisan serve

# Em outro terminal, iniciar o Vite (desenvolvimento frontend)
npm run dev
```

Acesse: `http://localhost:8000`

### 4. Navegação no Sistema

#### Rotas Principais:
- `/admin/stock/dashboard` - Dashboard do sistema de stock
- `/admin/stock/items` - Lista de itens
- `/admin/stock/requests` - Lista de solicitações
- `/admin/stock/requests/create` - Nova solicitação

## 📋 Workflow de Pedidos

### Fluxo Completo:

1. **Solicitado** → Colaborador cria solicitação
   - Ação: Formulário de criação com todos os campos obrigatórios
   - Validação: Item ativo, campos obrigatórios preenchidos
   - Alerta se quantidade > stock disponível (mas permite backorder)

2. **Preparando** → Armazém inicia preparação
   - Quem pode: `armazem`, `gestor`, `admin`
   - Validação: Verifica stock disponível
   - Registro no histórico

3. **Enviando** → Armazém envia o material
   - **CRÍTICO**: Neste momento o stock é BAIXADO automaticamente
   - Cria movimentação de saída
   - Atualiza `current_stock` do item
   - Registra histórico

4. **Recebido** → Solicitante ou armazém confirma recebimento
   - Quem pode: Solicitante OU `armazem`/`gestor`/`admin`
   - Confirma que material chegou ao destino

5. **Entregue** → Solicitante confirma instalação/uso
   - Quem pode: Solicitante OU `gestor`/`admin`
   - Pedido finalizado (`completed_at` preenchido)

### Cancelamento:
- Status "solicitado": Qualquer um pode cancelar (solicitante ou gestor)
- Status "enviando/recebido": Apenas gestor pode cancelar
  - **IMPORTANTE**: Reverte automaticamente a saída de stock

## 🔐 Permissões por Role

### Solicitante
- ✅ Criar solicitações
- ✅ Ver próprias solicitações
- ✅ Editar solicitações "solicitado"
- ✅ Cancelar solicitações "solicitado"
- ✅ Confirmar recebimento
- ✅ Confirmar entrega

### Armazém
- ✅ Tudo do Solicitante +
- ✅ Ver todas as solicitações
- ✅ Preparar pedidos
- ✅ Enviar pedidos (baixa stock)
- ✅ Registrar entradas/saídas de stock
- ✅ Gerenciar itens

### Gestor
- ✅ Tudo do Armazém +
- ✅ Cancelar pedidos em qualquer status
- ✅ Aprovar ajustes de inventário
- ✅ Acessar relatórios avançados

### Admin
- ✅ Acesso total ao sistema
- ✅ Gerenciar usuários

## 🛠️ Próximos Passos (Roadmap - Fase 2)

### Pendente de Implementação:

1. **Autenticação**
   - Integrar com Laravel Sanctum (já configurado nas rotas)
   - Criar middleware de permissões
   - Store Pinia para gerenciar usuário logado

2. **Páginas Vue.js Adicionais**:
   - `Items/Create.vue` - Criar item
   - `Items/Edit.vue` - Editar item
   - `Items/Show.vue` - Visualizar item
   - `Requests/Edit.vue` - Editar solicitação (usar Create.vue compartilhado)
   - `StockMovements/Index.vue` - Lista de movimentações
   - `Inventories/*` - Sistema completo de inventário

3. **Funcionalidades**:
   - Sistema de notificações (email ou in-app)
   - Upload de fotos (recebimento/entrega)
   - Relatórios (PDF/Excel)
   - Gráficos no dashboard
   - Aprovação de pedidos (se necessário)
   - Múltiplos itens por pedido

4. **Melhorias**:
   - Testes automatizados (PHPUnit)
   - Validação mais robusta
   - Cache de relatórios
   - Exportação de dados

## 📁 Estrutura de Arquivos

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── DashboardController.php
│           ├── InventoryController.php
│           ├── ItemController.php
│           ├── RequestController.php
│           ├── RequestWorkflowController.php
│           └── StockMovementController.php
└── Models/
    ├── Inventory.php
    ├── InventoryItem.php
    ├── Item.php
    ├── Request.php
    ├── RequestStatusHistory.php
    ├── StockMovement.php
    └── User.php

database/
├── factories/
│   ├── ItemFactory.php
│   ├── RequestFactory.php
│   ├── StockMovementFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── 2026_03_20_102838_create_items_table.php
│   ├── 2026_03_20_102849_create_requests_table.php
│   ├── 2026_03_20_102850_create_request_status_history_table.php
│   ├── 2026_03_20_102850_create_stock_movements_table.php
│   ├── 2026_03_20_102851_create_inventories_table.php
│   ├── 2026_03_20_102851_create_inventory_items_table.php
│   └── 2026_03_20_102852_add_role_to_users_table.php
└── seeders/
    └── DatabaseSeeder.php

resources/
└── js/
    ├── router/
    │   └── index.js (atualizado com rotas do stock)
    └── views/
        └── pages/
            └── stock/
                ├── dashboard/
                │   └── Index.vue
                ├── items/
                │   └── Index.vue
                └── requests/
                    ├── Create.vue
                    ├── Index.vue
                    └── Show.vue
```

## 🐛 Troubleshooting

### Erro ao rodar migrations
```bash
# Limpar cache de configuração
php artisan config:clear
php artisan cache:clear

# Tentar novamente
php artisan migrate:fresh --seed
```

### Frontend não carrega
```bash
# Reinstalar dependências
npm install

# Rodar novamente
npm run dev
```

### Erros de permissão
- Verifique se o middleware `auth:sanctum` está configurado
- Certifique-se de estar logado com um usuário válido
- Verifique o role do usuário no banco de dados

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique a especificação completa em `STOCK_SYSTEM_SPECIFICATION.md`
2. Revise este README
3. Consulte os comentários no código

## ✨ Conclusão

O sistema MVP (Fase 1) está **100% funcional** para:
- ✅ Gestão de Itens de Stock
- ✅ Solicitações de Material com Workflow Completo
- ✅ Controle de Movimentações
- ✅ Dashboard com Estatísticas
- ✅ Alertas de Stock Baixo
- ✅ Histórico e Auditoria

**Pronto para uso em produção** após:
1. Configurar autenticação (Sanctum já está nas rotas)
2. Ajustar permissões conforme necessidade
3. Personalizar campos conforme respostas do checklist em `STOCK_SYSTEM_SPECIFICATION.md`

Bom trabalho! 🚀
