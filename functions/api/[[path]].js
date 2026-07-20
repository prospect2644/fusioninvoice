import { handleApi, handleInboundEmailRoute } from '../../worker/index.js';

export function onRequest({ request, env }) {
  if(new URL(request.url).pathname==='/api/helpdesk/inbound-email')return handleInboundEmailRoute(request);
  return handleApi(request, env);
}
