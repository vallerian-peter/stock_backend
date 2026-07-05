# Professional Minimal Laravel Flow

This folder is an isolated study example. It is not wired into your live app.

Use it to learn this request flow:

`Route -> Controller -> FormRequest -> Service -> Model -> Event -> Listener -> SMS Gateway -> Resource -> JSON`

## Why this is the minimal professional structure

- `Controller`: thin, HTTP only
- `FormRequest`: validation and authorization
- `Service`: business logic
- `Model`: persistence with Eloquent
- `Event + Listener`: side effects like SMS
- `Resource`: stable API response shape

## Example features in this folder

- Auth flow
  - `POST /api/v1/auth/login`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/logout`
- User flow
  - `GET /api/v1/users`
  - `POST /api/v1/users`
  - `GET /api/v1/users/{user}`
  - `PUT /api/v1/users/{user}`
  - `DELETE /api/v1/users/{user}`
- Event-driven SMS side effects
  - login alert SMS
  - user created SMS
  - user updated SMS
  - user deleted SMS

## The exact responsibility of each layer

### 1. Route

Maps the URL to a controller action.

### 2. Controller

Accepts the request, calls a service, returns a resource.

Controllers should not:

- validate large payloads inline
- hash passwords
- write complex queries
- send SMS directly

### 3. FormRequest

Validates input before the controller runs.

### 4. Service

Owns business logic:

- login user
- create token
- hash password
- create/update/delete user
- dispatch domain events

### 5. Event + Listener

Moves side effects out of the service.

The service says: "this business event happened."

The listener says: "when that happens, send an SMS."

### 6. Resource

Controls the JSON shape returned to the frontend.

## File map

```text
examples/professional-minimal-flow/
├── routes/api.php
├── app/Http/Controllers/Api/V1
├── app/Http/Requests
├── app/Http/Resources
├── app/Services
├── app/Events
├── app/Listeners
├── app/Models
├── app/Providers
└── database/migrations
```

## One full request walkthrough

### Create user

1. `POST /api/v1/users`
2. `UserController@store`
3. `StoreUserRequest` validates payload
4. `UserService::store()` hashes password and creates user
5. `UserCreated` event is dispatched
6. `SendUserCreatedSms` listener receives the event
7. `SmsGateway` sends the message
8. `UserResource` returns clean JSON

## Production note

The example listeners implement `ShouldQueue`. That is the professional pattern for SMS or email side effects. Do not send SMS directly inside a controller.

## If you want to copy this into the real app later

Move the files into the matching real Laravel folders:

- `app/...`
- `routes/api.php`
- `database/migrations/...`

Then register the routes, run migrations, and point `SmsGateway` to a real provider.
