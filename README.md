# Wallet Service API

A RESTful API for managing wallets, performing deposits, withdrawals, and transfers between wallets. Built with Laravel 12.

## Features

- ✅ Wallet creation and management
- ✅ Deposits and withdrawals with idempotency
- ✅ Atomic transfers between wallets
- ✅ Transaction history with filtering and pagination
- ✅ Balance queries
- ✅ Currency validation
- ✅ Double-entry accounting for transfers
- ✅ Database transactions for atomicity
- ✅ Row-level locking to prevent race conditions
- ✅ Docker support for easy deployment

## Requirements

- PHP 8.4.0 or higher
- Composer
- MySQL

**Or using Docker:**
- Docker
- Docker Compose

## Installation

### Option 1: Using Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/emanalzoaubi/wallet-service.git
   cd wallet-service
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```

3. **Update `.env` file** for Docker:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=wallet_service
   DB_USERNAME=wallet_user
   DB_PASSWORD=secret
   ```

4. **Build and start containers**
   ```bash
   docker-compose up -d --build
   ```

5. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

The API will be available at `http://localhost:8000/api`

#### Docker Commands Reference

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Execute artisan commands
docker-compose exec app php artisan <command>

# Access MySQL
docker-compose exec db mysql -u wallet_user -p wallet_service

# Rebuild containers (after Dockerfile changes)
docker-compose up -d --build
```

### Option 2: Local Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/emanalzoaubi/wallet-service.git
   cd wallet-service
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` file** with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=wallet_service
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

## API Endpoints

All endpoints are prefixed with `/api`.

### Wallet Management

#### Create Wallet
```http
POST /api/wallets
Content-Type: application/json

{
  "owner_name": "John Doe",
  "currency": "USD"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "owner_name": "John Doe",
    "currency": "USD",
    "balance_minor": 0,
    "created_at": "2026-01-05T12:00:00.000000Z",
  }
}
```

#### Get Wallet
```http
GET /api/wallets/{id}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "owner_name": "John Doe",
    "currency": "USD",
    "balance_minor": 10000,
    "created_at": "2026-01-05T12:00:00.000000Z",
  }
}
```

#### List Wallets
```http
GET /api/wallets?owner_name=John&currency=USD&per_page=10
```

**Query Parameters:**
- `owner_name` (optional): Filter by owner name (partial match)
- `currency` (optional): Filter by currency code
- `per_page` (optional): Items per page (default: 10)

**Response:**
```json
{
  "status": "success",
  "data": {
    "data": [
      {
        "id": 1,
        "owner_name": "John Doe",
        "currency": "USD",
        "balance_minor": 10000,
        "created_at": "2026-01-05T12:00:00.000000Z",
      }
    ],
    "pager": {
      "total": 1,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Get Wallet Balance
```http
GET /api/wallets/{id}/balance
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "balance": 10000
  }
}
```

### Deposits and Withdrawals

#### Deposit
```http
POST /api/wallets/{id}/deposit
Content-Type: application/json
Idempotency-Key: unique-key-123

{
  "amount": 10000
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "type": "deposit",
    "amount_minor": 10000,
    "wallet_id": 1,
    "created_at": "2026-01-05T12:00:00.000000Z"
  }
}
```

#### Withdraw
```http
POST /api/wallets/{id}/withdraw
Content-Type: application/json
Idempotency-Key: unique-key-456

{
  "amount": 5000
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 2,
    "type": "withdraw",
    "amount_minor": 5000,
    "wallet_id": 1,
    "created_at": "2026-01-05T12:01:00.000000Z"
  }
}
```

**Note:** Both deposit and withdraw require the `Idempotency-Key` header. Using the same key will return the original transaction without creating a duplicate.

### Transfers

#### Transfer Between Wallets
```http
POST /api/transfers
Content-Type: application/json
Idempotency-Key: unique-key-789

{
  "from_wallet_id": 1,
  "to_wallet_id": 2,
  "amount": 3000
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "debit_transaction": {
      "id": 3,
      "type": "transfer_debit",
      "amount_minor": 3000,
      "wallet_id": 1,
      "related_wallet": {
        "id": 2,
        "owner_name": "Jane Doe"
      },
      "created_at": "2026-01-05T12:02:00.000000Z"
    },
    "credit_transaction": {
      "id": 4,
      "type": "transfer_credit",
      "amount_minor": 3000,
      "wallet_id": 2,
      "related_wallet": {
        "id": 1,
        "owner_name": "John Doe"
      },
      "created_at": "2026-01-05T12:02:00.000000Z"
    }
  }
}
```

**Rules:**
- Both wallets must have the same currency
- Source wallet must have sufficient balance
- Cannot transfer to the same wallet
- Requires `Idempotency-Key` header

### Transaction History

#### Get Wallet Transactions
```http
GET /api/wallets/{id}/transactions?type=deposit&date_from=2026-01-01&date_to=2026-01-31&per_page=20
```

**Query Parameters:**
- `type` (optional): Filter by transaction type (`deposit`, `withdraw`, `transfer_debit`, `transfer_credit`)
- `date_from` (optional): Start date (format: `Y-m-d`)
- `date_to` (optional): End date (format: `Y-m-d`)
- `per_page` (optional): Items per page (default: 10)

**Response:**
```json
{
  "status": "success",
  "data": {
    "data": [
      {
        "id": 1,
        "type": "deposit",
        "amount_minor": 10000,
        "wallet_id": 1,
        "related_wallet": null,
        "created_at": "2026-01-05T12:00:00.000000Z"
      }
    ],
    "pager": {
      "total": 1,
      "per_page": 20,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

### Health Check

#### Health Endpoint
```http
GET /api/health
```

**Response:**
```json
{
  "status": "ok"
}
```

## Business Rules

### Monetary Precision
- All amounts are stored as integers representing minor units (e.g., cents for USD)
- Example: $100.00 is stored as `10000` (10000 cents)

### Idempotency
- All mutation operations (deposit, withdraw, transfer) require an `Idempotency-Key` header
- Using the same key with the same parameters returns the original transaction
- **Idempotency-Key Requirements:**
  - The key is opaque and has no restrictions except a maximum length of 255 characters
  - It is the client's responsibility to generate a unique key for each operation
  - Common approaches include UUIDs, timestamps with random components, or client-generated unique identifiers

### Atomicity
- All operations are wrapped in database transactions
- Row-level locking prevents race conditions
- Transfers are fully atomic (both debit and credit succeed or fail together)

### Currency Rules
- Currency must be a 3-letter uppercase code (e.g., USD, EUR, GBP)
- Transfers only allowed between wallets with the same currency
- **Note:** For simplicity, currency validation is implemented using a PHP Enum containing common currencies (USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, BRL, SYP, SAR, AED, KWD, QAR, BHD, OMR, KRW, IDR). In a production environment, it is recommended to implement a dedicated currency management system to define and govern supported currencies. This ensures strict adherence to ISO 4217 standards and provides a centralized way to validate monetary inputs against a comprehensive database of allowed currencies.

### Balance Constraints
- Wallets cannot have negative balances
- Withdrawals and transfers are rejected if insufficient funds

### Double-Entry Accounting
- Transfers create two transactions:
  - `transfer_debit` on the source wallet
  - `transfer_credit` on the target wallet
- Both transactions share the same `idempotency_key`

## Error Responses

All errors follow a consistent format:

```json
{
  "status": "error",
  "message": "Error description"
}
```

### Common HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request (missing Idempotency-Key, validation errors)
- `404` - Not Found (wallet not found)
- `409` - Conflict (idempotency key violation)
- `422` - Unprocessable Entity (insufficient funds, currency mismatch, invalid transfer)

## Example Workflow

1. **Create two wallets:**
   ```bash
   curl -X POST http://localhost:8000/api/wallets \
     -H "Content-Type: application/json" \
     -d '{"owner_name": "John Doe", "currency": "USD"}'
   
   curl -X POST http://localhost:8000/api/wallets \
     -H "Content-Type: application/json" \
     -d '{"owner_name": "Jane Doe", "currency": "USD"}'
   ```

2. **Deposit 100 USD (10000 cents) to Wallet 1:**
   ```bash
   curl -X POST http://localhost:8000/api/wallets/1/deposit \
     -H "Content-Type: application/json" \
     -H "Idempotency-Key: deposit-001" \
     -d '{"amount": 10000}'
   ```

3. **Withdraw 30 USD (3000 cents) from Wallet 1:**
   ```bash
   curl -X POST http://localhost:8000/api/wallets/1/withdraw \
     -H "Content-Type: application/json" \
     -H "Idempotency-Key: withdraw-001" \
     -d '{"amount": 3000}'
   ```

4. **Transfer 50 USD (5000 cents) from Wallet 1 to Wallet 2:**
   ```bash
   curl -X POST http://localhost:8000/api/transfers \
     -H "Content-Type: application/json" \
     -H "Idempotency-Key: transfer-001" \
     -d '{"from_wallet_id": 1, "to_wallet_id": 2, "amount": 5000}'
   ```

5. **Check Wallet 1 balance:**
   ```bash
   curl http://localhost:8000/api/wallets/1/balance
   ```
   Expected balance: 2000 cents ($20.00)

6. **View transaction history:**
   ```bash
   curl http://localhost:8000/api/wallets/1/transactions
   ```


## Postman Collection

A Postman collection and environment are available in the repository under the /postman directory. Import (`wallet-service.postman_collection.json`) along with the provided environment into Postman to test all endpoints.

## Database Schema

### Wallets Table
- `id` - Primary key
- `owner_name` - Wallet owner name
- `currency` - Currency code (3 letters, uppercase)
- `balance_minor` - Balance in minor units (default: 0)
- `created_at` - Timestamp
- `updated_at` - Timestamp

### Transactions Table
- `id` - Primary key
- `wallet_id` - Foreign key to wallets
- `related_wallet_id` - Foreign key to wallets (for transfers)
- `type` - Transaction type (deposit, withdraw, transfer_debit, transfer_credit)
- `amount_minor` - Amount in minor units
- `idempotency_key` - Unique key for idempotency
- `created_at` - Timestamp
- `updated_at` - Timestamp

**Indexes:**
- Unique constraint on `(wallet_id, idempotency_key)`
- Index on `(wallet_id, type, created_at)` for efficient history queries

## Architecture

The application follows a clean architecture pattern:

- **Controllers** (`app/Http/Controllers/Api/`) - Handle HTTP requests/responses
- **Services** (`app/Services/`) - Business logic
- **Repositories** (`app/Repositories/`) - Data access layer
- **Models** (`app/Models/`) - Eloquent models
- **Resources** (`app/Http/Resources/`) - API response transformation
- **Requests** (`app/Http/Requests/`) - Form validation
- **Exceptions** (`app/Exceptions/`) - Custom exception handlers

## Docker Architecture

The application is containerized using Docker with the following services:

- **app** - PHP 8.4 container running Laravel's built-in server
- **db** - MySQL 8.0 database server

> **Note:** This Docker setup uses Laravel's built-in development server (`php artisan serve`) for simplicity and ease of testing. This is intentional for demonstration purposes. For production deployments, you should use a proper web server like **Nginx** with **PHP-FPM**, which provides better performance, concurrency handling, and security.

## Future Improvements

- [ ] Add API versioning (`/api/v1/...`)
- [ ] Add transaction logging/audit trail
- [ ] Add balance verification endpoint
- [ ] Add caching for read-heavy endpoints
- [ ] Add Redis for caching and queue management
- [ ] Add CI/CD pipeline configuration
