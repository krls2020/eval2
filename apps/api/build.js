// Build for apps/api: copies the server into dist/.
// Fails on purpose inside GitHub Actions (GITHUB_ACTIONS=true) to try out how a
// release behaves when CI fails; builds everywhere else (Zerops, locally) pass.
const fs = require('fs');

if (process.env.GITHUB_ACTIONS === 'true') {
  console.error('api build: failing on purpose in GitHub Actions (release CI test)');
  process.exit(1);
}

fs.mkdirSync('dist', { recursive: true });
fs.copyFileSync('server.js', 'dist/server.js');
