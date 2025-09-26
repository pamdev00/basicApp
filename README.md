# BasicApp API Project

<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Framework API Project</h1>
    <br>
</p>

This is a demo API application for the Yii 3 Framework, showcasing features like RESTful endpoints, user registration with email verification, and more.

## 🚀 Quick Start

This project is fully containerized using Docker. A `Makefile` is provided for convenience.

### Prerequisites

*   Docker and Docker Compose
*   `make`

### 1. Initial Setup

Clone the repository and run the initialization command. This will build the containers, install dependencies, and run database migrations.

```bash
make init
```

### 2. Start the Application

To start the application services (nginx, php-fpm, postgres), run:

```bash
make up
```

The API will be available at `http://localhost:8091`.

### 3. Stop the Application

To stop the services:

```bash
make docker-down
```

To stop the services and remove all data (including the database):

```bash
make docker-down-clear
```

## 🔧 Configuration

The application is configured using environment variables.

1.  Copy the example environment file:
    ```bash
    cp .env.example .env
    ```
2.  Edit the `.env` file to match your local setup. The following variables are available:

| Variable                | Description                                      | Default         |
| ----------------------- | ------------------------------------------------ | --------------- |
| `YII_ENV`               | The application environment (`dev`, `prod`).     | `dev`           |
| `YII_DEBUG`             | Whether debug mode is enabled.                   | `true`          |
| `DB_NAME`               | The name of the database.                        | `app`           |
| `DB_HOST`               | The database host.                               | `localhost`     |
| `DB_PORT`               | The database port.                               | `5432`          |
| `DB_USERNAME`           | The database user.                               | `docker`        |
| `DB_PASSWORD`           | The database password.                           | `dock123`       |
| `MAILER_HOST`           | The SMTP host for sending emails.                | `mailhog`       |
| `MAILER_PORT`           | The SMTP port.                                   | `1025`          |

## 🧪 Testing

The project uses Codeception for testing. To run the full test suite (Acceptance, Functional, Unit):

```bash
make test
```

## 🔍 Static Analysis

The code is statically analyzed with Psalm. To run the analysis:

```bash
make psalm
```

## 📖 API Documentation

The API documentation is automatically generated from OpenAPI annotations in the source code.

*   **Swagger UI:** View the interactive documentation at `http://localhost:8091/docs`
*   **OpenAPI JSON:** The raw specification is available at `http://localhost:8091/docs/openapi.json`

### Authentication

Authorization is performed by sending an API token via the `X-Api-Key` header.

### Main Endpoints

*   `POST /register/`: Register a new user.
*   `POST /auth/`: Authenticate and receive an API token.
*   `GET /verify-email/{token}`: Verify a user's email address.
*   `POST /auth/resend-verification`: Resend the verification email.
*   ... and more. See the full documentation at `/docs`.