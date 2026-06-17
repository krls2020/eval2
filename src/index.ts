// Weather dashboard — single Bun HTTP server.
// Serves the dashboard UI and proxies the public open-meteo API (no key required).
import { dashboardHtml } from "./ui.ts";

const PORT = Number(process.env.PORT ?? 3000);

const GEOCODE_API = "https://geocoding-api.open-meteo.com/v1/search";
const FORECAST_API = "https://api.open-meteo.com/v1/forecast";

function json(data: unknown, status = 200): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "content-type": "application/json; charset=utf-8" },
  });
}

// Proxy a query to an upstream open-meteo endpoint and relay the JSON back.
async function proxy(url: string): Promise<Response> {
  try {
    const res = await fetch(url, { signal: AbortSignal.timeout(10_000) });
    if (!res.ok) {
      return json({ error: `upstream ${res.status}` }, 502);
    }
    return json(await res.json());
  } catch (err) {
    return json({ error: String(err) }, 502);
  }
}

const server = Bun.serve({
  port: PORT,
  hostname: "0.0.0.0",
  async fetch(req) {
    const url = new URL(req.url);

    // Health endpoint for zerops_verify.
    if (url.pathname === "/status" || url.pathname === "/health") {
      return json({ status: "ok" });
    }

    // City search → coordinates.
    if (url.pathname === "/api/geocode") {
      const q = url.searchParams.get("q")?.trim();
      if (!q) return json({ error: "missing query param 'q'" }, 400);
      const target = `${GEOCODE_API}?name=${encodeURIComponent(q)}&count=8&language=en&format=json`;
      return proxy(target);
    }

    // Current weather + daily/hourly forecast for coordinates.
    if (url.pathname === "/api/weather") {
      const lat = url.searchParams.get("lat");
      const lon = url.searchParams.get("lon");
      if (!lat || !lon) return json({ error: "missing 'lat'/'lon'" }, 400);
      const params = new URLSearchParams({
        latitude: lat,
        longitude: lon,
        current: "temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m,wind_direction_10m",
        hourly: "temperature_2m,weather_code,precipitation_probability",
        daily: "weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset,precipitation_probability_max",
        timezone: "auto",
        forecast_days: "7",
      });
      return proxy(`${FORECAST_API}?${params.toString()}`);
    }

    // Dashboard UI.
    if (url.pathname === "/") {
      return new Response(dashboardHtml, {
        headers: { "content-type": "text/html; charset=utf-8" },
      });
    }

    return new Response("Not found", { status: 404 });
  },
});

console.log(`Weather dashboard listening on http://${server.hostname}:${server.port}`);
