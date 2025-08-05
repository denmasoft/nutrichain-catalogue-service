
## Table of Contents
- [Architecture & Key Decisions](#architecture--key-decisions)
- [Core Features](#core-features)
- [Prerequisites](#prerequisites)
- [Setup & Installation](#setup--installation)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Security](#security)
- [Testing](#testing)

## Architecture & Key Decisions

This service is designed as a **stateless resource server** within a microservices architecture. It does not handle user authentication (login/registration) but instead validates JWTs issued by a dedicated Authentication service.

- **Technology Stack**: Symfony 2.8, PHP 7.2, PostgreSQL.
- **API**: A versioned RESTful API (`v1`) built with `FOSRestBundle` and documented with `NelmioApiDocBundle`.
- **Decoupling**: Uses Data Transfer Objects (DTOs) to decouple the API layer from the domain (Doctrine entities). All business logic is encapsulated in Services, following SOLID principles.
- **Database**: All database interactions are managed via Doctrine ORM. It uses `Timestampable` for automatic `createdAt`/`updatedAt` fields and `SoftDeleteable` to prevent permanent deletion of product records.

## Core Features

- **Full CRUD for Products**: Endpoints to create, read, update, and soft-delete products.
- **Advanced Filtering**: The `GET /api/products` endpoint supports filtering by `name` and `category`.
- **Soft Deletion**: Products are never permanently removed from the database. A `deleted_at` timestamp is set, and they are automatically excluded from all standard queries.
- **JWT Authentication**: Secures all endpoints by validating JWTs. It expects a valid token in the `Authorization: Bearer <token>` header.
- **Role-Based Access Control (RBAC)**: Access to certain endpoints, like the API documentation (`/api/doc`), is restricted to users with the `ROLE_ADMIN` claim in their JWT payload.
- **Production-Grade Security Listeners**:
  - **Rate Limiting**: Protects the API from abuse. It uses Redis to track requests, applying different limits for authenticated users (identified by JWT username) and anonymous traffic (identified by IP).
  - **Idempotency**: Prevents accidental duplicate requests for `POST`, `PUT`, etc., by handling an `Idempotency-Key` header.
- **Internationalization (i18n)**: All user-facing messages (errors, confirmations) are translated and support English (`en`) and Spanish (`es`), managed via the Symfony Translator component.

## Prerequisites

- Docker & Docker Compose
- A running Redis instance for rate limiting and idempotency caching.
- A running PostgreSQL instance (e.g., on Supabase, as specified in the project).

## Setup & Installation

1.  **Clone the Repository**
    ```bash
    git clone <your-repo-url>
    cd <repository-folder>/catalogo
    ```

2.  **Configure Environment Variables**
    Copy the `app/config/parameters.yml.dist` to `app/config/parameters.yml` and fill in your database credentials, Redis connection details, and JWT pass-phrase.

    ```yaml
    # app/config/parameters.yml
    parameters:
        database_driver: pdo_pgsql
        database_host: your_db_host
        database_port: 5432
        database_name: your_db_name
        database_user: your_db_user
        database_password: your_db_password

        redis_host: localhost
        redis_port: 6379

        jwt_private_key_path: '%kernel.root_dir%/var/jwt/private.pem'
        jwt_public_key_path:  '%kernel.root_dir%/var/jwt/public.pem'
        jwt_key_pass_phrase:  'your_secret_passphrase'
        jwt_token_ttl:        3600 # 1 hour
    ```

3.  **Generate JWT Keys**
    The application uses RSA keys (RS256) for signing JWTs. Generate them with OpenSSL:
    ```bash
    mkdir -p app/var/jwt
    openssl genrsa -out app/var/jwt/private.pem -aes256 4096
    # Enter your chosen passphrase here

    openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
    # Enter the passphrase again to export the public key
    ```

4.  **Install Dependencies**
    It is highly recommended to use Docker to run the composer install command to ensure the correct PHP version and extensions are used.

    If you have a local PHP 7.2 setup:
    ```bash
    composer install
    ```

    Using Docker:
    ```bash
    docker-compose run --rm catalogo composer install
    ```

5.  **Setup the Database**
    Create the database schema and load initial test data.
    ```bash
    # From within the container or your local machine with php-cli
    php app/console doctrine:schema:update --force
    php app/console doctrine:fixtures:load --no-interaction
    ```

## Running the Application

The entire application stack can be launched using Docker Compose from the project's root directory.

```bash
# From the root of the NutriChain project
docker-compose up -d catalogo
```

The Catalogue microservice will be available at http://localhost:8081 (or as configured in your docker-compose.yml).


## API Documentation


API documentation is automatically generated and available at /api/doc.
URL: http://localhost:8081/api/doc
Access: This endpoint is protected and requires a valid JWT with the ROLE_ADMIN claim. You can generate a test admin token using the following command:
```bash
# From within the container or your local machine
php app/console lexik:jwt:generate-token admin --roles=ROLE_ADMIN
```

Use the generated token in the Authorization header field of the API documentation UI to test protected endpoints.

## Security

- **JWT Validation:** All API endpoints under /api/ (except health checks) require a valid JWT. The token's signature and expiration are verified on every request.
- **Rate Limiting:** Limits are applied per-user (for authenticated requests) and per-IP (for anonymous requests) to prevent brute-force attacks and service abuse.
- **Idempotency:** The Idempotency-Key header ensures that non-safe HTTP methods (POST, PUT) are only processed once, even if the client sends multiple identical requests.
- **CORS:** Cross-Origin Resource Sharing is configured via NelmioCorsBundle to allow requests only from trusted origins (configurable in config.yml).

## Testing

To run the PHPUnit test suite:
```bash 
# From within the container or your local machine
./bin/phpunit
```