# Kindred Invoice

A Vite + React invoice workspace inspired by FusionInvoice's operating model and Kindred Innovia's calm visual language.

## Local development

```powershell
Copy-Item .env.example .env
$env:DEV_AUTH_BYPASS='true'
$env:DEV_AUTH_EMAIL='owner@kindredinnovia.com'
pnpm dev
```

Open `http://127.0.0.1:4173`.

## Cloudflare Zero Trust deployment

1. Publish the Node origin only through a Cloudflare Tunnel. Do not expose the origin port publicly.
2. Create a Cloudflare Access self-hosted application and an email/identity-provider policy.
3. Set `CF_ACCESS_TEAM_DOMAIN` and the application's `CF_ACCESS_AUD` audience tag. Do not set `DEV_AUTH_BYPASS` in production.
4. Keep the API and frontend behind the same Access application. Deny requests that reach the origin outside the tunnel/network policy.

The API validates the `Cf-Access-Jwt-Assertion` signature against Cloudflare's JWKS, plus issuer, audience, algorithm, and expiry. The asserted email becomes usable only after that validation. The easily forged `Cf-Access-Authenticated-User-Email` header is intentionally ignored.

## Production notes

- Replace the JSON store with PostgreSQL before multi-user production use.
- Add explicit organization membership and role tables before inviting staff from multiple organizations.
- Add immutable audit events for invoice lifecycle actions and payment-provider webhooks.
- Configure transactional email and PDF rendering as server-side jobs.
