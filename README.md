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

## Cloudflare Pages + Zero Trust deployment

Client account passwords and MFA seeds use a separate 32-byte encryption key. Generate it once and save it only as a Worker secret; changing it makes existing stored credentials unreadable.

```powershell
$bytes = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Fill($bytes)
[Convert]::ToBase64String($bytes) | pnpm dlx wrangler secret put CREDENTIAL_ENCRYPTION_KEY
pnpm dlx wrangler d1 execute invoice-db --remote --file migrations/0009_client_assets_accounts.sql
```

Never put `CREDENTIAL_ENCRYPTION_KEY` in frontend code or in a Vite-exposed variable.

1. Configure the Pages project with build command `pnpm build` and output directory `dist`.
2. Under **Settings > Bindings**, add the D1 database `invoice-db` with the variable name `DB` for both Production and Preview.
3. Under **Settings > Variables and Secrets**, add `CF_ACCESS_TEAM_DOMAIN=https://kindredinnovia.cloudflareaccess.com` and the Access application audience tag as `CF_ACCESS_AUD`.
4. Create a Cloudflare Access self-hosted application and email/identity-provider policy covering the production Pages/custom hostname.
5. Apply the D1 migration with `pnpm db:migrate:remote`, then deploy by pushing to the Git-connected branch.

The Pages Function in `functions/api/[[path]].js` handles `/api/*` and reads data through `env.DB`. It validates the `Cf-Access-Jwt-Assertion` signature against Cloudflare's JWKS, plus issuer, audience, algorithm, and expiry. The asserted email becomes usable only after that validation. The easily forged `Cf-Access-Authenticated-User-Email` header is intentionally ignored.

Do not place `CLOUDFLARE_API_TOKEN` in Pages runtime variables. A Git-connected Pages deployment does not need it, and application code must never receive an account deployment credential.

## Production notes

### Reserved inbound helpdesk email route

`POST /api/helpdesk/inbound-email` is reserved for a future email-provider webhook. It is intentionally disabled and currently returns `501` with code `EMAIL_INGEST_NOT_CONFIGURED`. It does not parse messages or create tickets.

Before enabling it, require a provider-specific signed webhook, reject replayed or expired requests, impose strict message and attachment limits, map recipients to workspaces server-side, and configure Cloudflare Access only for the exact webhook path. Never accept sender email headers as proof of identity or workspace membership.

- Cloudflare D1 must be bound in the Pages dashboard as `env.DB` and named `invoice-db`; `wrangler.jsonc` carries the same binding for Wrangler-based development/deployment.
- The `invoice-db` UUID is configured in `wrangler.jsonc`; use `pnpm db:info` to verify it against the active Cloudflare account.
- Build the local database with `pnpm db:migrate:local` or apply the schema to the existing remote database with `pnpm db:migrate:remote`.
- Migration `0002_invoice_items.sql` adds the line-item table required by the full-screen invoice editor and PDF-ready preview.
- Migration `0003_items_catalog.sql` adds the reusable products and services catalog shown in the Items tab.
- Migration `0004_subscriptions.sql` adds recurring client billing schedules for the Subscriptions tab.
- Migration `0005_expenses.sql` adds vendor expenses, client assignment, tax, and billing status for the Expenses tab.
- Migration `0006_tasks.sql` adds client-linked work tasks, due dates, assignees, completion, and workflow status.
- Migration `0007_helpdesk_tickets.sql` adds client helpdesk tickets, public/private notes, technician time tracking, and billing configuration.
- Migration `0008_ticket_expenses.sql` links billable expenses to a specific helpdesk ticket.
- The initial migration stores money as integer cents and scopes every business record to a workspace.
- Enforce `workspace_members` membership in every D1 API query before inviting staff from multiple organizations.
- Add immutable audit events for invoice lifecycle actions and payment-provider webhooks.
- Configure transactional email and PDF rendering as server-side jobs.
