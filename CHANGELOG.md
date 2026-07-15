# Changelog

Every production push that changes application behavior must add a versioned entry here and update the version in `package.json`.

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
