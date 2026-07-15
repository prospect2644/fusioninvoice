import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

const packageJson=JSON.parse(readFileSync(new URL('../package.json',import.meta.url),'utf8'));
const changelog=readFileSync(new URL('../CHANGELOG.md',import.meta.url),'utf8');
const heading=`## [${packageJson.version}]`;
if(!changelog.includes(heading)){
  console.error(`CHANGELOG.md must contain ${heading} for the package version.`);
  process.exit(1);
}

const before=process.env.GITHUB_EVENT_BEFORE;
if(before&&!/^0+$/.test(before)){
  const changed=execFileSync('git',['diff','--name-only',before,'HEAD'],{encoding:'utf8'}).trim().split(/\r?\n/).filter(Boolean);
  const appChanged=changed.some(file=>/^(src|worker|server|functions|migrations)\//.test(file)||['package.json','vite.config.js'].includes(file));
  if(appChanged&&(!changed.includes('CHANGELOG.md')||!changed.includes('package.json'))){
    console.error('Application changes require both CHANGELOG.md and package.json version updates.');
    process.exit(1);
  }
}
console.log(`Release history is valid for v${packageJson.version}.`);
