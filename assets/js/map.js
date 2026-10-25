/**
 * EZEE2FIND - Interactive Leaflet Map Engine
 */

let mapInstance = null;
let markersLayer = null;
let routePolyline = null;

// Category color mapping
const categoryColors = {
  1: '#0284c7', // Heritage - Blue
  2: '#8b5cf6', // Museums - Purple
  3: '#d97706', // Religious - Amber
  4: '#06b6d4', // Beach - Cyan
  5: '#10b981', // Nature - Emerald
  6: '#84cc16'  // Agriculture - Lime
};

function initGalleMap(elementId = 'leaflet-map', options = {}) {
  const centerLat = options.centerLat || 6.0355;
  const centerLon = options.centerLon || 80.2170;
  const zoomLevel = options.zoom || 12;

  const mapEl = document.getElementById(elementId);
  if (!mapEl) return null;

  mapInstance = L.map(elementId).setView([centerLat, centerLon], zoomLevel);

  // Free OpenStreetMap Tile Layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(mapInstance);

  markersLayer = L.layerGroup().addTo(mapInstance);

  // Draw 25km Study Scope Circle 
  if (options.showScopeCircle) {
    L.circle([6.0355, 80.2140], {
      color: '#0284c7',
      fillColor: '#38bdf8',
      fillOpacity: 0.04,
      radius: 25000, // 25 km
      dashArray: '6, 8',
      weight: 2
    }).addTo(mapInstance).bindTooltip("EZEE2FIND 25 km Study Scope around Galle", { sticky: true });
  }

  return mapInstance;
}

// Create custom pin icon
function createCustomPin(color = '#0284c7', labelNumber = null) {
  const labelHtml = labelNumber !== null ? `<span style="position:absolute;top:6px;left:0;right:0;text-align:center;color:#fff;font-size:11px;font-weight:bold;">${labelNumber}</span>` : '';
  const svg = `
    <div style="position:relative;width:30px;height:42px;">
      <svg width="30" height="42" viewBox="0 0 30 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15 0C6.71573 0 0 6.71573 0 15C0 26.25 15 42 15 42C15 42 30 26.25 30 15C30 6.71573 23.2843 0 15 0Z" fill="${color}"/>
        <circle cx="15" cy="15" r="9" fill="rgba(0,0,0,0.2)"/>
      </svg>
      ${labelHtml}
    </div>
  `;
  return L.divIcon({
    html: svg,
    className: 'custom-map-pin',
    iconSize: [30, 42],
    iconAnchor: [15, 42],
    popupAnchor: [0, -38]
  });
}

// Render place markers on map
function renderPlacesOnMap(places, isNumberedRoute = false) {
  if (!mapInstance || !markersLayer) return;
  markersLayer.clearLayers();

  if (routePolyline) {
    mapInstance.removeLayer(routePolyline);
    routePolyline = null;
  }

  const bounds = [];
  const routePoints = [];

  places.forEach((p, idx) => {
    const lat = parseFloat(p.latitude);
    const lon = parseFloat(p.longitude);
    if (isNaN(lat) || isNaN(lon)) return;

    bounds.push([lat, lon]);
    if (isNumberedRoute) routePoints.push([lat, lon]);

    const color = isNumberedRoute ? '#0284c7' : (categoryColors[p.categoryid] || '#0284c7');
    const pin = createCustomPin(color, isNumberedRoute ? (idx + 1) : null);

    const marker = L.marker([lat, lon], { icon: pin });

    // Popup Card 
    const popupContent = `
      <div style="width: 240px; font-family: 'Inter', sans-serif;">
        <img src="${p.imageurl}" alt="${p.name}" style="width:100%;height:110px;object-fit:cover;border-radius:6px;margin-bottom:8px;">
        <h6 style="font-weight:700;margin:0 0 4px;font-size:14px;color:#0f172a;">${p.name}</h6>
        <div style="font-size:11px;color:#0284c7;font-weight:600;margin-bottom:6px;">
          <i class="bi bi-tag"></i> ${p.categoryname || 'Attraction'} • ${p.distancekm || 0} km from Galle
        </div>
        <div style="font-size:11px;color:#475569;margin-bottom:8px;line-height:1.4;">
          <div><i class="bi bi-clock"></i> ${p.openinginfo}</div>
          <div><i class="bi bi-ticket-perforated"></i> ${p.entrancefee > 0 ? 'LKR ' + p.entrancefee : 'Free admission'}</div>
        </div>
        <div style="display:flex;gap:4px;">
          <a href="details.php?id=${p.placeid}" class="btn btn-sm btn-outline-secondary" style="font-size:11px;flex:1;padding:3px 6px;">
            Details
          </a>
          <button onclick="togglePlanPlace(${p.placeid})" class="btn btn-sm btn-primary" style="font-size:11px;flex:1;padding:3px 6px;">
            Add to Plan
          </button>
        </div>
      </div>
    `;

    marker.bindPopup(popupContent);
    markersLayer.addLayer(marker);
  });

  // Draw Route Polyline if numbered itinerary
  if (isNumberedRoute && routePoints.length > 1) {
    routePolyline = L.polyline(routePoints, {
      color: '#0284c7',
      weight: 4,
      opacity: 0.85,
      dashArray: '8, 8',
      lineCap: 'round'
    }).addTo(mapInstance);
  }

  // Fit bounds if markers exist
  if (bounds.length > 0) {
    mapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
  }
}
