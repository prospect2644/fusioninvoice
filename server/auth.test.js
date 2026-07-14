import test from 'node:test';
import assert from 'node:assert/strict';
import { createAccessVerifier } from './auth.js';

test('production verifier fails closed without Access configuration', () => {
  assert.equal(createAccessVerifier({}), null);
});

test('email header is never an authentication input', async () => {
  const old = process.env.DEV_AUTH_BYPASS;
  delete process.env.DEV_AUTH_BYPASS;
  const req = { get: () => undefined, socket: { remoteAddress: '203.0.113.9' }, headers: { 'cf-access-authenticated-user-email': 'admin@example.com' } };
  let status;
  const res = { status: n => (status = n, res), json: body => body };
  const { requireIdentity } = await import('./auth.js');
  await requireIdentity(req, res, () => assert.fail('spoofed header authenticated'));
  assert.equal(status, 401);
  process.env.DEV_AUTH_BYPASS = old;
});
