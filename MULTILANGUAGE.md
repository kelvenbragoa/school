# Sistema de Múltiplos Idiomas (Português e Inglês)

## Visão Geral

O site agora suporta dois idiomas: **Português (PT)** e **Inglês (EN)** com um seletor de idioma no cabeçalho.

## Estrutura Implementada

### Arquivos de Tradução

- `lang/pt/landing.php` - Traduções em português
- `lang/en/landing.php` - Traduções em inglês

### Backend (Laravel)

1. **Middleware**: `app/Http/Middleware/SetLocale.php`
   - Define o idioma da aplicação baseado na sessão

2. **Controllers**:
   - `app/Http/Controllers/LocaleController.php` - Gerencia mudança de idioma
   - `app/Http/Controllers/Api/TranslationController.php` - API para retornar traduções

3. **Rotas**:
   - `POST /locale/change` - Endpoint para mudar idioma
   - `GET /api/translations/{locale}` - API para obter traduções

### Frontend (Vue)

1. **Composable**: `resources/js/composables/useTranslation.ts`
   - `t(key)` - Função para traduzir textos
   - `changeLocale(locale)` - Função para mudar idioma
   - `locale` - Idioma atual

2. **Componente**: `resources/js/components/LanguageSelector.vue`
   - Seletor visual de idioma com bandeiras
   - Dropdown com opções PT/EN

3. **Página Landing**: `resources/js/views/pages/Landing.vue`
   - Todas as strings estáticas foram convertidas para usar traduções

## Como Usar

### No código Vue:

```vue
<script setup>
import { useTranslation } from '@/composables/useTranslation';

const { t } = useTranslation();
</script>

<template>
  <h1>{{ t('landing.hero.title') }}</h1>
  <p>{{ t('landing.about.description') }}</p>
</template>
```

### Adicionar Novas Traduções:

1. Adicione a chave em `lang/pt/landing.php`:
```php
'my_section' => [
    'title' => 'Meu Título',
    'description' => 'Minha Descrição'
]
```

2. Adicione a tradução em inglês em `lang/en/landing.php`:
```php
'my_section' => [
    'title' => 'My Title',
    'description' => 'My Description'
]
```

3. Use no componente Vue:
```vue
<h2>{{ t('landing.my_section.title') }}</h2>
<p>{{ t('landing.my_section.description') }}</p>
```

## Configuração

### Idioma Padrão

O idioma padrão está configurado em `config/app.php`:

```php
'locale' => env('APP_LOCALE', 'pt'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'pt'),
```

Para alterar, edite o arquivo `.env`:
```
APP_LOCALE=pt
APP_FALLBACK_LOCALE=pt
```

## Funcionalidades

✅ Seletor de idioma no cabeçalho  
✅ Persistência do idioma selecionado (localStorage + sessão)  
✅ Tradução de toda a página Landing  
✅ API REST para obter traduções  
✅ Interface amigável com bandeiras  
✅ Transição suave entre idiomas  

## Idiomas Suportados

- 🇵🇹 Português (PT)
- 🇬🇧 Inglês (EN)

## Testar

1. Inicie o servidor:
```bash
php artisan serve
npm run dev
```

2. Acesse o site e clique no seletor de idioma no cabeçalho
3. Selecione entre Português ou Inglês
4. Toda a página será traduzida automaticamente
