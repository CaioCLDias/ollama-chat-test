
# 🧠 Ollama Chat – Laravel 12 + Local LLM Integration

This project is a **Laravel 12** application integrated with **Ollama**, a local LLM engine running the **Llama3.2:1b** model. It was developed as a technical challenge for Buzzvel, featuring modern authentication, user account lifecycle management, scheduled background jobs, LLM chat integration, and full test coverage.

> ✅ Public test instance: [https://olla-app-test.ccldias.com](https://olla-app-test.ccldias.com)

---

## ✨ Features

- 🔐 Full authentication system (register, login, logout, email verification, password reset)
- 👤 Complete user CRUD (Create, Read, Update, Soft Delete with scheduled cleanup)
- 🤖 Chat integration using local Ollama + `llama3.2:1b` model
- 📅 Scheduled Jobs:
  - Daily update of main chat messages
  - Automatic deletion of accounts scheduled for removal
- 🧪 Full test coverage: unit, feature, and integration tests
- 📄 Auto-generated API documentation via Swagger (L5-Swagger)

---

## 🛠️ Tech Stack

- Laravel 12
- PHP 8.4
- Docker & Docker Compose
- Laravel Sail (for development)
- MySQL 8.0
- Ollama (LLM engine)
- Swagger (L5-Swagger)

---

## 🚦 Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose
- Git
- PHP (optional, if running outside Docker)

---

## 📥 Installation

### 💻 Development environment (Laravel Sail)

1. **Clone the repository:**

```bash
git clone git@github.com:CaioCLDias/ollama-chat-test.git
cd ollama-chat-test
```

2. **Copy the environment file and install dependencies:**

```bash
cp .env.example .env
composer install
```

3. **Start Docker containers with Sail:**

```bash
./vendor/bin/sail up -d
```

4. **Generate application encryption key:**

```bash
./vendor/bin/sail artisan key:generate
```

5. **Run database migrations and seeders:**

```bash
./vendor/bin/sail artisan migrate --seed
```

6. **Run Laravel Scheduled Jobs:**

```bash
nohup ./vendor/bin/sail artisan schedule:work > storage/logs/schedule.log 2>&1 &
```

7. **Download and start the LLM model (llama3.2:1b):**

```bash
./vendor/bin/sail exec ollama ollama run llama3.2:1b
```

8. **Run all automated tests (unit + feature + integration):**

```bash
./vendor/bin/sail artisan test
```

9. **Generate Swagger Documentation:**

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

### 💻 You can also run the application in a production-like containerized environment:

1. **Clone the repository:**

```bash
git clone git@github.com:CaioCLDias/ollama-chat-test.git
```
```bash
cd ollama-chat-test
```

2. **Copy the environment file and install dependencies:**

```bash
cp .env.example .env
```
```bash
composer install
```

3. **Run the Container**

```bash
docker compose -f infra/dev/docker-compose.test.yml --env-file .env up --build -d
```

This will:

- Build the Laravel app container
- Seed the database
- Start scheduled jobs (email cleanup, message updates)

3. **🦙 Running Ollama Model**

> ❗️After the containers are up and running, you need to manually load the model inside the Ollama container using the following command:

```bash
docker exec -d ollama_test ollama run llama3.2:1b
```

---

## 🔍 Ollama Chat Example (LLM API)

You can test the Ollama LLM directly:

```bash
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{"model": "llama3.2:1b", "prompt": "What is the capital of Portugal?"}'
```

Example response:

```json
{
  "model": "llama3.2:1b",
  "response": "The capital of Portugal is Lisbon.",
  "done": true
}
```

---

## 🔁 Scheduled Jobs

The scheduler runs background commands like:

```php
$schedule->command('chat:update-main-message')->daily();
$schedule->command('users:process-user-deletions')->daily();
```

These are automatically started by the container via `start.sh`.

---

## 📄 API Documentation

Swagger docs are automatically generated:

- Local: [http://localhost/api/documentation](http://localhost/api/documentation)
- Public: [https://olla-app-test.ccldias.com/api/documentation](https://olla-app-test.ccldias.com/api/documentation)

---

## 🧩 Features Implemented

| Requirement                            | Status ✅ |
|----------------------------------------|-----------|
| Register / Login / Logout              | ✅        |
| Email verification                     | ✅        |
| Password reset                         | ✅        |
| User CRUD with soft deletion           | ✅        |
| Ollama LLM chat                        | ✅        |
| Store chat history                     | ✅        |
| Jobs: Delete accounts, update messages | ✅        |
| Swagger docs for API                   | ✅        |
| All routes protected and validated     | ✅        |
| Dockerized development and production  | ✅        |
| Full test suite                        | ✅        |

---

## 🚀 Demo Environment

Deployed on a VPS for public testing:

- 🌐 [https://olla-app-test.ccldias.com](https://olla-app-test.ccldias.com)
- 📘 Swagger: [https://olla-app-test.ccldias.com/api/documentation](https://olla-app-test.ccldias.com/api/documentation)

---

---

## ✅ How to Use the API (Step-by-Step)

> This guide assumes your API is accessible at:  
> `https://olla-app-test.ccldias.com`

---

### 1. 📝 Register a New User

**Endpoint:**

```
POST /api/auth/register
```

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Expected Response:**

- Returns a **Bearer token** and user object.
- Stores user as `unverified`.

---

### 2. 📬 Request Email Verification

**Endpoint:**

```
POST /api/auth/email/verification-notification
```

**Headers:**

```
Authorization: Bearer <your_token>
```

**What it does:**

- Generates a **signed verification link**.
- Since the app is using `MAIL_MAILER=log`, the link is logged (see `storage/logs/laravel.log`).
- For testing purposes, the URL will be returned in the request response.

---

### 3. 🔗 Access the Verification Link

- Locate the signed URL in your `laravel.log`, it looks like:

```
https://olla-app-test.ccldias.com/api/auth/verify-email/5/<HASH>?expires=...&signature=...
```

**Just open the URL in the browser** — it will verify the email and redirect to `/email-verified-success`.

---

### 4. 🔓 Now You're Verified!

After verifying your email:

- The user becomes **fully authenticated**.
- You can now access **protected routes**, like `/api/chat`.

---

### 5. 💬 Send a Chat Message

**Endpoint:**

```
POST /api/chat
```

**Headers:**

```
Authorization: Bearer <your_token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "message": "What is the capital of Brazil?"
}
```

**Expected Response:**

```json
{
  "status": true,
  "message": "Chat response generated successfully",
  "data": {
    "message": "What is the capital of Brazil?",
    "response": "The capital of Brazil is Brasília."
  }
}
```

---

## 🎥 Demonstration

A short video will be provided demonstrating:
https://www.youtube.com/watch?v=ug8V-h5ikZA
---

## 📌 Summary

| Step | Description |
|------|-------------|
| ✅ 1 | Register a user via `/auth/register` |
| 📬 2 | Send verification link via `/email/verification-notification` |
| 🔗 3 | Click the signed URL from logs to verify |
| 🔓 4 | Authenticated and verified |
| 🤖 5 | Use `/chat` endpoint to talk to the LLM |

## 📮 Final Notes

This project demonstrates:

- Strong understanding of Laravel ecosystem
- Proper Docker-based development workflow
- Modern API design and secure practices
- Real-world usage of LLM integration
- Robust testing and deployment

> 🔐 Designed with production-readiness and clean code in mind.
