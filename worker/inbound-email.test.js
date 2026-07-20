import test from 'node:test';
import assert from 'node:assert/strict';
import { handleInboundEmailRoute } from './index.js';

test('reserved inbound email route fails closed without processing a message', async () => {
  const response=handleInboundEmailRoute(new Request('https://invoice.example/api/helpdesk/inbound-email',{method:'POST',body:'untrusted email payload'}));
  assert.equal(response.status,501);
  assert.deepEqual(await response.json(),{error:'Inbound helpdesk email is reserved but not configured.',code:'EMAIL_INGEST_NOT_CONFIGURED'});
});

test('reserved inbound email route allows POST only', () => {
  const response=handleInboundEmailRoute(new Request('https://invoice.example/api/helpdesk/inbound-email'));
  assert.equal(response.status,405);
  assert.equal(response.headers.get('allow'),'POST');
});
