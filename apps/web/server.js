const http = require('http');
const port = process.env.PORT || 3000;
const api = process.env.API_URL || '';
http.createServer((req, res) => {
  res.writeHead(200, { 'content-type': 'text/html' });
  res.end(`<h1>web</h1><p>api: ${api}</p>`);
}).listen(port, () => console.log('web on ' + port));
