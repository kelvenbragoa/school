# Sistema de Controle de Stock - Especificação Completa

## A) REQUISITOS FUNCIONAIS

### RF01 - Gestão de Itens de Stock
- RF01.1: Cadastrar item (código, nome, descrição, unidade medida, categoria, localização, stock mínimo, stock máximo)
- RF01.2: Editar informações do item
- RF01.3: Visualizar detalhes do item (incluindo saldo atual, histórico de movimentações)
- RF01.4: Inativar/ativar item
- RF01.5: Listar itens com filtros (código, nome, categoria, status)

### RF02 - Solicitação de Material (Pedidos)
- RF02.1: Criar solicitação com campos obrigatórios:
  - Item code (seleção de item cadastrado)
  - Referência (texto livre, ex.: número de documento relacionado)
  - Work Order (número da ordem de trabalho)
  - Qty (quantidade solicitada)
  - ID Equipamento (identificador do equipamento)
  - Motivo de Substituição (Desgaste, Erro de Fabrico, Outro)
  - Solicitante (automaticamente preenchido com usuário logado)
  - Observações (opcional)
- RF02.2: Visualizar lista de solicitações próprias
- RF02.3: Visualizar lista de todas as solicitações (para usuários autorizados)
- RF02.4: Visualizar detalhes completos de uma solicitação
- RF02.5: Editar solicitação apenas no status "solicitado" (pelo solicitante)
- RF02.6: Cancelar solicitação apenas no status "solicitado" (pelo solicitante)

### RF03 - Workflow de Pedidos
- RF03.1: Alterar status de "solicitado" para "preparando" (perfil armazém)
  - Validar disponibilidade de stock
  - Registrar usuário e data/hora da mudança
  - Permitir adicionar observações
- RF03.2: Alterar status de "preparando" para "enviando" (perfil armazém)
  - Registrar saída de stock neste momento (baixa do inventário)
  - Gerar número de tracking/protocolo
  - Registrar usuário e data/hora da mudança
- RF03.3: Alterar status de "enviando" para "recebido" (solicitante ou armazém)
  - Confirmar recebimento
  - Registrar usuário e data/hora da mudança
- RF03.4: Alterar status de "recebido" para "entregue" (solicitante)
  - Confirmar entrega final no local/equipamento
  - Registrar usuário e data/hora da mudança
  - Opcional: anexar foto/comprovante
- RF03.5: Histórico completo de mudanças de status (audit trail)

### RF04 - Gestão de Entradas de Stock
- RF04.1: Registrar entrada por compra
- RF04.2: Registrar entrada por devolução
- RF04.3: Registrar entrada por ajuste (correção)
- RF04.4: Campos obrigatórios para entrada:
  - Item
  - Quantidade
  - Tipo de entrada (Compra, Devolução, Ajuste)
  - Documento/Referência
  - Fornecedor (se compra)
  - Data de entrada
  - Localização de armazenamento
  - Usuário responsável
  - Observações
- RF04.5: Atualizar saldo de stock automaticamente
- RF04.6: Listar histórico de entradas

### RF05 - Gestão de Saídas de Stock
- RF05.1: Saída automática vinculada a pedido aprovado
- RF05.2: Registrar saída avulsa (consumo interno, ajuste)
- RF05.3: Campos obrigatórios para saída:
  - Item
  - Quantidade
  - Tipo de saída (Pedido, Consumo Interno, Ajuste, Perda)
  - Referência (ID do pedido ou documento)
  - Destino/Centro de Custo
  - Data de saída
  - Usuário responsável
  - Observações
- RF05.4: Atualizar saldo de stock automaticamente
- RF05.5: Listar histórico de saídas

### RF06 - Inventário e Contagem
- RF06.1: Criar sessão de inventário (data, responsável, localização)
- RF06.2: Registrar contagem física por item
- RF06.3: Calcular divergência (contado vs. sistema)
- RF06.4: Aprovar/rejeitar ajuste de inventário
- RF06.5: Gerar movimentação de ajuste (entrada ou saída)
- RF06.6: Registrar histórico de inventários
- RF06.7: Relatório de divergências

### RF07 - Relatórios e Dashboards
- RF07.1: Dashboard com indicadores:
  - Total de pedidos por status
  - Itens abaixo do stock mínimo (alertas)
  - Movimentações do dia/semana/mês
  - Top 10 itens mais solicitados
- RF07.2: Relatório de movimentações por período
- RF07.3: Relatório de stock atual (todos os itens)
- RF07.4: Relatório de pedidos por solicitante
- RF07.5: Relatório de tempo médio de atendimento de pedidos

### RF08 - Gestão de Usuários e Permissões
- RF08.1: Perfis de acesso:
  - **Solicitante**: pode criar solicitações, visualizar próprias solicitações, confirmar recebimento
  - **Armazém**: pode gerenciar stock, processar pedidos, realizar inventários
  - **Gestor**: pode visualizar relatórios, aprovar ajustes, gerenciar usuários
  - **Admin**: acesso total ao sistema
- RF08.2: Atribuir/remover perfis de usuários
- RF08.3: Histórico de ações por usuário (audit log)

## B) REQUISITOS NÃO-FUNCIONAIS

### RNF01 - Auditoria e Rastreabilidade
- Todas as operações críticas devem ser registradas (quem, quando, o quê)
- Histórico de mudanças não pode ser editado ou deletado
- Backup diário de dados

### RNF02 - Segurança
- Autenticação obrigatória (Laravel Sanctum)
- Controle de acesso baseado em perfis (gates/policies)
- Proteção contra SQL Injection, XSS, CSRF
- Senhas criptografadas (bcrypt)
- Sessões seguras

### RNF03 - Performance
- Listagens com paginação (máximo 50 registros por página)
- Índices no banco de dados para consultas frequentes
- Cache de relatórios pesados (Redis, se disponível)
- Resposta de API < 500ms para operações comuns

### RNF04 - Usabilidade
- Interface responsiva (mobile-friendly)
- Feedback visual claro para ações (success, error, loading)
- Validação de formulários em tempo real
- Campos de seleção com autocomplete
- Mensagens de erro claras e em português

### RNF05 - Manutenibilidade
- Código seguindo PSR-12 (Laravel Pint)
- Testes automatizados (PHPUnit)
- Documentação de API (endpoints)
- Versionamento de código (Git)

### RNF06 - Disponibilidade
- Sistema disponível 24/7
- Tempo de inatividade planejado < 2h/mês
- Log centralizado de erros

## C) MODELO DE DADOS

### Tabela: items
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | Identificador único |
| code | string(50) UNIQUE | Código do item |
| name | string(255) | Nome do item |
| description | text | Descrição detalhada |
| unit | string(20) | Unidade de medida (un, kg, m, etc.) |
| category | string(100) | Categoria do item |
| location | string(100) | Localização no armazém |
| min_stock | decimal(10,2) | Stock mínimo |
| max_stock | decimal(10,2) | Stock máximo |
| current_stock | decimal(10,2) | Saldo atual (calculado) |
| is_active | boolean | Ativo/Inativo |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela: requests (Pedidos/Solicitações)
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | Identificador único |
| request_number | string(20) UNIQUE | Número de protocolo gerado |
| item_id | bigint FK | Item solicitado |
| reference | string(100) | Referência |
| work_order | string(100) | Work Order |
| quantity | decimal(10,2) | Quantidade solicitada |
| equipment_id | string(100) | ID do Equipamento |
| replacement_reason | enum | Desgaste, Erro de Fabrico, Outro |
| status | enum | solicitado, preparando, enviando, recebido, entregue, cancelado |
| requester_id | bigint FK | Solicitante (user_id) |
| notes | text | Observações |
| requested_at | timestamp | Data/hora da solicitação |
| completed_at | timestamp | Data/hora da conclusão |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela: request_status_history
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| request_id | bigint FK | Pedido relacionado |
| from_status | enum | Status anterior |
| to_status | enum | Novo status |
| changed_by | bigint FK | Usuário que mudou |
| notes | text | Observações da mudança |
| changed_at | timestamp | Data/hora da mudança |

### Tabela: stock_movements (Movimentações)
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| item_id | bigint FK | Item movimentado |
| movement_type | enum | entrada, saida, ajuste |
| movement_reason | enum | compra, devolucao, pedido, consumo_interno, inventario, outro |
| quantity | decimal(10,2) | Quantidade (positivo entrada, negativo saída) |
| previous_stock | decimal(10,2) | Saldo anterior |
| new_stock | decimal(10,2) | Novo saldo |
| request_id | bigint FK NULL | Pedido relacionado (se aplicável) |
| inventory_id | bigint FK NULL | Inventário relacionado (se aplicável) |
| reference | string(100) | Documento/Referência |
| supplier | string(255) | Fornecedor (se compra) |
| destination | string(255) | Destino/Centro de Custo |
| location | string(100) | Localização |
| user_id | bigint FK | Usuário responsável |
| notes | text | Observações |
| movement_date | timestamp | Data/hora da movimentação |
| created_at | timestamp | |

### Tabela: inventories
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| inventory_number | string(20) UNIQUE | Número do inventário |
| location | string(100) | Localização inventariada |
| status | enum | em_andamento, concluido, cancelado |
| responsible_id | bigint FK | Responsável pelo inventário |
| start_date | timestamp | Data/hora de início |
| end_date | timestamp | Data/hora de conclusão |
| notes | text | Observações |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela: inventory_items
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| inventory_id | bigint FK | Inventário relacionado |
| item_id | bigint FK | Item contado |
| system_stock | decimal(10,2) | Saldo no sistema |
| counted_stock | decimal(10,2) | Saldo contado |
| difference | decimal(10,2) | Divergência (contado - sistema) |
| adjusted | boolean | Já foi ajustado? |
| counted_by | bigint FK | Usuário que contou |
| counted_at | timestamp | Data/hora da contagem |
| notes | text | Observações |

### Tabela: users (já existe, adicionar campos se necessário)
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| name | string(255) | Nome completo |
| email | string(255) UNIQUE | Email |
| password | string(255) | Senha hash |
| role | enum | solicitante, armazem, gestor, admin |
| department | string(100) | Departamento |
| is_active | boolean | Ativo/Inativo |
| created_at | timestamp | |
| updated_at | timestamp | |

## D) FLUXOS PRINCIPAIS

### D1) Criar Solicitação
1. Usuário autenticado acessa "Nova Solicitação"
2. Preenche formulário:
   - Seleciona item (autocomplete por código ou nome)
   - Sistema mostra stock disponível
   - Informa: Referência, Work Order, Qty, ID Equipamento
   - Seleciona Motivo de Substituição
   - Adiciona observações (opcional)
3. Sistema valida campos obrigatórios
4. Sistema gera número único de solicitação (REQ-YYYY-XXXX)
5. Sistema cria registro com status "solicitado"
6. Sistema registra data/hora atual
7. Sistema envia notificação para equipe de armazém
8. Usuário recebe confirmação com número do pedido

### D2) Preparar Pedido
1. Usuário armazém acessa lista de pedidos "solicitado"
2. Seleciona pedido para preparar
3. Sistema mostra detalhes do pedido e stock disponível
4. Usuário clica em "Iniciar Preparação"
5. Sistema verifica se há stock suficiente:
   - Se SIM: permite continuar
   - Se NÃO: alerta usuário (permite backorder ou cancela)
6. Usuário confirma ação (pode adicionar observações)
7. Sistema muda status para "preparando"
8. Sistema registra histórico de mudança de status
9. Sistema notifica solicitante da mudança de status

### D3) Enviar Pedido
1. Usuário armazém acessa pedido em "preparando"
2. Clica em "Enviar"
3. Sistema registra SAÍDA DE STOCK:
   - Cria registro em stock_movements
   - Tipo: saida
   - Motivo: pedido
   - Quantidade: quantidade do pedido (negativa)
   - Calcula novo saldo (current_stock - quantidade)
   - Atualiza items.current_stock
4. Sistema muda status para "enviando"
5. Sistema registra histórico de mudança de status
6. Sistema envia notificação para solicitante

**JUSTIFICATIVA: A saída de stock ocorre no momento "enviando" porque é quando o material fisicamente sai do armazém. Isso mantém o saldo do sistema sincronizado com o físico.**

### D4) Receber Pedido
1. Solicitante (ou armazém) acessa pedido em "enviando"
2. Clica em "Confirmar Recebimento"
3. Pode adicionar observações ou foto
4. Sistema muda status para "recebido"
5. Sistema registra histórico de mudança de status
6. Sistema notifica partes interessadas

### D5) Entregar (Finalizar)
1. Solicitante acessa pedido em "recebido"
2. Clica em "Confirmar Entrega no Equipamento"
3. Pode adicionar observações ou foto
4. Sistema muda status para "entregue"
5. Sistema registra data/hora de conclusão (completed_at)
6. Sistema registra histórico de mudança de status
7. Pedido é considerado finalizado

### D6) Entrada de Stock
1. Usuário armazém acessa "Registrar Entrada"
2. Preenche formulário:
   - Seleciona item
   - Informa quantidade
   - Seleciona tipo (Compra, Devolução, Ajuste)
   - Informa referência/documento
   - Se compra: informa fornecedor
   - Informa localização de armazenamento
   - Adiciona observações
3. Sistema valida dados
4. Sistema cria registro em stock_movements:
   - Tipo: entrada
   - Quantity: positivo
   - Previous_stock: saldo atual
   - New_stock: saldo atual + quantidade
5. Sistema atualiza items.current_stock
6. Sistema registra data/hora da entrada
7. Se ultrapassar max_stock: gera alerta (mas permite)
8. Confirmação de entrada registrada

### D7) Inventário e Ajustes
1. Gestor/Armazém cria "Nova Sessão de Inventário"
   - Informa localização (ou "Geral")
   - Status: "em_andamento"
2. Sistema gera número de inventário (INV-YYYY-XXXX)
3. Para cada item a contar:
   - Sistema mostra saldo atual (system_stock)
   - Usuário informa contagem física (counted_stock)
   - Sistema calcula divergência automaticamente
   - Usuário pode adicionar observações
4. Após contagem completa, usuário finaliza inventário
5. Sistema lista todas as divergências
6. Para cada divergência:
   - Gestor analisa
   - Pode aprovar ajuste ou rejeitar
   - Se aprovado: sistema cria movimentação de ajuste
     - Se diferença positiva: entrada
     - Se diferença negativa: saída
   - Atualiza items.current_stock
7. Sistema marca inventário como "concluido"
8. Gera relatório de inventário (PDF/Excel)

## E) REGRAS DE VALIDAÇÃO

### E1) Solicitação de Material
- **RV01**: Quantidade solicitada deve ser maior que zero
- **RV02**: Se quantidade > stock disponível:
  - OPÇÃO A (recomendada): Exibe alerta mas permite criar (backorder)
  - OPÇÃO B: Bloqueia criação e sugere quantidade máxima disponível
  - **SUGESTÃO: Implementar OPÇÃO A** - permite flexibilidade operacional, gerente pode aprovar urgência
- **RV03**: Todos os campos obrigatórios devem estar preenchidos
- **RV04**: Item deve estar ativo (is_active = true)
- **RV05**: Work Order deve ter formato válido (ex.: WO-\d{4,6})

### E2) Mudança de Status
- **RV06**: Status deve seguir fluxo sequencial obrigatório:
  - solicitado → preparando → enviando → recebido → entregue
  - Exceção: solicitado → cancelado (apenas pelo solicitante ou gestor)
- **RV07**: Apenas perfis autorizados podem mudar status:
  - solicitado → preparando: armazem, gestor, admin
  - preparando → enviando: armazem, gestor, admin
  - enviando → recebido: solicitante, armazem, gestor, admin
  - recebido → entregue: solicitante, gestor, admin
- **RV08**: Não permitir voltar status (exceto cancelamento por gestor)
- **RV09**: Ao enviar, validar stock disponível (se não houver, bloquear)

### E3) Movimentações de Stock
- **RV10**: Quantidade de movimentação deve ser maior que zero
- **RV11**: Saída não pode deixar stock negativo (exceto se permitir backorder)
- **RV12**: Item deve existir e estar ativo
- **RV13**: Referência/documento é obrigatório para rastreabilidade
- **RV14**: Movimentações de ajuste devem estar vinculadas a inventário aprovado

### E4) Inventário
- **RV15**: Não permitir múltiplos inventários "em_andamento" na mesma localização
- **RV16**: Contagem deve ser >= 0
- **RV17**: Apenas gestor ou admin pode aprovar ajustes
- **RV18**: Inventário concluído não pode ser editado

### E5) Itens
- **RV19**: Código do item deve ser único
- **RV20**: Stock mínimo deve ser >= 0
- **RV21**: Stock máximo deve ser > stock mínimo (se informado)
- **RV22**: Não permitir deletar item com movimentações existentes (apenas inativar)

## F) TELAS MÍNIMAS (MVP URGENTE) + ROADMAP

### MVP URGENTE (Fase 1 - 2-3 semanas)

#### Módulo de Solicitações
1. **Solicitações - Index** (`/requests`)
   - Tabela com: Nº Pedido, Item, Qty, Status, Data, Ações
   - Filtros: Status, Data, Solicitante
   - Paginação
   - Botões: Ver, Editar (se solicitado), Cancelar (se solicitado)
   - Botão "Nova Solicitação"

2. **Solicitações - Create** (`/requests/create`)
   - Formulário de solicitação completo
   - Validação em tempo real
   - Autocomplete de item (mostra stock disponível)
   - Botão "Enviar Solicitação"

3. **Solicitações - Show** (`/requests/:id`)
   - Detalhes completos da solicitação
   - Timeline de status com histórico
   - Informações do solicitante
   - Botões de ação baseados em status e permissão:
     - "Iniciar Preparação" (se solicitado)
     - "Enviar" (se preparando)
     - "Confirmar Recebimento" (se enviando)
     - "Confirmar Entrega" (se recebido)
     - "Cancelar" (se solicitado)

4. **Solicitações - Edit** (`/requests/:id/edit`)
   - Apenas para status "solicitado"
   - Mesmos campos do Create
   - Botão "Atualizar"

#### Módulo de Itens (Básico)
5. **Itens - Index** (`/items`)
   - Tabela: Código, Nome, Categoria, Stock Atual, Status
   - Filtros: Categoria, Status
   - Busca por código/nome
   - Paginação
   - Botões: Ver, Editar

6. **Itens - Create/Edit** (`/items/create`, `/items/:id/edit`)
   - Formulário completo de item
   - Validação

7. **Itens - Show** (`/items/:id`)
   - Detalhes do item
   - Saldo atual
   - Últimas 10 movimentações

#### Dashboard Básico
8. **Dashboard** (`/dashboard`)
   - Cards de métricas:
     - Total de pedidos por status
     - Itens abaixo do stock mínimo (alerta vermelho)
     - Pedidos aguardando ação
   - Listagem de pedidos recentes

### ROADMAP FUTURO (Fase 2)

#### Módulo de Movimentações
9. **Entradas - Index** (`/stock/entries`)
10. **Entradas - Create** (`/stock/entries/create`)
11. **Saídas - Index** (`/stock/exits`)
12. **Saídas - Create** (`/stock/exits/create`) - para consumo interno

#### Módulo de Inventário
13. **Inventários - Index** (`/inventories`)
14. **Inventários - Create** (`/inventories/create`)
15. **Inventários - Show** (`/inventories/:id`) - com listagem de itens
16. **Inventários - Count** (`/inventories/:id/count`) - tela de contagem

#### Relatórios
17. **Relatórios - Movimentações** (`/reports/movements`)
18. **Relatórios - Stock Atual** (`/reports/current-stock`)
19. **Relatórios - Pedidos** (`/reports/requests`)
20. **Relatórios - Performance** (`/reports/performance`)

#### Gestão de Usuários
21. **Usuários - Index** (`/users`)
22. **Usuários - Create/Edit** (`/users/create`, `/users/:id/edit`)

#### Configurações
23. **Categorias** (`/settings/categories`)
24. **Localizações** (`/settings/locations`)
25. **Perfis e Permissões** (`/settings/roles`)

## G) SUGESTÃO DE API ENDPOINTS

### Autenticação
```
POST   /api/auth/login          - Login
POST   /api/auth/logout         - Logout
GET    /api/auth/user           - Usuário autenticado
```

### Itens
```
GET    /api/items               - Listar itens (filtros, busca, paginação)
POST   /api/items               - Criar item
GET    /api/items/:id           - Visualizar item
PUT    /api/items/:id           - Atualizar item
DELETE /api/items/:id           - Inativar item
GET    /api/items/:id/movements - Histórico de movimentações do item
GET    /api/items/search        - Busca autocomplete
GET    /api/items/low-stock     - Itens abaixo do mínimo
```

### Solicitações (Requests)
```
GET    /api/requests            - Listar solicitações (filtros, paginação)
POST   /api/requests            - Criar solicitação
GET    /api/requests/:id        - Visualizar solicitação
PUT    /api/requests/:id        - Atualizar solicitação (só se solicitado)
DELETE /api/requests/:id        - Cancelar solicitação
GET    /api/requests/my         - Minhas solicitações
```

### Workflow de Pedidos
```
POST   /api/requests/:id/prepare   - Mudar para "preparando"
POST   /api/requests/:id/send      - Mudar para "enviando" (baixa stock)
POST   /api/requests/:id/receive   - Mudar para "recebido"
POST   /api/requests/:id/deliver   - Mudar para "entregue"
POST   /api/requests/:id/cancel    - Cancelar
GET    /api/requests/:id/history   - Histórico de status
```

### Movimentações de Stock
```
GET    /api/stock/movements         - Listar movimentações (filtros)
POST   /api/stock/movements/entry   - Registrar entrada
POST   /api/stock/movements/exit    - Registrar saída avulsa
GET    /api/stock/movements/:id     - Detalhes da movimentação
```

### Inventários
```
GET    /api/inventories             - Listar inventários
POST   /api/inventories             - Criar inventário
GET    /api/inventories/:id         - Visualizar inventário
PUT    /api/inventories/:id         - Atualizar inventário
POST   /api/inventories/:id/count   - Registrar contagem de item
POST   /api/inventories/:id/complete - Finalizar inventário
POST   /api/inventories/:id/adjust   - Aprovar ajuste de divergência
```

### Dashboard e Relatórios
```
GET    /api/dashboard/stats         - Estatísticas do dashboard
GET    /api/dashboard/alerts        - Alertas (stock baixo, pedidos pendentes)
GET    /api/reports/movements       - Relatório de movimentações
GET    /api/reports/current-stock   - Relatório de stock atual
GET    /api/reports/requests        - Relatório de pedidos
GET    /api/reports/performance     - Relatório de performance
```

### Usuários (Admin)
```
GET    /api/users               - Listar usuários
POST   /api/users               - Criar usuário
GET    /api/users/:id           - Visualizar usuário
PUT    /api/users/:id           - Atualizar usuário
DELETE /api/users/:id           - Inativar usuário
```

### Configurações
```
GET    /api/settings/categories     - Listar categorias
POST   /api/settings/categories     - Criar categoria
PUT    /api/settings/categories/:id - Atualizar categoria
DELETE /api/settings/categories/:id - Deletar categoria

GET    /api/settings/locations      - Listar localizações
POST   /api/settings/locations      - Criar localização
PUT    /api/settings/locations/:id  - Atualizar localização
DELETE /api/settings/locations/:id  - Deletar localização
```

---

## CHECKLIST DE CLARIFICAÇÕES

Antes de iniciar a implementação, preciso confirmar alguns pontos:

### 1. Stock Negativo
- [ ] Permitir stock negativo (backorder)? **Recomendo SIM** para urgências, com alerta.
- [ ] Ou bloquear totalmente saída quando stock insuficiente?

### 2. Unidades de Medida
- [ ] Quais unidades serão usadas? (un, kg, m, L, cx, etc.) - posso criar lista padrão?

### 3. Categorias de Itens
- [ ] Quais categorias principais? (Elétrica, Mecânica, Hidráulica, Consumíveis, EPIs, etc.)
- [ ] Ou deixo livre para cadastrar conforme necessidade?

### 4. Localizações no Armazém
- [ ] O armazém tem localizações físicas (ex.: Prateleira A1, Corredor 3, etc.)?
- [ ] Controlar stock por localização ou apenas geral por item?

### 5. Notificações
- [ ] Enviar notificações por email nas mudanças de status?
- [ ] Ou apenas notificações no sistema (in-app)?

### 6. Anexos/Fotos
- [ ] Permitir anexar fotos no recebimento/entrega?
- [ ] Necessário ou pode ser fase 2?

### 7. Aprovação de Pedidos
- [ ] Pedidos precisam de aprovação do gestor antes de preparar?
- [ ] Ou qualquer solicitação é válida automaticamente?

### 8. Múltiplos Itens por Pedido
- [ ] Um pedido pode ter múltiplos itens diferentes?
- [ ] Ou sempre 1 item por pedido (atual)?
- **Atenção**: Modelo atual suporta 1 item/pedido. Se precisar múltiplos, preciso ajustar.

### 9. Fornecedores
- [ ] Cadastrar fornecedores separadamente ou apenas texto livre nas entradas?

### 10. Impressões
- [ ] Precisa imprimir etiquetas, guias de remessa, etc.?
- [ ] Ou apenas exportar relatórios PDF/Excel?

**Por favor, responda essas perguntas para eu ajustar o sistema conforme suas necessidades exatas.**

---

## PRÓXIMOS PASSOS

Após suas respostas, vou implementar:

1. ✅ Migrations (banco de dados)
2. ✅ Models Eloquent com relationships
3. ✅ Controllers API
4. ✅ Rotas API
5. ✅ Policies e Gates (permissões)
6. ✅ Páginas Vue.js (seguindo padrão Sakai)
7. ✅ Factories e Seeders
8. ✅ Testes PHPUnit (feature tests principais)
9. ✅ Documentação de uso

**Tempo estimado MVP (Fase 1):** 2-3 semanas de desenvolvimento
**Tempo estimado Completo (Fase 1 + 2):** 4-6 semanas

Aguardo suas respostas para iniciar a implementação! 🚀
