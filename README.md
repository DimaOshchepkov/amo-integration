# amo-integration

Лендинг с формой заявки: отправляет сделки с прикреплёнными контактами в amoCRM (OAuth-интеграция). Laravel 13 + Inertia + React + TypeScript.

## Запуск (Sail)

```bash
cp .env.example .env
docker compose up -d
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
npm install
npm run dev
```

Для OAuth-подключения amoCRM заполните в `.env` ключи `AMOCRM_CLIENT_ID` (UUID интеграции), `AMOCRM_CLIENT_SECRET`, `AMOCRM_REDIRECT_URI`, `AMOCRM_SUBDOMAIN` и пройдите `/amocrm/connect`.

Продакшен-сборка фронта: `npm run build`.

## Ключевые моменты

- Форма заявки (имя, email, телефон, цена): валидация zod на клиенте и FormRequest на сервере
- Флаг «провёл на сайте >30 сек» — в кастомное поле сделки (`AMOCRM_LEAD_30S_FIELD_ID`)
- OAuth: `/amocrm/connect` редирект-флоу, токены хранятся в БД в зашифрованном виде, access-токен обновляется автоматически (это нужно сделать один раз клиенту, пользователи ничего не делают)

## Демо

[Смотреть демо](public/videos/demo-amo.gif)

## Git

В проекте также продемонстрирована работа с git (было указано как желательное в тз)
