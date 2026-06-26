# ThreadForge API

API Laravel de transformation de contenu brut en posts Twitter/X optimisés par IA, avec assistant conversationnel Ghostwriter.

## Stack

| Technologie | Version |
|---|---|
| Laravel | 13 |
| PHP | ^8.3 |
| MySQL | |
| laravel/ai | ^0.8.1 |
| Sanctum | ^4.0 |
| Queue | database |
| API Docs | Scribe |

## Prérequis

- PHP ^8.3
- Composer
- MySQL
- Node.js & npm

## Installation

```bash
composer install
cp .env.example .env
# Configurer DB et GROQ_API_KEY dans .env
php artisan key:generate
php artisan migrate
php artisan scribe:generate
```

## Développement

```bash
composer run dev
# Lance simultanément : serveur + queue:listen + logs + Vite
```

## Tests

```bash
composer run test
```

## Architecture

### Flux Repurposing

```
CampaignBlueprint (règles de style)
        │
        ▼
RawContent (contenu brut soumis via API)
        │
        ▼
ProcessRawContentJob (queue asynchrone)
        │
        ▼
GrokRepurposingService (appel Groq)
        │
        ▼
GeneratedPost (post structuré généré)
```

### Ghostwriter Agent

Agent conversationnel avec mémoire (`RemembersConversations`) et 2 outils PHP réels :

| Outil | Rôle |
|---|---|
| `getCampaignRules` | Lit les règles de style du Blueprint associé au post |
| `getPostHistory` | Lit les versions précédentes du post |

L'agent répond en français, utilise les outils pour éviter les hallucinations, et maintient le contexte via `laravel/ai`.

## MCD — Relations entre entités

| Entité #1 | Cardinalité | Entité #2 | Cardinalité | Signification |
|---|---|---|---|---|
| **User** | 1──N | CampaignBlueprint | 1──N | Un utilisateur possède N blueprints |
| **User** | 1──N | RawContent | 1──N | Un utilisateur soumet N contenus bruts |
| **CampaignBlueprint** | 1──N | RawContent | 1──N | Un blueprint définit N contenus bruts |
| **RawContent** | 1──1 | GeneratedPost | 1──1 | Un contenu brut génère un post |
| **GeneratedPost** | 0..1──1 | AgentConversation | 0..1──1 | Un post peut référencer une conversation |
| **User** | 1──N | AgentConversation | 1──N | Un utilisateur a N conversations |
| **AgentConversation** | 1──N | AgentConversationMessage | 1──N | Une conversation contient N messages |

## MLD — Tables

### `users`

| Colonne | Type | Contraintes |
|---|---|---|
| id | INT | PK |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| email_verified_at | TIMESTAMP | NULL |
| password | VARCHAR(255) | NOT NULL |
| remember_token | VARCHAR(100) | NULL |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `campaign_blueprints`

| Colonne | Type | Contraintes |
|---|---|---|
| id | INT | PK |
| **user_id** | INT | FK → users.id |
| name | VARCHAR(255) | |
| target_audience | VARCHAR(255) | |
| tone | VARCHAR(255) | |
| max_length | INT UNSIGNED | DEFAULT 280 |
| forbidden_words | JSON | NULL |
| max_hashtags | TINYINT UNSIGNED | DEFAULT 1 |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `raw_contents`

| Colonne | Type | Contraintes |
|---|---|---|
| id | INT | PK |
| **user_id** | INT | FK → users.id ON DELETE CASCADE |
| **campaign_blueprint_id** | INT | FK → campaign_blueprints.id ON DELETE CASCADE |
| content | TEXT | |
| status | VARCHAR(255) | DEFAULT 'pending' |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `generated_posts`

| Colonne | Type | Contraintes |
|---|---|---|
| id | INT | PK |
| **raw_content_id** | INT | FK → raw_contents.id ON DELETE CASCADE |
| hook_propose | VARCHAR(280) | NULL |
| body_points | JSON | NULL |
| technical_readability_score | TINYINT UNSIGNED | NULL |
| suggested_hashtags | JSON | NULL |
| tone_compliance_justification | TEXT | NULL |
| status | VARCHAR(255) | DEFAULT 'draft' |
| **ai_conversation_id** | VARCHAR(36) | NULL, FK → agent_conversations.id |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `agent_conversations`

| Colonne | Type | Contraintes |
|---|---|---|
| id | VARCHAR(36) | PK |
| **user_id** | INT | NULL, FK → users.id |
| title | VARCHAR(255) | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### `agent_conversation_messages`

| Colonne | Type | Contraintes |
|---|---|---|
| id | VARCHAR(36) | PK |
| **conversation_id** | VARCHAR(36) | FK → agent_conversations.id |
| **user_id** | INT | NULL, FK → users.id |
| agent | VARCHAR(255) | |
| role | VARCHAR(25) | |
| content | TEXT | |
| attachments | TEXT | |
| tool_calls | TEXT | |
| tool_results | TEXT | |
| usage | TEXT | |
| meta | TEXT | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

## API Endpoints

### Auth

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | ✗ | Inscription |
| POST | `/api/login` | ✗ | Connexion |
| POST | `/api/logout` | ✓ | Déconnexion |
| GET | `/api/user` | ✓ | Profil utilisateur |

### Campaign Blueprints

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/campaing-blueprint` | ✓ | Lister mes blueprints |
| GET | `/api/campaing-blueprint/{id}` | ✓ | Détail d'un blueprint |
| POST | `/api/campaing-blueprint/store` | ✓ | Créer un blueprint |
| PUT | `/api/campaing-blueprint/{id}` | ✓ | Modifier un blueprint |
| DELETE | `/api/campaing-blueprint/{id}` | ✓ | Supprimer un blueprint |

### Raw Content

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/raw-content/store` | ✓ | Soumettre contenu brut (déclenche job) |

### Generated Posts

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/generated-post` | ✓ | Lister mes posts générés |
| GET | `/api/generated-post/{id}` | ✓ | Détail d'un post |
| PUT | `/api/generated-post/{id}` | ✓ | Mettre à jour le statut |
| DELETE | `/api/generated-post/{id}` | ✓ | Supprimer un post |

### Ghostwriter Agent

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/generated-post/{postId}/chat` | ✓ | Envoyer un message à l'agent |

## Statuts

### RawContent

```
pending → analyzing → done
                   → failed
```

### GeneratedPost

```
draft → posted → archived
```
