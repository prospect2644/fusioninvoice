import { handleApi } from '../../worker/index.js';

export function onRequest({ request, env }) {
  return handleApi(request, env);
}
