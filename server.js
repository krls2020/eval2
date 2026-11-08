import { createServer } from 'node:http';

const port = Number(process.env.PORT || 3000);

const server = createServer((request, response) => {
  if (request.url === '/health') {
    response.writeHead(200, { 'Content-Type': 'application/json' });
    response.end(JSON.stringify({ status: 'ok' }));
    return;
  }

  if (request.url === '/') {
    response.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('Hello from Node.js!\n');
    return;
  }

  response.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
  response.end('Not found\n');
});

server.listen(port, '0.0.0.0', () => {
  console.log(`Listening on port ${port}`);
});

process.on('SIGTERM', () => server.close());
process.on('SIGINT', () => server.close());
