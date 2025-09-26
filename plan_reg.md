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
  "password_confirm": "Str0ng!Passw0rd",
  "login": "JohnDoe"
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

### 2.3.2. GET /verify-email/{token}

**Успех (200):**

```json
{
  "status": "success",
  "data": {
    "status": "ok"
  }
}
```

**Ошибки:** 410 (expired), 422 (invalid/used), 404 (user not found по токену), 409 (already verified).

### 2.3.3. [x] POST `/auth/resend-verification`

**Body:** `{ "email": "user@example.com" }`
**Успех (200/202):** письмо отправлено/запланировано.
**Ошибки:** 404 (если политика раскрытия не запрещает), 429 (частые запросы).

### 2.3.4. Формат ошибок (RFC 7807)

`Content-Type: application/problem+json`

```json
{
  "type": "/docs/errors/validation",
  "title": "Validation Failed",
  "status": 422,
  "detail": "email is invalid"
}
```

## 2.4. Доменные модели

**Entity `User` (final):**

* `id: int`
* [x] `email: Email` (VO) - *поле добавлено как `string`, VO не реализован*
* `passwordHash: PasswordHash` (VO, Argon2id) - *реализовано как `string`*
* `login: string`
* [x] `locale: string|null` - *поле добавлено*
* [x] `timezone: string|null` - *поле добавлено*
* [x] `status: UserStatus` (`pending`, `active`, `blocked`) - *поле и Enum добавлены, не все статусы реализованы*
* [x] `emailVerifiedAt: DateTimeImmutable|null` - *поле добавлено*
* `createdAt`, `updatedAt`

**[x] Entity `EmailVerificationToken` (final):**

* `id: Uuid`
* `userId: Uuid`
* `tokenHash: string` (SHA-256 или Argon2id)
* `expiresAt: DateTimeImmutable`
* `usedAt: DateTimeImmutable|null`
* `createdAt: DateTimeImmutable`

**VO (final):** `Email`, `PasswordHash` (+фабрика `fromPlain()` с Argon2id), при необходимости `Locale`, `Timezone`.

**Enums:**
* [x] `UserStatus`. - *Enum создан*

## 2.5. Репозитории/Сервисы

* [x] `UserRepositoryInterface` / impl для Cycle.
* [x] `EmailVerificationTokenRepositoryInterface` / impl.
* [x] `RegisterUserService` — транзакционно:

    1. проверка уникальности (case-insensitive),
    2. создание `User(status=pending)`,
    3. генерация opaque-token, вычисление `tokenHash`, сохранение токена,
    4. публикация события `UserRegistered`.

    **Детализация реализации отправки письма (следующие шаги):**

    1.  [x] **Модифицировать `RegisterUserService`:** Внедрить публикацию события `UserRegistered` (согласно п.4 выше). - *реализовано*
    2.  [x] **Создать обработчик события:** Реализовать класс-слушатель (listener) для `UserRegistered`. - *реализован и исправлен*
    3.  [x] **Реализовать логику отправки письма:** Внутри слушателя. Для разработки использовать фейковый почтовый драйвер. - *реализовано*
    4.  **Написать тесты:** Покрыть тестами публикацию события и работу обработчика.
* `VerifyEmailService` — валидация токена (не использован, не истёк), маркировка `usedAt`, установка `emailVerifiedAt`, `status=active`.
* [x] `ResendVerificationService` — с rate-limit. - *реализовано*
* [x] `SendEmailVerificationJob` — письмо с ссылкой вида:
  `https://app.example.com/verify?token=<opaque>`. - *реализовано в слушателе, не через Job*

## 2.6. HTTP слой (Yii3)

* [x] HTTP-слой: Вместо отдельных классов-действий (Actions) используется `AuthController`, содержащий методы для каждого эндпоинта (метод `register` реализован).
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

* [x] `users`: pk uuid, уникальный индекс на `LOWER(email)`, индексы на `status`, `created_at`. - *миграции созданы, добавлен индекс на LOWER(email)*
* [x] `email_verification_tokens`: pk uuid, fk `user_id`, индексы на `expires_at`, `used_at`. - *миграция создана*
* [x] Скрипт очистки просроченных/использованных токенов (console command/cron).

## 2.9. Логи/Метрики/Аудит

* Логи уровня info/warning/error, без PII.
* Метрики: счетчики рег/верификаций/ресендов, rate-limit hits, время выполнения.
* traceId в ответах ошибок.

## 2.10. Тестирование

**Юнит-тесты:**

* VO `Email` (валид/невалид).
* VO `PasswordHash` (хеширование/проверка, невозможность извлечь plain).
* [x] `RegisterUserService`: happy path, дубликат email, транзакции. - *тесты написаны и проходят*
* [x] `TokenCleanupCommand`: happy path и пустой результат. - *тесты написаны и проходят*
* [x] `VerifyEmailService`: валид/просрочен/использован/повторная верификация. - *тесты написаны и проходят*
* [x] Rate-limit / идемпотентность (поведение мидлварей). - *rate-limit реализован, идемпотентность — нет*

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
* [x] Миграции прогоняются, схемы корректны.
* Тесты зеленые; покрытие домена ≥90%.
* Статика (phpstan max), стиль (php-cs-fixer) — без замечаний.
* [x] Письмо приходит (в dev — в mailhog/фейк-драйвер). - *реализовано*
* Повторная верификация — корректные коды (409/422).
* Rate-limit/идемпотентность работает.
* README с инструкцией запуска/переменными окружения.

## 2.13. Структура директорий (пример)
следуй структуре проекта.

Самое главное после каждых изменений запускай тесты. 
не правь файлы которые не относятся к заданию

рые не относятся к заданию

------------------------------------------------


1. API и Обработка Ошибок
* Идемпотентность: Middleware для обработки заголовка Idempotency-Key не реализован.

2. Безопасность
* Конфигурация паролей: Параметры для хеширования паролей (Argon2id) не вынесены в файл .env.
* Защита логов: Атрибут #[SensitiveParameter] не используется для полей с паролями, что может привести к их утечке в логи.
* Политики безопасности: Не настроены строгие политики для CORS и HSTS.
* CAPTCHA: Защита от ботов на эндпоинтах регистрации и повторной отправки письма не реализована.

3. Качество Кода и Доменные модели
* Value Objects: Email и PasswordHash до сих пор реализованы как простые строки (string), а не как полноценные Value Objects.
* Статический анализ: Неизвестно, пройдены ли проверки phpstan и php-cs-fixer без замечаний.

4. Тестирование
* Юнит-тесты:
    * [x] Написан тест для обработчика событий UserRegisteredListener (отправка письма).
    * [x] Написаны тесты для Value Objects (Email, PasswordHash).
* Интеграционные тесты: Отсутствуют тесты, проверяющие:
    * Корректную работу репозиториев с базой данных.
    * Транзакционную согласованность (например, атомарное создание user + token).
* API тесты: Покрытие неполное. Не хватает тестов на:
    * Полный цикл верификации email (переход по ссылке).
    * Работу эндпоинта resend-verification.
    * Проверку идемпотентности и лимитов запросов.
    * Множество других негативных сценариев.
5. Документация
* OpenAPI: Спецификация не завершена. Описаны не все эндпоинты и схемы данных.
* README: Неизвестно, обновлена ли документация по запуску и настройке проекта.

6. Логи и Метрики
* Логирование и метрики: Не настроен сбор метрик (счетчики регистраций, ошибок) и трассировка запросов (traceId).
