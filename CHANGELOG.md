# Changelog

Every production push that changes application behavior must add a versioned entry here and update the version in `package.json`.

## [0.22.0] - 2026-07-19

### Added

- Added CSV and generated PDF exports to completed financial reports.
- Added shared Year to Date, Current Month, All Time, and Custom Range report scopes, defaulting to Year to Date.
- Added a Total Revenue dashboard card based on payments received year to date.

### Changed

- Dashboard billing metrics now default to the Year to Date period and display four responsive KPI cards.

## [0.21.4] - 2026-07-19

### Fixed

- Added horizontal scrolling to data tables and full workspaces when browser zoom makes content wider than the viewport.
- Added independent vertical scrolling to the fixed sidebar and overflow handling to full-page screens and dialogs.

## [0.21.3] - 2026-07-19

### Added

- Subscription rows now open a full detail workspace.
- Added included, used, remaining, and overage hour totals with linked-ticket activity.

## [0.21.2] - 2026-07-19

### Changed

- Ticket creation now restores the last contact used for each company or defaults to an authorized contact when no prior choice exists.
- Technical Support now visibly defaults to an active Managed IT subscription for the selected company.
- Moved Service Board directly below the ticket title.

## [0.21.1] - 2026-07-19

### Changed

- Simplified dashboard KPIs to Sent Invoices, Payments Collected, and Payments Due.
- Removed draft, quote, and separate overdue KPI cards; overdue counts now appear inside Payments Due.
- Added coordinated blue, teal, and amber visual treatments for the remaining billing cards.

## [0.21.0] - 2026-07-19

### Added

- Added optional time logging directly from Internal and External ticket notes.
- Added External-note client delivery intent, per-ticket recipient lists, and persistent delivery metadata for the future email integration.

### Security

- Client delivery controls are unavailable for Internal notes, and recipient addresses are validated before storage.

## [0.20.0] - 2026-07-19

### Changed

- Split ticket updates and notes into External and Internal tabs with independent counts and filtered timelines.
- Renamed public customer updates to External and private technician notes to Internal throughout the ticket UI while retaining API compatibility.

## [0.19.11] - 2026-07-19

### Added

- Built the Commission report with user, status, date, commission-type, and invoice-payment filters.
- Added fixed and percentage commission calculations with paid and pending totals.

## [0.19.10] - 2026-07-19

### Added

- Built the Credits and Pre-payments report with client, date, and application-status filters.
- Added credit, prepayment, total-value, and available-balance summaries with ledger detail.

## [0.19.9] - 2026-07-19

### Added

- Built the Invoices by Client report with client, status, tag, and invoice-date filters.
- Added invoice totals and optional detailed invoice-line reporting.

## [0.19.8] - 2026-07-19

### Added

- Built the Subscription List report around subscription next-run dates.
- Added scheduled value, active subscription, managed-IT allotment, and recurring schedule details.

## [0.19.7] - 2026-07-19

### Added

- Built the Tax Report with invoice-sent or payment-collected revenue models and expense-type filtering.
- Added sales-tax, expense-tax, net-tax summaries, and detailed or summarized tax activity.

## [0.19.6] - 2026-07-19

### Added

- Built the dedicated Revenue by Client report with invoice-sent or payment-collected revenue models.
- Added date filtering, client-name or revenue ordering, revenue shares, and client activity totals.

## [0.19.5] - 2026-07-19

### Added

- Built the Profit and Loss report with invoice-sent or payment-collected revenue models.
- Added date filtering, expense-tax treatment, net-profit and margin summaries, and expense-category detail.

## [0.19.4] - 2026-07-19

### Added

- Built the Payments Collected report with payment-method, date, prepayment, other-income, tag, and detail-level options.
- Added payment totals and detailed or payment-method summary results.

## [0.19.3] - 2026-07-19

### Added

- Built the Item Sales report with invoice-line filters, date basis, unpaid handling, ordering, and discount allocation.
- Added quantity, gross sales, allocated discount, and net-sales summaries with detailed item rows.

## [0.19.2] - 2026-07-19

### Added

- Built the Expense List report with date, category, vendor, order, and grouping options.
- Added expense totals, tax totals, and grouped report detail generated from workspace expenses.

## [0.19.1] - 2026-07-19

### Added

- Built the Client Statement report with client, status, tag, date-range, and paid-invoice filters.
- Added statement totals and matching invoice activity after running the report.

## [0.19.0] - 2026-07-19

### Added

- Added twelve report submenu destinations with refresh-safe report URLs.
- Added styled placeholder workspaces for report layouts that are still being defined.

### Changed

- Moved the existing financial activity report under Revenue by Client.

## [0.18.2] - 2026-07-19

### Changed

- Dashboard and all menu-page headers now remain sticky at the top while content scrolls.
- Sticky headers use consistent light and dark scroll shadows and remain above tables and cards.

## [0.18.1] - 2026-07-19

### Added

- Reserved `POST /api/helpdesk/inbound-email` for a future signed email-provider webhook.
- Added a stable `EMAIL_INGEST_NOT_CONFIGURED` response for future integration testing.

### Security

- The reserved inbound-email route fails closed with HTTP 501 and performs no parsing, ticket creation, or database writes.

## [0.18.0] - 2026-07-19

### Added

- Subscriptions can be designated as Managed IT services with a shared hourly support allotment.
- Tickets now have a service board selector defaulting to Technical Support.
- Technical Support tickets automatically inherit the client's active Managed IT subscription.
- Ticket plan usage shows remaining included hours or hourly overage value after the allotment is depleted.

### Security

- Managed-service routing and client hourly-rate fallback are resolved and validated by the server rather than trusted from browser input.

## [0.17.0] - 2026-07-19

### Changed

- Client, contact, asset, account, item, subscription, payment, expense, ticket, and task creation now opens as a full workspace page instead of a centered modal.
- The side menu remains visible and usable while creating records.
- Creation pages now use the same full-page Kindred styling in light and dark modes.

## [0.16.0] - 2026-07-19

### Added

- Invoice records now include a Tasks tab showing tasks linked to that invoice.
- Ticket records now include an in-record Tasks section reached from the ticket workspace tab.
- Linked task views show title, task number, due date, assignee, and status.

## [0.15.9] - 2026-07-19

### Added

- New tasks must be linked to exactly one existing invoice or helpdesk ticket.
- Task client ownership is inherited from the selected parent record and validated server-side.
- The task list now displays and searches the linked invoice or ticket reference.

## [0.15.8] - 2026-07-19

### Changed

- Restored the original rounded Kindred ticket workspace instead of the later ConnectWise-inspired compact layout.
- Retained functional ticket navigation, deletion, status, notes, time tracking, expenses, and record URLs.

## [0.15.7] - 2026-07-19

### Fixed

- The Preparing your workspace screen now fills the entire viewport at the app's 80% display scale.
- Protected-workspace loading errors no longer leave a grey strip below the blue background.

## [0.15.6] - 2026-07-16

### Added

- App sections now use readable browser paths and remain selected after refresh.
- Invoice, client, and helpdesk ticket records now have direct, refresh-safe URLs.
- Browser Back and Forward navigation now restores the matching app screen.

## [0.15.5] - 2026-07-16

### Fixed

- Invoice saving now exits the busy state after refreshing the saved invoice in place.
- Successful item saves return the invoice to preview mode instead of remaining frozen on Saving.
- Invoice status updates now clear their busy state after refreshing.

## [0.15.4] - 2026-07-15

### Changed

- Saving invoice item changes now refreshes and keeps the saved invoice open.
- Invoice status updates also remain on the current invoice.

### Added

- Invoice and estimate editors include a Cancel Changes action.
- Unsaved document changes trigger an in-app warning before sidebar navigation.
- Browser refresh, tab close, and window close use standard unsaved-change protection.

## [0.15.3] - 2026-07-15

### Added

- Client records now include an optional street address field.
- Street addresses appear in client creation, editing, save confirmation, list location, record details, invoice Bill To, and exported PDFs.

### Database

- Added migration `0013_client_address.sql` for client street addresses.

## [0.15.2] - 2026-07-15

### Fixed

- Replaced the decorative ticket workspace labels with real clickable navigation buttons.

### Added

- Ticket tabs now open Tasks, client Configurations, Products, Activities, Time, and Expenses destinations.
- Ticket tabs include active, hover, count, overflow, and dark-mode states.

## [0.15.1] - 2026-07-15

### Changed

- Helpdesk text search now searches globally across all tickets and temporarily ignores company, status, and closed-ticket filters.
- Clearing the search restores the selected helpdesk filters.
- Ticket search now also matches contact email addresses.

## [0.15.0] - 2026-07-15

### Added

- New estimates use the same full-screen line-item workspace as invoices.
- Estimates support catalog items, one-time items, subscription references, totals, notes, and custom fields.
- Any invoice can be converted into an estimate, including invoices created without an estimate.
- An invoice created from an estimate can be converted back, restoring that estimate to draft status.

### Changed

- Estimate-to-invoice conversion now preserves individual line items and their catalog or subscription references.

### Database

- Added migration `0012_estimate_items.sql` for structured estimate line items.

## [0.14.4] - 2026-07-15

### Changed

- Dark mode now uses one consistent midnight, slate, and cyan palette across the workspace.
- Panels, tables, forms, tabs, statuses, modals, client records, and ticket screens have coordinated dark contrast.
- Hover, alternating-row, placeholder, and muted-text colors were refined to avoid clashes.
- Printable invoice paper remains white for accurate PDF and print output.

## [0.14.3] - 2026-07-15

### Fixed

- Inactive client record tabs remain visible when hovered.
- Client tab hover colors now use a readable Kindred blue treatment in light and dark modes.

## [0.14.2] - 2026-07-15

### Changed

- The ticket workspace now uses a denser ConnectWise-inspired service-desk layout in the Kindred blue theme.
- Ticket summary, metrics, activity notes, time tracking, forms, and controls use compact operational panels.
- The ticket title remains prominent with tracked time and billable value cards directly beneath it.

## [0.14.1] - 2026-07-15

### Added

- Helpdesk tickets can be permanently deleted from within the ticket using an in-app confirmation.
- The ticket list includes a Show closed checkbox.

### Changed

- Closed tickets are hidden from the helpdesk list by default.
- Deleting a ticket removes its notes and time entries while preserving linked expenses as unassigned expenses.

## [0.14.0] - 2026-07-15

### Added

- Client records now have dedicated Client, Billing, Assets & Accounts, Tickets, Invoices, and Quotes tabs.
- Client ticket history includes contact, status, billing method, and tracked time.

### Changed

- Assets and secure accounts are grouped into one client documentation area.
- Invoices and quotes have focused client views with direct creation actions.

## [0.13.3] - 2026-07-15

### Fixed

- Workspace loading now remains backward compatible while invoice-reference migration `0011` is pending.
- Missing new invoice-item columns no longer prevent the entire workspace from opening.

## [0.13.2] - 2026-07-15

### Added

- Contacts can be created directly from the global Contacts directory.
- Contact creation requires selecting an existing client from a dropdown.

## [0.13.1] - 2026-07-15

### Changed

- Rebuilt the loading screen with the CRM's dark navy and cyan-blue palette.
- Added the official Kindred Innovia logo, wordmark, tagline, and branded loading indicator.
- Updated the protected-workspace error background to match the new UI.

## [0.13.0] - 2026-07-15

### Added

- Active subscriptions can be selected as linked invoice line items.
- Subscription invoice references default to quantity 1 and `$0.00` and remain non-billable.
- Invoice line items persist their catalog-item or subscription source reference.

### Changed

- Subscription-backed ticket billing is explicitly presented as a zero-dollar covered service.

### Security

- The API verifies referenced subscriptions belong to the invoice client and prevents changing their rate above zero.

### Fixed

- Removed a stray non-SQL token from the historical invoice-items migration so clean database setup succeeds.

## [0.12.1] - 2026-07-15

### Added

- Payment records can be edited through an in-app modal.
- Payment records can be permanently deleted after an in-app confirmation.

### Security

- Payment updates and deletions are workspace scoped.
- Edited payment amounts cannot exceed the invoice total after accounting for other payments.

## [0.12.0] - 2026-07-15

### Added

- Global Contacts menu and directory.
- Contact search and client-company filtering.
- Contact preview columns for client, name, email, phone, title, and authorization status.
- Client names in the directory open the associated full client record.

## [0.11.6] - 2026-07-15

### Added

- Ticket creation can create a new contact for the selected client without leaving the ticket modal.
- Newly created contacts are automatically selected for the ticket.

## [0.11.5] - 2026-07-15

### Changed

- New tickets now require a saved contact belonging to the selected client company.
- Removed one-time contact name and email fields from ticket creation.
- Ticket API validation rejects missing or cross-company contact references.

## [0.11.4] - 2026-07-15

### Changed

- Replaced the temporary CRM initials with the official Kindred Innovia heart/tree logo from the public website.
- Updated the sidebar wordmark to Kindred Innovia and Careful Technology.

## [0.11.3] - 2026-07-15

### Changed

- Replaced the remaining gray-green application bars with a consistent dark navy blue.
- Active navigation and highlighted controls use the lighter Kindred cyan-blue.

## [0.11.2] - 2026-07-15

### Changed

- Removed the redundant identity bar from the application shell.
- Verified identity details and logout remain available under Settings.

## [0.11.1] - 2026-07-15

### Changed

- Item company fields now use a dropdown of existing client companies instead of free-text entry.
- The invoice inline catalog-item creator uses the same company dropdown.

## [0.11.0] - 2026-07-15

### Added

- Client contact records with name, email, phone, title, and authorized-user status.
- Saved client contacts can be selected when creating helpdesk tickets.
- Client assets for computers, servers, network devices, printers, mobile devices, and other equipment.
- Encrypted client accounts with password reveal auditing and server-generated TOTP MFA codes.
- Client record pencil editor with an in-app review and save-confirmation modal.
- Version label in the application sidebar and automated release-history enforcement.

### Changed

- Invoice Additional, Notes, and Payments tabs are interactive.
- Invoice PDF export downloads a generated invoice PDF instead of opening browser print preview.
- Record screens retain the global side navigation.
- Application interface uses the Kindred Innovia theme and 80% visual scale.

### Security

- Credential fields are encrypted with AES-256-GCM using a Worker-only secret.
- Credential and MFA reveals are logged to a workspace audit table.
- Native browser dialogs were removed in favor of controlled in-app modals.

## Earlier development history

- `9ec99fb` App-based confirmation dialogs.
- `7668a11` Client record editing.
- `9b607eb` Client contacts and ticket integration.
- `06cc81d` Direct invoice PDF generation.
- `2b8e226` Secure client assets and accounts.
- `0cc6dfc` Interactive invoice detail tabs.
- `7043e67` Helpdesk ticketing module.
- `029339b` Ticket-linked expenses.
- `9eb01e9` Editable invoice catalog items.
