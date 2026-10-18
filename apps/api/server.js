const http = require('http');
const port = process.env.PORT || 4000;
http.createServer((req, res) => {
  res.writeHead(200, { 'content-type': 'application/json' });
  res.end(JSON.stringify({ ok: true, service: 'api', path: req.url }));
}).listen(port, () => console.log('api on ' + port));
