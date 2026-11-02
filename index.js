const http = require('node:http');

http.createServer((request, response) => {
  response.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
  response.end('ok');
}).listen(3000, '0.0.0.0');
