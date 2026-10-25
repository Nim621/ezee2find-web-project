/**
 * EZEE2FIND - Core Client-Side Logic & Day Plan State Manager
 */

// Key for LocalStorage
const PLAN_STORAGE_KEY = 'ezee_selected_places';

// Get array of selected place Ids
function getSelectedPlanPlaces() {
  try {
    const raw = localStorage.getItem(PLAN_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

// Save selected place Is
function saveSelectedPlanPlaces(ids) {
  try {
    localStorage.setItem(PLAN_STORAGE_KEY, JSON.stringify(ids));
    updatePlanCounters();
    updateCardButtonStates();
  } catch (e) {
    console.error('Storage error:', e);
  }
}

// Add or remove a place from the plan
function togglePlanPlace(placeId) {
  placeId = parseInt(placeId, 10);
  let list = getSelectedPlanPlaces();
  const index = list.indexOf(placeId);
  let added = false;

  if (index === -1) {
    list.push(placeId);
    added = true;
    showToastNotification(`Added attraction to your day plan!`, 'success');
  } else {
    list.splice(index, 1);
    showToastNotification(`Removed attraction from your day plan.`, 'info');
  }

  saveSelectedPlanPlaces(list);
  return added;
}

// Clear all places from plan
function clearAllPlanPlaces() {
  if (confirm('Are you sure you want to clear all places from your day plan?')) {
    saveSelectedPlanPlaces([]);
    showToastNotification('Day plan cleared.', 'info');
    if (window.location.pathname.includes('planner.php')) {
      window.location.reload();
    }
  }
}

// Update badges in navbar and floating pill
function updatePlanCounters() {
  const count = getSelectedPlanPlaces().length;
  
  const navBadge = document.getElementById('nav-plan-count');
  if (navBadge) navBadge.textContent = count;

  const floatBtn = document.getElementById('floating-plan-pill');
  const floatCount = document.getElementById('floating-plan-count');
  if (floatBtn && floatCount) {
    floatCount.textContent = count;
    if (count > 0 && !window.location.pathname.includes('planner.php')) {
      floatBtn.classList.remove('d-none');
    } else {
      floatBtn.classList.add('d-none');
    }
  }
}

// Update button labels on place cards
function updateCardButtonStates() {
  const selected = getSelectedPlanPlaces();
  document.querySelectorAll('[data-plan-btn]').forEach(btn => {
    const pid = parseInt(btn.getAttribute('data-plan-btn'), 10);
    if (selected.includes(pid)) {
      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-success');
      btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> In Plan';
    } else {
      btn.classList.remove('btn-success');
      btn.classList.add('btn-outline-primary');
      btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add to Plan';
    }
  });
}

// Minimal Toast notification
function showToastNotification(message, type = 'info') {
  let toastContainer = document.getElementById('toast-dynamic-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toast-dynamic-container';
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    document.body.appendChild(toastContainer);
  }

  const toastId = 'toast-' + Date.now();
  const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
  const html = `
    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body fw-medium">
          <i class="bi bi-info-circle-fill me-2"></i> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  toastContainer.insertAdjacentHTML('beforeend', html);
  const toastElem = document.getElementById(toastId);
  if (toastElem && typeof bootstrap !== 'undefined') {
    const bsToast = new bootstrap.Toast(toastElem, { delay: 2800 });
    bsToast.show();
    toastElem.addEventListener('hidden.bs.toast', () => toastElem.remove());
  }
}

// Format currency
function formatLKR(amount) {
  return 'LKR ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// Auto-run on document ready
document.addEventListener('DOMContentLoaded', () => {
  updatePlanCounters();
  updateCardButtonStates();

  // Attach click listener for data-plan-btn
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-plan-btn]');
    if (btn) {
      e.preventDefault();
      const pid = btn.getAttribute('data-plan-btn');
      togglePlanPlace(pid);
    }
  });
});
