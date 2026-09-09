// =============================================
// Frontend Main JavaScript
// =============================================

// --- Cart State ---
let cart = JSON.parse(localStorage.getItem('bd_cart') || '[]');
let couponDiscount = 0;
let appliedCoupon = null;

function saveCart() {
    localStorage.setItem('bd_cart', JSON.stringify(cart));
    updateCartUI();
    triggerPixelEvent('CartUpdate');
}

function addToCart(id, title, price, image, qty = 1) {
    const existing = cart.find(i => i.id == id);
    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({ id, title, price, image, qty });
    }
    saveCart();
    showToast('কার্টে যোগ হয়েছে! 🛒', 'success');
    openCart();
    // Pixel AddToCart event
    if (window.fbq) fbq('track', 'AddToCart', { content_ids: [id], content_name: title, value: price, currency: 'BDT' });
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id != id);
    saveCart();
    showToast('কার্ট থেকে সরানো হয়েছে', 'info');
}

function updateCartQty(id, delta) {
    const item = cart.find(i => i.id == id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) removeFromCart(id);
    else saveCart();
}

function getCartTotal() {
    return cart.reduce((s, i) => s + i.price * i.qty, 0);
}

function getCartCount() {
    return cart.reduce((s, i) => s + i.qty, 0);
}

function updateCartUI() {
    const count = getCartCount();
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = count;
        el.style.display = count > 0 ? 'flex' : 'none';
    });
    renderCartDrawer();
}

function renderCartDrawer() {
    const container = document.getElementById('cartItemsContainer');
    const emptyEl = document.getElementById('cartEmpty');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '';
        if (emptyEl) emptyEl.style.display = 'block';
        updateCartFooter(0);
        return;
    }
    if (emptyEl) emptyEl.style.display = 'none';

    container.innerHTML = cart.map(item => `
        <div class="cart-item" id="cart-item-${item.id}">
            <img src="${item.image || 'https://placehold.co/400x400/1a1a2e/6b6b8a?text=No+Image'}" alt="${item.title}" class="cart-item-img">
            <div class="cart-item-body">
                <div class="cart-item-name">${item.title}</div>
                <div class="cart-item-price">৳${(item.price * item.qty).toLocaleString('bn-BD')}</div>
                <div class="cart-item-qty">
                    <button class="qty-btn" onclick="updateCartQty(${item.id}, -1)">−</button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn" onclick="updateCartQty(${item.id}, 1)">+</button>
                    <button class="cart-item-remove" onclick="removeFromCart(${item.id})"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        </div>
    `).join('');

    updateCartFooter(getCartTotal());
}

function updateCartFooter(subtotal) {
    const subEl = document.getElementById('cartSubtotal');
    const discEl = document.getElementById('cartDiscount');
    const totalEl = document.getElementById('cartTotal');
    if (subEl) subEl.textContent = '৳' + subtotal.toLocaleString('bn-BD');
    if (discEl) discEl.textContent = couponDiscount > 0 ? '−৳' + couponDiscount : '—';
    if (totalEl) totalEl.textContent = '৳' + (subtotal - couponDiscount).toLocaleString('bn-BD');
}

// --- Cart Drawer ---
function openCart() {
    document.getElementById('cartDrawer')?.classList.add('open');
    document.getElementById('cartOverlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    document.getElementById('cartDrawer')?.classList.remove('open');
    document.getElementById('cartOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
}

// --- Coupon ---
async function applyCoupon() {
    const code = document.getElementById('couponInput')?.value?.trim();
    if (!code) return;
    const subtotal = getCartTotal();
    try {
        const res = await fetch(`/api/cart.php?action=coupon&code=${encodeURIComponent(code)}&subtotal=${subtotal}`);
        const data = await res.json();
        if (data.success) {
            couponDiscount = data.discount;
            appliedCoupon = code;
            showToast('কুপন প্রয়োগ হয়েছে! ' + data.message, 'success');
            updateCartFooter(subtotal);
        } else {
            showToast(data.message || 'অবৈধ কুপন কোড', 'error');
        }
    } catch (e) {
        showToast('সার্ভার ত্রুটি', 'error');
    }
}

// --- Live Search ---
let searchTimeout;
function initSearch() {
    const input = document.getElementById('searchInput');
    const results = document.getElementById('searchResults');
    if (!input || !results) return;

    input.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const q = this.value.trim();
        if (q.length < 2) { results.classList.remove('show'); return; }
        searchTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`/api/search.php?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data.products && data.products.length > 0) {
                    results.innerHTML = data.products.map(p => `
                        <div class="search-result-item" onclick="window.location='/product.php?id=${p.id}'">
                            <img src="${p.image || 'https://placehold.co/400x400/1a1a2e/6b6b8a?text=No+Image'}" alt="${p.title}">
                            <div class="info">
                                <div class="name">${p.title}</div>
                                <div class="price">৳${Number(p.selling_price).toLocaleString('bn-BD')}</div>
                            </div>
                        </div>
                    `).join('');
                    results.classList.add('show');
                } else {
                    results.innerHTML = '<div class="search-result-item"><div class="info"><div class="name">কোনো পণ্য পাওয়া যায়নি</div></div></div>';
                    results.classList.add('show');
                }
            } catch (e) {}
        }, 350);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            window.location = '/shop.php?search=' + encodeURIComponent(this.value);
        }
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.remove('show');
        }
    });
}

// --- Toast Notifications ---
function showToast(message, type = 'info', duration = 3000) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fa ${icons[type] || icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function createToastContainer() {
    const el = document.createElement('div');
    el.id = 'toastContainer';
    el.className = 'toast-container';
    document.body.appendChild(el);
    return el;
}

// --- Flash Sale Timer ---
function initTimer(targetId, seconds) {
    const el = document.getElementById(targetId);
    if (!el) return;
    let remaining = seconds;
    function update() {
        const h = Math.floor(remaining / 3600);
        const m = Math.floor((remaining % 3600) / 60);
        const s = remaining % 60;
        const hEl = el.querySelector('.timer-h');
        const mEl = el.querySelector('.timer-m');
        const sEl = el.querySelector('.timer-s');
        if (hEl) hEl.textContent = String(h).padStart(2, '0');
        if (mEl) mEl.textContent = String(m).padStart(2, '0');
        if (sEl) sEl.textContent = String(s).padStart(2, '0');
        if (remaining > 0) remaining--;
    }
    update();
    setInterval(update, 1000);
}

// --- Scroll Animations ---
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('animated');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('[data-aos]').forEach(el => observer.observe(el));
}

// --- Mobile Menu ---
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const sidebar = document.getElementById('shopSidebar');
    if (nav) nav.classList.toggle('mobile-open');
    if (sidebar) sidebar.classList.toggle('mobile-open');
}

// --- Hero Particles ---
function initParticles() {
    const container = document.querySelector('.hero-particles');
    if (!container) return;
    for (let i = 0; i < 15; i++) {
        const p = document.createElement('div');
        p.className = 'hero-particle';
        const size = Math.random() * 8 + 3;
        p.style.cssText = `
            width: ${size}px; height: ${size}px;
            left: ${Math.random() * 100}%;
            animation-duration: ${Math.random() * 10 + 8}s;
            animation-delay: ${Math.random() * 5}s;
        `;
        container.appendChild(p);
    }
}

// --- Price Range Filter ---
function initPriceSlider() {
    const slider = document.getElementById('priceSlider');
    const label = document.getElementById('priceLabel');
    if (!slider || !label) return;
    slider.addEventListener('input', function () {
        label.textContent = '৳' + Number(this.value).toLocaleString('bn-BD');
        const pct = ((this.value - this.min) / (this.max - this.min)) * 100;
        this.style.background = `linear-gradient(to right, var(--primary) ${pct}%, var(--bg4) ${pct}%)`;
    });
}

// --- Product Gallery ---
function initGallery() {
    const thumbs = document.querySelectorAll('.gallery-thumb');
    const main = document.getElementById('galleryMain');
    if (!thumbs.length || !main) return;
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function () {
            const src = this.dataset.src;
            main.src = src;
            thumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// --- Variant Selection ---
function initVariants() {
    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const group = this.dataset.group;
            document.querySelectorAll(`.variant-btn[data-group="${group}"]`).forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// --- Delivery Zone Selection ---
function initDeliveryZones() {
    document.querySelectorAll('.zone-card').forEach(zone => {
        zone.addEventListener('click', function () {
            document.querySelectorAll('.zone-card').forEach(z => z.classList.remove('active'));
            this.classList.add('active');
            const input = document.getElementById('regionInput');
            if (input) input.value = this.dataset.zone;
            updateCheckoutTotal();
        });
    });
}

// --- Payment Method Selection ---
function initPaymentMethods() {
    document.querySelectorAll('.payment-method').forEach(pm => {
        pm.addEventListener('click', function () {
            document.querySelectorAll('.payment-method').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
}

// --- Checkout Total Update ---
function updateCheckoutTotal() {
    const activeZone = document.querySelector('.zone-card.active');
    const shippingFee = activeZone ? parseFloat(activeZone.dataset.fee) : 70;
    const subtotal = parseFloat(document.getElementById('checkoutSubtotal')?.dataset.value || 0);
    const discount = parseFloat(document.getElementById('checkoutDiscount')?.dataset.value || 0);
    const total = subtotal + shippingFee - discount;

    const shippingEl = document.getElementById('shippingFeeDisplay');
    const totalEl = document.getElementById('grandTotalDisplay');
    const shippingInput = document.getElementById('shippingFeeInput');
    if (shippingEl) shippingEl.textContent = '৳' + shippingFee.toLocaleString('bn-BD');
    if (totalEl) totalEl.textContent = '৳' + total.toLocaleString('bn-BD');
    if (shippingInput) shippingInput.value = shippingFee;
}

// --- Checkout Form ---
async function submitOrder(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('.btn-place-order');
    if (!cart.length) { showToast('কার্টে কোনো পণ্য নেই', 'error'); return; }

    // Validate phone
    const phone = form.querySelector('[name="customer_phone"]').value;
    if (!/^01[3-9]\d{8}$/.test(phone)) {
        showToast('সঠিক বাংলাদেশি মোবাইল নম্বর দিন (১১ ডিজিট)', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> অর্ডার হচ্ছে...';

    const formData = new FormData(form);
    formData.append('cart', JSON.stringify(cart));
    formData.append('coupon_discount', couponDiscount);
    if (appliedCoupon) formData.append('coupon_code', appliedCoupon);

    try {
        const res = await fetch('/api/order.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            // Pixel Purchase event
            if (window.fbq) fbq('track', 'Purchase', { value: data.total, currency: 'BDT', order_id: data.tracking_code });
            // GTM dataLayer
            if (window.dataLayer) dataLayer.push({ event: 'purchase', order_id: data.tracking_code, value: data.total });
            // Show success modal
            document.getElementById('successOrderId').textContent = data.tracking_code;
            document.getElementById('successModal').classList.add('open');
            cart = [];
            couponDiscount = 0;
            appliedCoupon = null;
            saveCart();
        } else {
            showToast(data.message || 'অর্ডার ব্যর্থ হয়েছে', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check-circle"></i> অর্ডার কনফার্ম করুন';
        }
    } catch (err) {
        showToast('সার্ভার সংযোগে সমস্যা হয়েছে', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check-circle"></i> অর্ডার কনফার্ম করুন';
    }
}

// --- Order Tracking ---
async function trackOrder(e) {
    e.preventDefault();
    const code = document.getElementById('trackCode').value.trim();
    const phone = document.getElementById('trackPhone').value.trim();
    const resultEl = document.getElementById('trackResult');
    if (!code) { showToast('অর্ডার নম্বর দিন', 'error'); return; }

    try {
        const res = await fetch(`/api/track.php?code=${encodeURIComponent(code)}&phone=${encodeURIComponent(phone)}`);
        const data = await res.json();
        if (data.success && data.order) {
            resultEl.innerHTML = renderTimeline(data.order);
            resultEl.style.display = 'block';
        } else {
            resultEl.innerHTML = `<div class="tracking-form-card"><div class="empty-state"><i class="fa fa-search" style="color:var(--text3)"></i><p>অর্ডার পাওয়া যায়নি। সঠিক অর্ডার নম্বর ও ফোন নম্বর দিন।</p></div></div>`;
            resultEl.style.display = 'block';
        }
    } catch (e) {
        showToast('সার্ভার ত্রুটি', 'error');
    }
}

function renderTimeline(order) {
    const steps = [
        { key: 'pending', label: 'অর্ডার গ্রহণ করা হয়েছে', icon: '📋', desc: 'আপনার অর্ডার আমরা পেয়েছি' },
        { key: 'processing', label: 'প্রসেসিং', icon: '⚙️', desc: 'আপনার পণ্য প্যাক করা হচ্ছে' },
        { key: 'shipped', label: 'কুরিয়ারে পাঠানো হয়েছে', icon: '🚚', desc: 'পণ্য ডেলিভারির পথে রয়েছে' },
        { key: 'delivered', label: 'ডেলিভারি সম্পন্ন', icon: '✅', desc: 'পণ্য সফলভাবে পৌঁছে গেছে' },
    ];
    const statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
    const currentIdx = statusOrder.indexOf(order.status);
    const isCancelled = order.status === 'cancelled';

    const stepsHtml = isCancelled ? `
        <div class="timeline-step done">
            <div class="timeline-dot">📋</div>
            <div class="timeline-content"><h4>অর্ডার গ্রহণ করা হয়েছে</h4></div>
        </div>
        <div class="timeline-step active">
            <div class="timeline-dot">❌</div>
            <div class="timeline-content"><h4>অর্ডার বাতিল হয়েছে</h4><p>আপনার অর্ডারটি বাতিল করা হয়েছে।</p></div>
        </div>
    ` : steps.map((step, idx) => {
        const isDone = idx < currentIdx;
        const isActive = idx === currentIdx;
        return `<div class="timeline-step ${isDone ? 'done' : ''} ${isActive ? 'active' : ''}">
            <div class="timeline-dot">${step.icon}</div>
            <div class="timeline-content"><h4>${step.label}</h4><p>${step.desc}</p></div>
        </div>`;
    }).join('');

    return `
        <div class="timeline">
            <div style="margin-bottom:24px">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px">
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3)">অর্ডার আইডি</div>
                        <div style="font-size:1.2rem;font-weight:800;color:var(--primary-light);font-family:monospace">${order.tracking_code}</div>
                    </div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3)">মোট</div>
                        <div style="font-size:1.1rem;font-weight:700;color:var(--white)">৳${Number(order.total_amount).toLocaleString('bn-BD')}</div>
                    </div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3)">তারিখ</div>
                        <div style="font-size:0.9rem;color:var(--text)">${new Date(order.created_at).toLocaleDateString('bn-BD')}</div>
                    </div>
                </div>
            </div>
            ${stepsHtml}
        </div>
    `;
}

// --- Pixel Events ---
function triggerPixelEvent(event, data = {}) {
    if (window.fbq) fbq('trackCustom', event, data);
}

// --- Init ---
document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initScrollAnimations();
    initParticles();
    initPriceSlider();
    initGallery();
    initVariants();
    initDeliveryZones();
    initPaymentMethods();
    updateCartUI();

    // Cart controls
    document.getElementById('cartBtn')?.addEventListener('click', openCart);
    document.getElementById('cartOverlay')?.addEventListener('click', closeCart);
    document.getElementById('cartCloseBtn')?.addEventListener('click', closeCart);
    document.getElementById('couponApplyBtn')?.addEventListener('click', applyCoupon);

    // Checkout
    document.getElementById('checkoutForm')?.addEventListener('submit', submitOrder);

    // Tracking
    document.getElementById('trackForm')?.addEventListener('submit', trackOrder);

    // Flash sale timer (6 hours)
    initTimer('flashTimer', 6 * 3600);

    // Mobile menu
    document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleMobileMenu);

    // Update checkout total on load
    updateCheckoutTotal();

    // Page-specific pixel events
    const page = document.body.dataset.page;
    if (page === 'product' && window.fbq) {
        fbq('track', 'ViewContent', {
            content_id: document.body.dataset.productId,
            content_name: document.body.dataset.productName,
            value: document.body.dataset.productPrice,
            currency: 'BDT'
        });
    }
    if (page === 'checkout' && window.fbq) {
        fbq('track', 'InitiateCheckout', { value: getCartTotal(), currency: 'BDT', num_items: getCartCount() });
    }
});
