# Minimal Node.js app

A dependency-free HTTP app using Node.js 26.

Run `npm start` (or `npm run dev` for file watching). It listens on
`0.0.0.0:3000` by default; set `PORT` to override it.

- `/`: plain-text greeting
- `/health`: JSON health response
- Other paths: HTTP 404

Run `npm run check` to check JavaScript syntax.

`zerops.yaml` contains the development setup and an optional production
setup. The paired preview is intentionally left unbuilt.
