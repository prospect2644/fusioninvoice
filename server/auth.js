import { createRemoteJWKSet, jwtVerify } from 'jose';

const normalize = (value = '') => value.trim().toLowerCase();
let verifier;

export function createAccessVerifier(env = process.env) {
  const teamDomain = (env.CF_ACCESS_TEAM_DOMAIN || '').replace(/\/$/, '');
  const audience = env.CF_ACCESS_AUD;
  if (!teamDomain || !audience) return null;
  const issuer = `${teamDomain}`;
  const jwks = createRemoteJWKSet(new URL(`${teamDomain}/cdn-cgi/access/certs`));
  return async (token) => jwtVerify(token, jwks, {
    issuer,
    audience,
    algorithms: ['RS256'],
    clockTolerance: 5,
  });
}

export async function requireIdentity(req, res, next) {
  try {
    const token = req.get('Cf-Access-Jwt-Assertion');
    if (token) {
      verifier ||= createAccessVerifier();
      if (!verifier) throw new Error('Cloudflare Access is not configured');
      const { payload } = await verifier(token);
      const email = normalize(payload.email);
      if (!email || !payload.sub) throw new Error('Access token is missing identity claims');
      req.identity = { id: payload.sub, email, source: 'cloudflare-access' };
      return next();
    }

    // Never trust CF-Access-Authenticated-User-Email on its own: clients can forge it.
    const isLoopback = ['127.0.0.1', '::1', '::ffff:127.0.0.1'].includes(req.socket.remoteAddress);
    if (process.env.DEV_AUTH_BYPASS === 'true' && isLoopback) {
      const email = normalize(process.env.DEV_AUTH_EMAIL || 'owner@kindredinnovia.com');
      req.identity = { id: `dev:${email}`, email, source: 'local-development' };
      return next();
    }
    return res.status(401).json({ error: 'A valid Cloudflare Access session is required.' });
  } catch {
    return res.status(401).json({ error: 'Access session could not be verified.' });
  }
}
