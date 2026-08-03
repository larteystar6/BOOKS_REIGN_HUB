# API Contracts - Authentication

## POST /api/auth/login

Request
- Content-Type: application/json
- Body:
  {
    "email": "user@example.com",
    "password": "plaintext-password"
  }

Response 200
{
  "token": "<jwt>",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "uuid": "...",
    "username": "...",
    "email": "...",
    "role": { "uuid":"...", "name":"..." },
    "permissions": ["products:read","sales:create"]
  }
}

Errors
- 401: { "error": "invalid_credentials" }
- 422: validation errors

Notes
- Uses JWT authentication. Tokens are issued with expiration and must be sent on subsequent requests in the Authorization header: `Authorization: Bearer <token>`.

## POST /api/auth/logout

Request
- Headers: Authorization: Bearer <token>

Response 200
{ "success": true }

## GET /api/auth/me

Request
- Headers: Authorization: Bearer <token>

Response 200
{ "user": { ... } }
