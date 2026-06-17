// The dashboard UI as a single self-contained HTML document.
export const dashboardHtml = /* html */ `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Weather Dashboard</title>
<style>
  :root {
    --bg: #0f172a;
    --card: #1e293b;
    --card2: #273449;
    --text: #e2e8f0;
    --muted: #94a3b8;
    --accent: #38bdf8;
    --border: #334155;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    background: radial-gradient(1200px 600px at 70% -10%, #1e3a5f 0%, var(--bg) 55%);
    color: var(--text);
    min-height: 100vh;
  }
  .wrap { max-width: 900px; margin: 0 auto; padding: 32px 20px 64px; }
  h1 { font-size: 1.6rem; margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
  .sub { color: var(--muted); margin: 0 0 24px; font-size: .9rem; }
  .search { display: flex; gap: 8px; margin-bottom: 24px; position: relative; }
  .search input {
    flex: 1; padding: 14px 16px; border-radius: 12px; border: 1px solid var(--border);
    background: var(--card); color: var(--text); font-size: 1rem; outline: none;
  }
  .search input:focus { border-color: var(--accent); }
  .search button {
    padding: 0 22px; border-radius: 12px; border: 0; background: var(--accent);
    color: #04222e; font-weight: 600; font-size: 1rem; cursor: pointer;
  }
  .results {
    position: absolute; top: 56px; left: 0; right: 88px; z-index: 5;
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    overflow: hidden; box-shadow: 0 12px 32px rgba(0,0,0,.4);
  }
  .results div { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid var(--border); }
  .results div:last-child { border-bottom: 0; }
  .results div:hover { background: var(--card2); }
  .results small { color: var(--muted); }
  .current {
    background: var(--card); border: 1px solid var(--border); border-radius: 18px;
    padding: 24px; margin-bottom: 20px;
  }
  .current .top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
  .current .place { font-size: 1.25rem; font-weight: 600; }
  .current .place small { display: block; color: var(--muted); font-weight: 400; font-size: .85rem; margin-top: 2px; }
  .temp { font-size: 3.4rem; font-weight: 700; line-height: 1; }
  .icon { font-size: 3.4rem; }
  .desc { color: var(--muted); margin-top: 4px; }
  .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-top: 22px; }
  .stat { background: var(--card2); border-radius: 12px; padding: 12px 14px; }
  .stat .label { color: var(--muted); font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
  .stat .value { font-size: 1.15rem; font-weight: 600; margin-top: 4px; }
  h2 { font-size: 1rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin: 28px 0 12px; }
  .forecast { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 12px; }
  .day { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 16px 12px; text-align: center; }
  .day .dow { font-weight: 600; }
  .day .di { font-size: 2rem; margin: 8px 0; }
  .day .hi { font-weight: 600; }
  .day .lo { color: var(--muted); }
  .msg { color: var(--muted); padding: 16px 0; }
  .err { color: #f87171; }
  .hidden { display: none; }
</style>
</head>
<body>
  <div class="wrap">
    <h1>🌤️ Weather Dashboard</h1>
    <p class="sub">Powered by open-meteo · running on Bun + Zerops</p>

    <div class="search">
      <input id="q" type="text" placeholder="Search for a city…" autocomplete="off" />
      <button id="go">Search</button>
      <div id="results" class="results hidden"></div>
    </div>

    <div id="msg" class="msg">Search for a city to see the weather.</div>
    <div id="current" class="current hidden"></div>
    <div id="forecastWrap" class="hidden">
      <h2>7-day forecast</h2>
      <div id="forecast" class="forecast"></div>
    </div>
  </div>

<script>
const WMO = {
  0:["Clear sky","☀️"],1:["Mainly clear","🌤️"],2:["Partly cloudy","⛅"],3:["Overcast","☁️"],
  45:["Fog","🌫️"],48:["Rime fog","🌫️"],
  51:["Light drizzle","🌦️"],53:["Drizzle","🌦️"],55:["Dense drizzle","🌦️"],
  56:["Freezing drizzle","🌧️"],57:["Freezing drizzle","🌧️"],
  61:["Light rain","🌦️"],63:["Rain","🌧️"],65:["Heavy rain","🌧️"],
  66:["Freezing rain","🌧️"],67:["Freezing rain","🌧️"],
  71:["Light snow","🌨️"],73:["Snow","🌨️"],75:["Heavy snow","❄️"],77:["Snow grains","❄️"],
  80:["Light showers","🌦️"],81:["Showers","🌧️"],82:["Violent showers","⛈️"],
  85:["Snow showers","🌨️"],86:["Snow showers","🌨️"],
  95:["Thunderstorm","⛈️"],96:["Thunderstorm + hail","⛈️"],99:["Thunderstorm + hail","⛈️"],
};
const wmo = (c) => WMO[c] || ["Unknown","❓"];

const $ = (id) => document.getElementById(id);
const qEl = $("q"), resultsEl = $("results"), msgEl = $("msg");

async function getJSON(url) {
  const r = await fetch(url);
  const d = await r.json();
  if (!r.ok) throw new Error(d.error || ("HTTP " + r.status));
  return d;
}

let searchTimer;
qEl.addEventListener("input", () => {
  clearTimeout(searchTimer);
  const q = qEl.value.trim();
  if (q.length < 2) { resultsEl.classList.add("hidden"); return; }
  searchTimer = setTimeout(() => search(q), 300);
});
$("go").addEventListener("click", () => { if (qEl.value.trim()) search(qEl.value.trim()); });
qEl.addEventListener("keydown", (e) => { if (e.key === "Enter" && qEl.value.trim()) search(qEl.value.trim()); });

async function search(q) {
  try {
    const data = await getJSON("/api/geocode?q=" + encodeURIComponent(q));
    const list = data.results || [];
    if (!list.length) { resultsEl.innerHTML = "<div><small>No matches</small></div>"; resultsEl.classList.remove("hidden"); return; }
    resultsEl.innerHTML = list.map((p, i) =>
      '<div data-i="' + i + '">' + p.name +
      '<small> — ' + [p.admin1, p.country].filter(Boolean).join(", ") + '</small></div>'
    ).join("");
    resultsEl.classList.remove("hidden");
    [...resultsEl.children].forEach((el, i) => {
      el.addEventListener("click", () => { resultsEl.classList.add("hidden"); qEl.value = list[i].name; load(list[i]); });
    });
  } catch (e) {
    msgEl.innerHTML = '<span class="err">Search failed: ' + e.message + '</span>';
  }
}

async function load(place) {
  msgEl.textContent = "Loading weather for " + place.name + "…";
  $("current").classList.add("hidden");
  $("forecastWrap").classList.add("hidden");
  try {
    const w = await getJSON("/api/weather?lat=" + place.latitude + "&lon=" + place.longitude);
    renderCurrent(place, w);
    renderForecast(w);
    msgEl.textContent = "";
  } catch (e) {
    msgEl.innerHTML = '<span class="err">Failed to load weather: ' + e.message + '</span>';
  }
}

function renderCurrent(place, w) {
  const c = w.current, u = w.current_units;
  const [desc, icon] = wmo(c.weather_code);
  const loc = [place.admin1, place.country].filter(Boolean).join(", ");
  $("current").innerHTML =
    '<div class="top">' +
      '<div>' +
        '<div class="place">' + place.name + '<small>' + loc + '</small></div>' +
        '<div class="desc">' + desc + '</div>' +
      '</div>' +
      '<div style="text-align:right">' +
        '<span class="icon">' + icon + '</span> ' +
        '<span class="temp">' + Math.round(c.temperature_2m) + (u.temperature_2m || "°") + '</span>' +
      '</div>' +
    '</div>' +
    '<div class="stats">' +
      stat("Feels like", Math.round(c.apparent_temperature) + (u.apparent_temperature || "°")) +
      stat("Humidity", c.relative_humidity_2m + (u.relative_humidity_2m || "%")) +
      stat("Wind", Math.round(c.wind_speed_10m) + " " + (u.wind_speed_10m || "km/h")) +
      stat("Precipitation", c.precipitation + " " + (u.precipitation || "mm")) +
    '</div>';
  $("current").classList.remove("hidden");
}

function stat(label, value) {
  return '<div class="stat"><div class="label">' + label + '</div><div class="value">' + value + '</div></div>';
}

function renderForecast(w) {
  const d = w.daily;
  const html = d.time.map((t, i) => {
    const [, icon] = wmo(d.weather_code[i]);
    const dow = new Date(t).toLocaleDateString(undefined, { weekday: "short" });
    return '<div class="day">' +
      '<div class="dow">' + dow + '</div>' +
      '<div class="di">' + icon + '</div>' +
      '<div><span class="hi">' + Math.round(d.temperature_2m_max[i]) + '°</span> ' +
      '<span class="lo">' + Math.round(d.temperature_2m_min[i]) + '°</span></div>' +
    '</div>';
  }).join("");
  $("forecast").innerHTML = html;
  $("forecastWrap").classList.remove("hidden");
}

document.addEventListener("click", (e) => {
  if (!e.target.closest(".search")) resultsEl.classList.add("hidden");
});
</script>
</body>
</html>`;
