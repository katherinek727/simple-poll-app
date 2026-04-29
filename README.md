# Simple Poll

A minimal full-stack polling application. Create a poll, share a short link, collect votes, see results instantly.

---

## Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Backend   | Laravel 12, PHP 8.2, SQLite         |
| Frontend  | Vue 3, Vite, Vuex 4, Vue Router 4   |
| HTTP      | Axios                               |

---

## Project Structure

```
simple-poll-app/
├── backend/          # Laravel API
│   ├── app/
│   │   ├── Domain/Poll/
│   │   │   ├── Contracts/        # ShortCodeGeneratorInterface
│   │   │   ├── Factories/        # PollFactory
│   │   │   ├── Generators/       # RandomShortCodeGenerator
│   │   │   └── Services/         # PollService
│   │   ├── Http/
│   │   │   ├── Controllers/Api/  # PollController
│   │   │   └── Requests/Api/     # CreatePollRequest, CastVoteRequest
│   │   └── Models/               # Poll, Option, Vote
│   ├── database/migrations/
│   └── routes/api.php
└── frontend/         # Vue 3 SPA
    └── src/
        ├── api/          # Axios wrapper
        ├── assets/       # Global CSS design system
        ├── components/   # AppLayout, PollResults
        ├── router/       # Vue Router
        ├── store/        # Vuex (poll module)
        └── views/        # CreatePollView, PollView
```

---

## Prerequisites

- PHP >= 8.2
- Composer >= 2
- Node.js >= 18
- npm >= 9

---

## Setup & Run

### 1. Clone the repository

```bash
git clone <repository-url>
cd simple-poll-app
```

### 2. Backend

```bash
cd backend

# Install PHP dependencies
composer install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create the SQLite database file
touch database/database.sqlite
# On Windows:
# New-Item -ItemType File -Path database/database.sqlite

# Run migrations
php artisan migrate

# Start the development server (runs on http://localhost:8000)
php artisan serve
```

### 3. Frontend

Open a second terminal:

```bash
cd frontend

# Install Node dependencies
npm install

# Start the dev server (runs on http://localhost:5173)
npm run dev
```

### 4. Open the app

Navigate to **http://localhost:5173** in your browser.

> The Vite dev server proxies all `/api/*` requests to `http://localhost:8000` automatically — no CORS configuration needed during development.

---

## API Reference

### `POST /api/polls`

Create a new poll.

**Request body:**
```json
{
  "title": "What is your favourite language?",
  "options": ["PHP", "Python", "Go"]
}
```

**Response `201`:**
```json
{ "short_code": "aBc123" }
```

---

### `GET /api/polls/{short_code}`

Fetch poll data. If the requesting IP has already voted, results are included.

**Response `200`:**
```json
{
  "id": 1,
  "title": "What is your favourite language?",
  "short_code": "aBc123",
  "created_at": "2026-04-29T12:00:00+00:00",
  "has_voted": false,
  "options": [
    { "id": 1, "text": "PHP" },
    { "id": 2, "text": "Python" },
    { "id": 3, "text": "Go" }
  ]
}
```

---

### `POST /api/polls/{short_code}/vote`

Cast a vote. Returns results on success or `409` if the IP has already voted.

**Request body:**
```json
{ "option_id": 2 }
```

**Response `200`:**
```json
{
  "message": "Vote cast successfully.",
  "results": [
    { "id": 1, "text": "PHP",    "votes": 3, "percentage": 37.5 },
    { "id": 2, "text": "Python", "votes": 4, "percentage": 50.0 },
    { "id": 3, "text": "Go",     "votes": 1, "percentage": 12.5 }
  ]
}
```

**Response `409`** (already voted):
```json
{
  "message": "You have already voted on this poll.",
  "results": [ ... ]
}
```

---

## Architecture Notes

### Domain-Driven Design

Business logic lives in `app/Domain/Poll/` and is completely decoupled from the HTTP layer:

- **`ShortCodeGeneratorInterface`** — contract for code generation strategies
- **`RandomShortCodeGenerator`** — CSPRNG-based implementation; swap by rebinding in `AppServiceProvider`
- **`PollFactory`** — creates polls transactionally; receives the generator via constructor injection
- **`PollService`** — handles lookups, vote casting, duplicate detection, and results aggregation

### Duplicate Vote Prevention

Enforced at two levels:

1. **Application layer** — `PollService::hasVoted()` checks before inserting
2. **Database layer** — `UNIQUE(poll_id, ip_address)` constraint on the `votes` table

### Short Code Generation

- 6 characters from a 57-character alphabet
- Excludes visually ambiguous characters: `0`, `O`, `l`, `I`
- Uses `random_int()` (CSPRNG) for each character
- Checks uniqueness against the database before returning
- Collision-resistant: 57⁶ ≈ 34 billion possible codes

---

## Building for Production

```bash
# Build frontend assets
cd frontend
npm run build

# The compiled assets are output to frontend/dist/
# Serve them via any static host or configure Laravel to serve them
```
