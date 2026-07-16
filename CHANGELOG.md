# Changelog

Every production push that changes application behavior must add a versioned entry here and update the version in `package.json`.

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
