// === ADMIN PANEL JAVASCRIPT ===

const API_BASE = '/api';

// --- State Store ---
const state = {
  token: localStorage.getItem('admin_token') || null,
  user: JSON.parse(localStorage.getItem('admin_user') || 'null'),
  currentRoute: 'dashboard',
  cache: {
    users: [],
    invoices: [],
    clients: [],
    items: [],
    businesses: []
  }
};

// --- DOM Elements ---
const loginSection = document.getElementById('loginSection');
const appSection = document.getElementById('appSection');
const loginForm = document.getElementById('loginForm');
const loginPhone = document.getElementById('loginPhone');
const loginPassword = document.getElementById('loginPassword');
const loginError = document.getElementById('loginError');
const logoutBtn = document.getElementById('logoutBtn');
const refreshBtn = document.getElementById('refreshBtn');

const navItems = document.querySelectorAll('.nav-item');
const viewPanels = document.querySelectorAll('.view-panel');
const currentPageTitle = document.getElementById('currentPageTitle');
const currentPageSubtitle = document.getElementById('currentPageSubtitle');
const adminName = document.getElementById('adminName');
const adminAvatar = document.getElementById('adminAvatar');

// --- Helper Utilities ---
function formatRupiah(num) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> <span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

async function apiRequest(endpoint, method = 'GET', body = null) {
  const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
  if (state.token) headers['Authorization'] = `Bearer ${state.token}`;

  const config = { method, headers };
  if (body) config.body = JSON.stringify(body);

  try {
    const res = await fetch(`${API_BASE}${endpoint}`, config);
    const data = await res.json();
    if (res.status === 401 || res.status === 403) {
      if (state.token) {
        showToast('Sesi berakhir atau akses ditolak', 'danger');
        handleLogout();
      }
      throw new Error(data.message || 'Akses ditolak');
    }
    if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan');
    return data;
  } catch (err) {
    throw err;
  }
}

// --- Auth Functions ---
loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  loginError.classList.add('hidden');
  
  try {
    const res = await apiRequest('/login', 'POST', {
      phone: loginPhone.value.trim(),
      password: loginPassword.value
    });

    if (!res.user || !res.user.is_admin) {
      loginError.textContent = 'Akun Anda bukan Admin. Akses ditolak.';
      loginError.classList.remove('hidden');
      return;
    }

    state.token = res.token;
    state.user = res.user;
    localStorage.setItem('admin_token', res.token);
    localStorage.setItem('admin_user', JSON.stringify(res.user));

    showToast(`Selamat datang kembali, ${res.user.name}!`);
    initApp();
  } catch (err) {
    loginError.textContent = err.message || 'Login gagal. Periksa kembali data Anda.';
    loginError.classList.remove('hidden');
  }
});

function handleLogout() {
  state.token = null;
  state.user = null;
  localStorage.removeItem('admin_token');
  localStorage.removeItem('admin_user');
  loginSection.classList.remove('hidden');
  appSection.classList.add('hidden');
}

logoutBtn.addEventListener('click', handleLogout);
refreshBtn.addEventListener('click', () => {
  loadRouteData(state.currentRoute, true);
  showToast('Data diperbarui');
});

// --- App Initialization & Routing ---
function initApp() {
  if (!state.token || !state.user || !state.user.is_admin) {
    loginSection.classList.remove('hidden');
    appSection.classList.add('hidden');
    return;
  }

  loginSection.classList.add('hidden');
  appSection.classList.remove('hidden');

  adminName.textContent = state.user.name || 'Admin';
  adminAvatar.textContent = (state.user.name || 'A').charAt(0).toUpperCase();

  // Setup Navigation listeners
  window.addEventListener('hashchange', handleRoute);
  handleRoute();
}

function handleRoute() {
  const hash = window.location.hash.replace('#', '') || 'dashboard';
  state.currentRoute = hash;

  navItems.forEach(item => {
    if (item.getAttribute('data-target') === hash) {
      item.classList.add('active');
    } else {
      item.classList.remove('active');
    }
  });

  viewPanels.forEach(panel => {
    if (panel.id === `view-${hash}`) {
      panel.classList.add('active');
    } else {
      panel.classList.remove('active');
    }
  });

  // Update Page Title
  const titles = {
    dashboard: { title: 'Dashboard Overview', sub: 'Ringkasan statistik sistem dan performa aplikasi' },
    users: { title: 'Manajemen Pengguna', sub: 'Kelola seluruh daftar pengguna terdaftar' },
    invoices: { title: 'Manajemen Invoice', sub: 'Pantau seluruh invoice yang dibuat oleh seluruh bisnis' },
    clients: { title: 'Manajemen Klien', sub: 'Kelola daftar klien pelanggan pengguna' },
    items: { title: 'Manajemen Produk & Item', sub: 'Daftar katalog item dan jasa terdaftar' },
    businesses: { title: 'Manajemen Bisnis', sub: 'Profil usaha yang terdaftar di dalam aplikasi' },
    notifications: { title: 'Pemberitahuan & Broadcast', sub: 'Kirim informasi/pengumuman ke seluruh user atau user tertentu' }
  };

  const current = titles[hash] || titles.dashboard;
  currentPageTitle.textContent = current.title;
  currentPageSubtitle.textContent = current.sub;

  loadRouteData(hash);
}

function loadRouteData(route, forceRefresh = false) {
  switch (route) {
    case 'dashboard': fetchDashboardData(); break;
    case 'users': fetchUsersData(); break;
    case 'invoices': fetchInvoicesData(); break;
    case 'clients': fetchClientsData(); break;
    case 'items': fetchItemsData(); break;
    case 'businesses': fetchBusinessesData(); break;
    case 'notifications': fetchNotificationsData(); setupNotificationForm(); break;
  }
}

// --- Data Handlers ---

// 1. Dashboard
async function fetchDashboardData() {
  try {
    const res = await apiRequest('/admin/dashboard');
    const data = res.data;

    document.getElementById('statUsers').textContent = data.total_users;
    document.getElementById('statRevenue').textContent = formatRupiah(data.total_revenue);
    document.getElementById('statInvoices').textContent = data.total_invoices;
    document.getElementById('statBusinesses').textContent = data.total_businesses;

    // Render Recent Users
    const usersBody = document.getElementById('recentUsersTable');
    if (!data.recent_users.length) {
      usersBody.innerHTML = '<tr><td colspan="3" class="text-center">Belum ada pengguna</td></tr>';
    } else {
      usersBody.innerHTML = data.recent_users.map(u => `
        <tr>
          <td><strong>${u.name}</strong></td>
          <td>${u.phone || '-'}</td>
          <td><span class="badge ${u.is_admin ? 'badge-danger' : 'badge-primary'}">${u.is_admin ? 'Admin' : 'User'}</span></td>
        </tr>
      `).join('');
    }

    // Render Recent Invoices
    const invBody = document.getElementById('recentInvoicesTable');
    if (!data.recent_invoices.length) {
      invBody.innerHTML = '<tr><td colspan="3" class="text-center">Belum ada invoice</td></tr>';
    } else {
      invBody.innerHTML = data.recent_invoices.map(inv => `
        <tr>
          <td><strong>#${inv.invoice_number || inv.id}</strong></td>
          <td>${inv.client ? inv.client.name : '-'}</td>
          <td><strong style="color: var(--success);">${formatRupiah(inv.total || 0)}</strong></td>
        </tr>
      `).join('');
    }
  } catch (err) {
    showToast('Gagal memuat data dashboard', 'danger');
  }
}

// 2. Users
async function fetchUsersData() {
  try {
    const res = await apiRequest('/admin/users');
    state.cache.users = res.data;
    renderUsersTable(res.data);
  } catch (err) {
    showToast('Gagal memuat data pengguna', 'danger');
  }
}

function renderUsersTable(users) {
  const tbody = document.getElementById('usersTableBody');
  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada pengguna ditemukan</td></tr>';
    return;
  }

  tbody.innerHTML = users.map(u => `
    <tr>
      <td>#${u.id}</td>
      <td><strong>${u.name}</strong></td>
      <td>${u.phone || '-'}</td>
      <td>
        <small style="color: var(--text-muted);">
          ${u.invoices_count || 0} Invoice | ${u.clients_count || 0} Klien | ${u.items_count || 0} Item
        </small>
      </td>
      <td><span class="badge ${u.is_admin ? 'badge-danger' : 'badge-primary'}">${u.is_admin ? 'Admin' : 'User'}</span></td>
      <td>${formatDate(u.created_at)}</td>
      <td class="text-right">
        <button class="btn-action btn-action-toggle" onclick="toggleAdmin(${u.id})" title="Toggle Role Admin">
          <i class="fa-solid fa-user-shield"></i>
        </button>
        <button class="btn-action btn-action-danger" onclick="deleteUser(${u.id}, '${u.name}')" title="Hapus User">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

document.getElementById('searchUsers').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  const filtered = state.cache.users.filter(u => 
    u.name.toLowerCase().includes(q) || (u.phone && u.phone.includes(q))
  );
  renderUsersTable(filtered);
});

async function toggleAdmin(id) {
  try {
    const res = await apiRequest(`/admin/users/${id}/admin`, 'PUT');
    showToast(res.message);
    fetchUsersData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

async function deleteUser(id, name) {
  if (!confirm(`Apakah Anda yakin ingin menghapus pengguna "${name}" beserta SELURUH data bisnisnya?`)) return;
  try {
    const res = await apiRequest(`/admin/users/${id}`, 'DELETE');
    showToast(res.message);
    fetchUsersData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

// 3. Invoices
async function fetchInvoicesData() {
  try {
    const res = await apiRequest('/admin/invoices');
    state.cache.invoices = res.data;
    renderInvoicesTable(res.data);
  } catch (err) {
    showToast('Gagal memuat data invoice', 'danger');
  }
}

function renderInvoicesTable(invoices) {
  const tbody = document.getElementById('invoicesTableBody');
  if (!invoices.length) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada invoice ditemukan</td></tr>';
    return;
  }

  tbody.innerHTML = invoices.map(inv => {
    const bizName = inv.business ? inv.business.name : 'Unknown';
    const ownerName = inv.business && inv.business.user ? inv.business.user.name : '-';
    const clientName = inv.client ? inv.client.name : '-';
    
    // Status Badge
    let badgeClass = 'badge-warning';
    if (inv.status === 'PAID' || inv.status === 'Lunas') badgeClass = 'badge-success';
    if (inv.status === 'CANCELLED' || inv.status === 'Batal') badgeClass = 'badge-danger';

    return `
      <tr>
        <td><strong>#${inv.invoice_number || inv.id}</strong></td>
        <td>${bizName} <br><small style="color: var(--text-muted);">By: ${ownerName}</small></td>
        <td>${clientName}</td>
        <td><strong style="color: var(--success);">${formatRupiah(inv.total || 0)}</strong></td>
        <td><span class="badge ${badgeClass}">${inv.status || 'UNPAID'}</span></td>
        <td>${formatDate(inv.date || inv.created_at)}</td>
        <td class="text-right">
          <button class="btn-action btn-action-danger" onclick="deleteInvoice(${inv.id}, '${inv.invoice_number || inv.id}')">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

document.getElementById('searchInvoices').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  const filtered = state.cache.invoices.filter(inv => {
    const num = (inv.invoice_number || inv.id.toString()).toLowerCase();
    const client = (inv.client ? inv.client.name : '').toLowerCase();
    const biz = (inv.business ? inv.business.name : '').toLowerCase();
    return num.includes(q) || client.includes(q) || biz.includes(q);
  });
  renderInvoicesTable(filtered);
});

async function deleteInvoice(id, invNum) {
  if (!confirm(`Hapus invoice #${invNum}?`)) return;
  try {
    const res = await apiRequest(`/admin/invoices/${id}`, 'DELETE');
    showToast(res.message);
    fetchInvoicesData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

// 4. Clients
async function fetchClientsData() {
  try {
    const res = await apiRequest('/admin/clients');
    state.cache.clients = res.data;
    renderClientsTable(res.data);
  } catch (err) {
    showToast('Gagal memuat data klien', 'danger');
  }
}

function renderClientsTable(clients) {
  const tbody = document.getElementById('clientsTableBody');
  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada klien ditemukan</td></tr>';
    return;
  }

  tbody.innerHTML = clients.map(c => `
    <tr>
      <td>#${c.id}</td>
      <td><strong>${c.name}</strong></td>
      <td>${c.phone || '-'}<br><small style="color: var(--text-muted);">${c.email || ''}</small></td>
      <td>${c.business ? c.business.name : '-'}</td>
      <td>${c.address1 || '-'}</td>
      <td class="text-right">
        <button class="btn-action btn-action-danger" onclick="deleteClient(${c.id}, '${c.name}')">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

document.getElementById('searchClients').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  const filtered = state.cache.clients.filter(c => 
    c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q)) || (c.email && c.email.toLowerCase().includes(q))
  );
  renderClientsTable(filtered);
});

async function deleteClient(id, name) {
  if (!confirm(`Hapus klien "${name}"?`)) return;
  try {
    const res = await apiRequest(`/admin/clients/${id}`, 'DELETE');
    showToast(res.message);
    fetchClientsData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

// 5. Items
async function fetchItemsData() {
  try {
    const res = await apiRequest('/admin/items');
    state.cache.items = res.data;
    renderItemsTable(res.data);
  } catch (err) {
    showToast('Gagal memuat data item', 'danger');
  }
}

function renderItemsTable(items) {
  const tbody = document.getElementById('itemsTableBody');
  if (!items.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada item ditemukan</td></tr>';
    return;
  }

  tbody.innerHTML = items.map(item => `
    <tr>
      <td>#${item.id}</td>
      <td><strong>${item.name}</strong><br><small style="color: var(--text-muted);">${item.description || ''}</small></td>
      <td><strong style="color: var(--success);">${formatRupiah(item.price || 0)}</strong></td>
      <td>${item.quantity || 1} ${item.unit || ''}</td>
      <td>${item.business ? item.business.name : '-'}</td>
      <td class="text-right">
        <button class="btn-action btn-action-danger" onclick="deleteItem(${item.id}, '${item.name}')">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

document.getElementById('searchItems').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  const filtered = state.cache.items.filter(item => 
    item.name.toLowerCase().includes(q) || (item.description && item.description.toLowerCase().includes(q))
  );
  renderItemsTable(filtered);
});

async function deleteItem(id, name) {
  if (!confirm(`Hapus item "${name}"?`)) return;
  try {
    const res = await apiRequest(`/admin/items/${id}`, 'DELETE');
    showToast(res.message);
    fetchItemsData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

// 6. Businesses
async function fetchBusinessesData() {
  try {
    const res = await apiRequest('/admin/businesses');
    state.cache.businesses = res.data;
    renderBusinessesTable(res.data);
  } catch (err) {
    showToast('Gagal memuat data bisnis', 'danger');
  }
}

function renderBusinessesTable(businesses) {
  const tbody = document.getElementById('businessesTableBody');
  if (!businesses.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada bisnis ditemukan</td></tr>';
    return;
  }

  tbody.innerHTML = businesses.map(b => `
    <tr>
      <td>#${b.id}</td>
      <td><strong>${b.name}</strong></td>
      <td>${b.user ? b.user.name : '-'}<br><small style="color: var(--text-muted);">${b.user ? b.user.phone : ''}</small></td>
      <td>${b.phone || '-'}<br><small style="color: var(--text-muted);">${b.email || ''}</small></td>
      <td>${b.address1 || '-'}</td>
      <td><span class="badge ${b.is_active ? 'badge-success' : 'badge-warning'}">${b.is_active ? 'Aktif' : 'Non-Aktif'}</span></td>
    </tr>
  `).join('');
}

document.getElementById('searchBusinesses').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  const filtered = state.cache.businesses.filter(b => 
    b.name.toLowerCase().includes(q) || (b.user && b.user.name.toLowerCase().includes(q))
  );
  renderBusinessesTable(filtered);
});

// 7. Notifications & Broadcast
async function fetchNotificationsData() {
  try {
    const res = await apiRequest('/admin/notifications');
    renderNotificationsHistoryTable(res.data);
  } catch (err) {
    showToast('Gagal memuat riwayat pemberitahuan', 'danger');
  }
}

function renderNotificationsHistoryTable(notifications) {
  const tbody = document.getElementById('notificationsHistoryTable');
  if (!notifications.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Belum ada pemberitahuan terkirim</td></tr>';
    return;
  }

  tbody.innerHTML = notifications.map(n => {
    const targetBadge = n.user_id 
      ? `<span class="badge badge-warning">🎯 ${n.user ? n.user.name : 'User #' + n.user_id}</span>`
      : `<span class="badge badge-success">📢 Semua User</span>`;

    return `
      <tr>
        <td>
          <strong>${n.title}</strong><br>
          <small style="color: var(--text-muted);">${n.message}</small>
        </td>
        <td>${targetBadge}</td>
        <td><small style="color: var(--text-muted);">${formatDate(n.created_at)}</small></td>
        <td class="text-right">
          <button class="btn-action btn-action-danger" onclick="deleteNotification(${n.id}, '${n.title}')">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

async function deleteNotification(id, title) {
  if (!confirm(`Hapus pengumuman "${title}"?`)) return;
  try {
    const res = await apiRequest(`/admin/notifications/${id}`, 'DELETE');
    showToast(res.message);
    fetchNotificationsData();
  } catch (err) {
    showToast(err.message, 'danger');
  }
}

let notificationFormInitialized = false;

function setupNotificationForm() {
  const targetAll = document.getElementById('targetAll');
  const targetSpecific = document.getElementById('targetSpecific');
  const specificContainer = document.getElementById('specificUserContainer');
  const form = document.getElementById('sendNotificationForm');

  targetAll.addEventListener('change', () => {
    if (targetAll.checked) specificContainer.classList.add('hidden');
  });

  targetSpecific.addEventListener('change', async () => {
    if (targetSpecific.checked) {
      specificContainer.classList.remove('hidden');
      loadUserCheckboxList();
    }
  });

  if (notificationFormInitialized) return;
  notificationFormInitialized = true;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const title = document.getElementById('notifTitle').value.trim();
    const message = document.getElementById('notifMessage').value.trim();
    const isSpecific = targetSpecific.checked;

    let selectedUserIds = [];
    if (isSpecific) {
      const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
      selectedUserIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
      if (!selectedUserIds.length) {
        showToast('Pilih minimal satu pengguna untuk pengiriman khusus!', 'danger');
        return;
      }
    }

    try {
      const res = await apiRequest('/admin/notifications', 'POST', {
        title,
        message,
        target_type: isSpecific ? 'specific' : 'all',
        user_ids: selectedUserIds
      });

      showToast(res.message);
      form.reset();
      specificContainer.classList.add('hidden');
      targetAll.checked = true;
      fetchNotificationsData();
    } catch (err) {
      showToast(err.message, 'danger');
    }
  });
}

async function loadUserCheckboxList() {
  const listEl = document.getElementById('userCheckboxList');
  try {
    let users = state.cache.users;
    if (!users || !users.length) {
      const res = await apiRequest('/admin/users');
      users = res.data;
      state.cache.users = users;
    }

    if (!users.length) {
      listEl.innerHTML = '<span style="font-size: 12px; color: var(--text-muted);">Tidak ada pengguna terdaftar</span>';
      return;
    }

    listEl.innerHTML = users.map(u => `
      <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #fff; cursor: pointer;">
        <input type="checkbox" class="user-checkbox" value="${u.id}">
        <span><strong>${u.name}</strong> (${u.phone || 'No phone'})</span>
      </label>
    `).join('');
  } catch (err) {
    listEl.innerHTML = '<span style="font-size: 12px; color: var(--danger);">Gagal memuat pengguna</span>';
  }
}

// Run Init
document.addEventListener('DOMContentLoaded', initApp);
