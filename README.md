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

1. Create a Cloudflare Access self-hosted application and an email/identity-provider policy for the Worker hostname.
2. In Worker settings, set `CF_ACCESS_TEAM_DOMAIN` to `https://kindredinnovia.cloudflareaccess.com` and `CF_ACCESS_AUD` to the Access application audience tag.
3. Apply the D1 migration with `pnpm db:migrate:remote`.
4. Deploy the Worker, Vite assets, API, and D1 binding together with `pnpm deploy`.

The deployed Worker handles `/api/*` before the SPA assets and reads data through `env.DB`. It validates the `Cf-Access-Jwt-Assertion` signature against Cloudflare's JWKS, plus issuer, audience, algorithm, and expiry. The asserted email becomes usable only after that validation. The easily forged `Cf-Access-Authenticated-User-Email` header is intentionally ignored.

## Production notes

- Cloudflare D1 is bound in `wrangler.jsonc` as `env.DB` and named `invoice-db`.
- The `invoice-db` UUID is configured in `wrangler.jsonc`; use `pnpm db:info` to verify it against the active Cloudflare account.
- Build the local database with `pnpm db:migrate:local` or apply the schema to the existing remote database with `pnpm db:migrate:remote`.
- The initial migration stores money as integer cents and scopes every business record to a workspace.
- Enforce `workspace_members` membership in every D1 API query before inviting staff from multiple organizations.
- Add immutable audit events for invoice lifecycle actions and payment-provider webhooks.
- Configure transactional email and PDF rendering as server-side jobs.
