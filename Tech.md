## 2.1. Цель

Внедрить безопасную email-регистрацию через REST API с подтверждением почты, идемпотентностью, rate-limit, логированием/метриками и полной автоматизацией тестов.

## 2.2. Стек и стандарты

* PHP 8.3, Yii3 (actions/controllers, DI, config), Cycle ORM (entities, миграции, репозитории).
* PSR-12, `strict_types=1`, финальные классы, типы/`readonly`, `#[SensitiveParameter]` для пароля.
* Статика: PHPStan (max level), Rector (миграции стиля/советов), php-cs-fixer.
* Локали: i18n сообщений валидации/ошибок.

## 2.3. API контракты

### 2.3.1. POST `/auth/register`

**Headers:**
`Content-Type: application/json`
`Accept: application/json`
`Idempotency-Key: <uuid>` (опц.)

**Body:**

```json
{
  "email": "user@example.com",
  "password": "Str0ng!Passw0rd",
  "name": "John Doe",
  "locale": "ru_RU",
  "timezone": "Europe/Moscow",
  "marketing_consent": false
}
```

**Ответ (202):**

```json
{
  "verification_status": "pending",
  "message": "Verification email sent."
}
```

**Ошибки:**

* 409 — email уже существует (если уже активен).
* 422 — валидация.
* 429 — превышен лимит.
* 500 — общее.

### 2.3.2. POST `/auth/verify-email`

**Body:**

```json
{ "token": "<opaque-string>" }
```

**Успех (200):**

```json
{
  "verification_status": "verified",
  "user_id": "018f5c2e-...-v7"
}
```

**Ошибки:** 410 (expired), 422 (invalid/used), 404 (user not found по токену), 409 (already verified).

### 2.3.3. POST `/auth/resend-verification`

**Body:** `{ "email": "user@example.com" }`
**Успех (200/202):** письмо отправлено/запланировано.
**Ошибки:** 404 (если политика раскрытия не запрещает), 429 (частые запросы).

### 2.3.4. Формат ошибок (RFC 7807)

`Content-Type: application/problem+json`

```json
{
  "type": "https://errors.example.com/validation",
  "title": "Validation Failed",
  "status": 422,
  "detail": "email is invalid",
  "traceId": "c2f9..."
}
```

## 2.4. Доменные модели

**Entity `User` (final):**

* `id: UuidV7`
* `email: Email` (VO)
* `passwordHash: PasswordHash` (VO, Argon2id)
* `name: string|null`
* `locale: string|null`
* `timezone: string|null`
* `status: UserStatus` (`pending`, `active`, `blocked`)
* `emailVerifiedAt: DateTimeImmutable|null`
* `createdAt`, `updatedAt`

**Entity `EmailVerificationToken` (final):**

* `id: Uuid`
* `userId: Uuid`
* `tokenHash: string` (SHA-256 или Argon2id)
* `expiresAt: DateTimeImmutable`
* `usedAt: DateTimeImmutable|null`
* `createdAt: DateTimeImmutable`

**VO (final):** `Email`, `PasswordHash` (+фабрика `fromPlain()` с Argon2id), при необходимости `Locale`, `Timezone`.

**Enums:** `UserStatus`.

## 2.5. Репозитории/Сервисы

* `UserRepositoryInterface` / impl для Cycle.
* `EmailVerificationTokenRepositoryInterface` / impl.
* `RegisterUserService` — транзакционно:

    1. проверка уникальности (case-insensitive),
    2. создание `User(status=pending)`,
    3. генерация opaque-token, вычисление `tokenHash`, сохранение токена,
    4. публикация события `UserRegistered`.
* `VerifyEmailService` — валидация токена (не использован, не истёк), маркировка `usedAt`, установка `emailVerifiedAt`, `status=active`.
* `ResendVerificationService` — с rate-limit.
* `SendEmailVerificationJob` — письмо с ссылкой вида:
  `https://app.example.com/verify?token=<opaque>`.

## 2.6. HTTP слой (Yii3)

* Actions: `RegisterAction`, `VerifyEmailAction`, `ResendVerificationAction`.
* DTO/Form для запросов + атрибуты валидации.
* Единая ошибка → ProblemDetails.
* Идемпотентность: middleware (хранилище ключей + ответ кэша).
* Rate-limit: middleware (по IP и по email).

## 2.7. Безопасность

* Пароли: Argon2id (параметры через env, тесты на реальное хеширование).
* Токены: opaque (не JWT), одноразовые, TTL=24h (env).
* Секреты не логируем, пароли помечаем `#[SensitiveParameter]`.
* CORS: только доверенные origin’ы.
* HSTS, HTTPS-только.
* Возможность включить CAPTCHA на регистрацию/ресенд (фича-флаг).

## 2.8. Миграции Cycle ORM

* `users`: pk uuid, уникальный индекс на `LOWER(email)`, индексы на `status`, `created_at`.
* `email_verification_tokens`: pk uuid, fk `user_id`, индексы на `expires_at`, `used_at`.
* Скрипт очистки просроченных/использованных токенов (console command/cron).

## 2.9. Логи/Метрики/Аудит

* Логи уровня info/warning/error, без PII.
* Метрики: счетчики рег/верификаций/ресендов, rate-limit hits, время выполнения.
* traceId в ответах ошибок.

## 2.10. Тестирование

**Юнит-тесты:**

* VO `Email` (валид/невалид).
* VO `PasswordHash` (хеширование/проверка, невозможность извлечь plain).
* `RegisterUserService`: happy path, дубликат email, транзакции.
* `VerifyEmailService`: валид/просрочен/использован/повторная верификация.
* Rate-limit / идемпотентность (поведение мидлварей).

**Интеграционные:**

* Репозитории (CRUD, уникальность, индексы).
* Транзакционная согласованность user+token.
* Джоба отправки письма (через тестовый транспортер/фейк).

**API (HTTP):**

* `POST /register` (201/202), повтор с `Idempotency-Key` — тот же body.
* Валидационные ошибки (422) по каждому полю.
* `POST /verify-email` (200/410/422/409).
* `POST /resend-verification` (200/429).
* Негативные сценарии ≈30% (пустые поля, слабый пароль, неверный токен, дубли, лимиты).

**Нефункциональные:**

* Производительность (рег до 200мс на холодном кэше без I/O почты).
* Потокобезопасность: гонки на повторной верификации (single-use токен).
* Идемпотентность при сетевых ретраях.

## 2.11. OpenAPI 3.1 (фрагмент)

* Описать схемы `RegisterRequest`, `VerifyRequest`, `ProblemDetails`.
* Примеры payload/ответов.
* Теги: `Auth`.

## 2.12. DoD / Критерии приемки

* Все эндпоинты реализованы, соответствуют OpenAPI.
* Миграции прогоняются, схемы корректны.
* Тесты зеленые; покрытие домена ≥90%.
* Статика (phpstan max), стиль (php-cs-fixer) — без замечаний.
* Письмо приходит (в dev — в mailhog/фейк-драйвер).
* Повторная верификация — корректные коды (409/422).
* Rate-limit/идемпотентность работает.
* README с инструкцией запуска/переменными окружения.

## 2.13. Структура директорий (пример)
следуй структуре проекта.

Самое главное после каждых изменений запускай тесты. 
не правь файлы которые не относятся к заданию
