// =============================================
// Admin Dashboard JavaScript
// =============================================

// --- Toast ---
function showAdminToast(msg, type = 'success') {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-circle' };
    let container = document.getElementById('adminToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'adminToastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fa ${icons[type] || 'fa-info-circle'}"></i><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.classList.add('removing'); setTimeout(() => toast.remove(), 300); }, 3500);
}

// --- Confirm Delete ---
function confirmDelete(url, name) {
    if (!confirm(`"${name}" ডিলিট করতে চান? এই কাজ পূর্বাবস্থায় ফেরানো যাবে না।`)) return;
    fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=delete' })
        .then(r => r.json())
        .then(d => {
            if (d.success) { showAdminToast(d.message || 'সফলভাবে ডিলিট হয়েছে'); setTimeout(() => location.reload(), 800); }
            else showAdminToast(d.message || 'ডিলিট ব্যর্থ হয়েছে', 'error');
        }).catch(() => showAdminToast('সার্ভার ত্রুটি', 'error'));
}

// --- Order Status Update ---
function updateOrderStatus(orderId, status) {
    fetch('/admin/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&order_id=${orderId}&status=${status}`
    }).then(r => r.json()).then(d => {
        if (d.success) showAdminToast('স্ট্যাটাস আপডেট হয়েছে');
        else showAdminToast(d.message || 'আপডেট ব্যর্থ', 'error');
    });
}

// --- Image Preview ---
function initImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        preview.innerHTML = '';
        Array.from(this.files).forEach(file => {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
}

// --- Color Picker Sync ---
function syncColorInput(colorId, textId) {
    const colorInput = document.getElementById(colorId);
    const textInput = document.getElementById(textId);
    if (!colorInput || !textInput) return;
    colorInput.addEventListener('input', () => { textInput.value = colorInput.value; updateThemePreview(); });
    textInput.addEventListener('input', () => { if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) colorInput.value = textInput.value; updateThemePreview(); });
}

function updateThemePreview() {
    const primary = document.getElementById('primaryColorPicker')?.value;
    const accent = document.getElementById('accentColorPicker')?.value;
    if (primary) document.documentElement.style.setProperty('--primary', primary);
    if (accent) document.documentElement.style.setProperty('--accent', accent);
}

// --- Tracking Code Preview ---
function updateTrackingPreview() {
    const pixelId = document.getElementById('metaPixelId')?.value;
    const gtmId = document.getElementById('gtmContainerId')?.value;
    const pixelPreview = document.getElementById('pixelCodePreview');
    const gtmPreview = document.getElementById('gtmCodePreview');

    if (pixelPreview && pixelId) {
        pixelPreview.textContent = `<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s) {
  if(f.fbq)return; n=f.fbq=function() { n.callMethod ?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n; n.push=n; n.loaded=!0; n.version='2.0';
  n.queue=[]; t=b.createElement(e); t.async=!0;
  t.src=v; s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '${pixelId}');
fbq('track', 'PageView');
<\/script>`;
    }

    if (gtmPreview && gtmId) {
        gtmPreview.textContent = `<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','${gtmId}');<\/script>`;
    }
}

// --- Charts ---
function initDashboardCharts(salesData, profitData, labels) {
    // Sales & Profit Chart
    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'বিক্রয় (৳)',
                        data: salesData,
                        borderColor: '#6C63FF',
                        backgroundColor: 'rgba(108,99,255,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6C63FF',
                        pointRadius: 4,
                    },
                    {
                        label: 'প্রফিট (৳)',
                        data: profitData,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#22c55e',
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#a0a0c0', font: { family: 'Hind Siliguri' } } },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#e8e8f0',
                        bodyColor: '#a0a0c0',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: ctx => ' ৳' + ctx.parsed.y.toLocaleString('bn-BD')
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: '#6b6b8a' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: { ticks: { color: '#6b6b8a', callback: v => '৳' + v.toLocaleString() }, grid: { color: 'rgba(255,255,255,0.04)' } }
                }
            }
        });
    }

    // Order Status Donut
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        const statusData = window.statusChartData || { labels: [], data: [] };
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.data,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#a855f7', '#22c55e', '#ef4444'],
                    borderColor: '#161628',
                    borderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#a0a0c0', font: { family: 'Hind Siliguri' }, padding: 12 } }
                }
            }
        });
    }
}

// --- Sidebar Toggle (Mobile) ---
function toggleAdminSidebar() {
    document.querySelector('.admin-sidebar')?.classList.toggle('open');
}

// --- Table Search Filter ---
function initTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// --- Category Slug Auto-generate ---
function initSlugGenerate() {
    const nameInput = document.getElementById('categoryName');
    const slugInput = document.getElementById('categorySlug');
    if (!nameInput || !slugInput) return;
    nameInput.addEventListener('input', function () {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    });
    slugInput.addEventListener('input', () => { slugInput.dataset.manual = '1'; });
}

// --- Print Invoice ---
function printInvoice() { window.print(); }

// --- Copy Order ID ---
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => showAdminToast('কপি হয়েছে!', 'info'));
}

// --- Init ---
document.addEventListener('DOMContentLoaded', function () {
    // Image previews
    initImagePreview('productImages', 'productImagePreviews');
    initImagePreview('categoryImage', 'categoryImagePreview');
    initImagePreview('logoUpload', 'logoPreview');
    initImagePreview('faviconUpload', 'faviconPreview');

    // Color pickers
    syncColorInput('primaryColorPicker', 'primaryColorText');
    syncColorInput('accentColorPicker', 'accentColorText');

    // Tracking previews
    document.getElementById('metaPixelId')?.addEventListener('input', updateTrackingPreview);
    document.getElementById('gtmContainerId')?.addEventListener('input', updateTrackingPreview);

    // Table search
    initTableSearch('productSearch', 'productsTable');
    initTableSearch('orderSearch', 'ordersTable');

    // Category slug
    initSlugGenerate();

    // Mobile sidebar
    document.getElementById('adminMenuBtn')?.addEventListener('click', toggleAdminSidebar);

    // Print
    document.getElementById('printInvoiceBtn')?.addEventListener('click', printInvoice);
});
