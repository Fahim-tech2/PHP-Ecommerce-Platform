<?php
// =============================================
// Checkout Page — এক পেজ চেকআউট
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$shippingZones = getShippingZones();
$pageTitle = 'চেকআউট | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc  = 'আপনার অর্ডার সম্পন্ন করুন।';

// Direct buy
$directProductId = (int)($_GET['direct'] ?? 0);
$directQty = max(1, (int)($_GET['qty'] ?? 1));
$directProduct = $directProductId ? getProduct($directProductId) : null;

include __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">🛒 চেকআউট</h1>
        <div class="breadcrumb">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <a href="/shop.php">শপ</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <span>চেকআউট</span>
        </div>
    </div>
</div>
<section class="section">
    <div class="container">
        <div class="checkout-grid">
            <!-- Order Form -->
            <div>
                <form id="checkoutForm">
                    <!-- Customer Info -->
                    <div class="checkout-card" style="margin-bottom:20px">
                        <h2 class="checkout-title"><i class="fa fa-user"></i> গ্রাহকের তথ্য</h2>
                        <div class="form-group">
                            <label class="form-label">সম্পূর্ণ নাম <span>*</span></label>
                            <input type="text" name="customer_name" class="form-control" placeholder="আপনার নাম লিখুন" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">মোবাইল নম্বর <span>*</span></label>
                            <input type="tel" name="customer_phone" class="form-control" placeholder="01XXXXXXXXX (১১ ডিজিট)" maxlength="11" required pattern="^01[3-9]\d{8}$">
                        </div>
                        <div class="form-group">
                            <label class="form-label">সম্পূর্ণ ঠিকানা <span>*</span></label>
                            <textarea name="delivery_address" class="form-control" placeholder="বাড়ি নং, রাস্তা, এলাকা, জেলা..." rows="3" required></textarea>
                        </div>
                    </div>

                    <!-- Delivery Zone -->
                    <div class="checkout-card" style="margin-bottom:20px">
                        <h2 class="checkout-title"><i class="fa fa-map-marker-alt"></i> ডেলিভারি এরিয়া</h2>
                        <input type="hidden" id="regionInput" name="region" value="<?= htmlspecialchars($shippingZones[0]['zone_key'] ?? 'inside_dhaka') ?>">
                        <div class="delivery-zones">
                            <?php foreach ($shippingZones as $i => $zone): ?>
                            <div class="zone-card <?= $i === 0 ? 'active' : '' ?>"
                                 data-zone="<?= htmlspecialchars($zone['zone_key']) ?>"
                                 data-fee="<?= $zone['delivery_fee'] ?>"
                                 id="zone-<?= htmlspecialchars($zone['zone_key']) ?>">
                                <div style="font-size:1.5rem;margin-bottom:6px"><?= $i === 0 ? '🏙️' : '🌳' ?></div>
                                <h4><?= htmlspecialchars($zone['zone_name']) ?></h4>
                                <div class="fee">
                                    <?php if ($zone['delivery_fee'] == 0): ?>
                                        <span class="text-success">ফ্রি ডেলিভারি</span>
                                    <?php else: ?>
                                        ৳<?= number_format($zone['delivery_fee'], 0) ?> ডেলিভারি চার্জ
                                    <?php endif; ?>
                                </div>
                                <?php if ($zone['free_shipping_min_amount'] > 0): ?>
                                <div style="font-size:0.75rem;color:var(--success);margin-top:4px">
                                    ৳<?= number_format($zone['free_shipping_min_amount'], 0) ?>+ অর্ডারে ফ্রি
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card" style="margin-bottom:20px">
                        <h2 class="checkout-title"><i class="fa fa-credit-card"></i> পেমেন্ট পদ্ধতি</h2>
                        <div class="payment-methods">
                            <div class="payment-method active" data-method="cod">
                                <div class="payment-method-icon">💵</div>
                                <div class="payment-method-info">
                                    <h4>ক্যাশ অন ডেলিভারি</h4>
                                    <p>পণ্য পেয়ে হাতে হাতে পেমেন্ট করুন</p>
                                </div>
                                <input type="radio" name="payment_method" value="cod" checked style="margin-left:auto">
                            </div>
                            <div class="payment-method" data-method="mobile_banking">
                                <div class="payment-method-icon">📱</div>
                                <div class="payment-method-info">
                                    <h4>বিকাশ / নগদ / রকেট</h4>
                                    <p>মোবাইল ব্যাংকিং পেমেন্ট</p>
                                </div>
                                <input type="radio" name="payment_method" value="mobile_banking" style="margin-left:auto">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="shippingFeeInput" name="shipping_fee" value="<?= $shippingZones[0]['delivery_fee'] ?? 70 ?>">
                    <input type="hidden" name="direct_product_id" value="<?= $directProductId ?>">
                    <input type="hidden" name="direct_qty" value="<?= $directQty ?>">
                </form>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="checkout-card" style="position:sticky;top:calc(var(--header-h) + 20px)">
                    <h2 class="checkout-title"><i class="fa fa-receipt"></i> অর্ডার সামারি</h2>

                    <div id="orderSummaryItems">
                        <?php if ($directProduct): ?>
                        <?php
                        $img = getFirstImage($directProduct);
                        $currency = getSetting('currency_symbol', '৳');
                        ?>
                        <div class="order-summary-item">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($directProduct['title']) ?>">
                            <div class="order-summary-item-info">
                                <div class="name"><?= htmlspecialchars($directProduct['title']) ?></div>
                                <div class="qty">পরিমাণ: <?= $directQty ?></div>
                                <div class="price"><?= $currency ?><?= number_format($directProduct['selling_price'] * $directQty, 0) ?></div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div id="cartSummaryItems" style="min-height:60px">
                            <p class="text-muted text-center" style="padding:16px;font-size:0.875rem">কার্ট লোড হচ্ছে...</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="order-total-rows">
                        <div class="order-total-row">
                            <span>সাবটোটাল</span>
                            <span id="checkoutSubtotal"
                                <?php if ($directProduct): ?>
                                data-value="<?= $directProduct['selling_price'] * $directQty ?>"
                                <?php else: ?>
                                data-value="0"
                                <?php endif; ?>>
                                <?php if ($directProduct): ?>
                                    <?= $currency ?><?= number_format($directProduct['selling_price'] * $directQty, 0) ?>
                                <?php else: ?>
                                    ৳০
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="order-total-row">
                            <span>ডেলিভারি চার্জ</span>
                            <span id="shippingFeeDisplay">৳<?= number_format($shippingZones[0]['delivery_fee'] ?? 70, 0) ?></span>
                        </div>
                        <div class="order-total-row">
                            <span>ডিসকাউন্ট</span>
                            <span id="checkoutDiscount" data-value="0">—</span>
                        </div>
                        <div class="order-total-row grand">
                            <span>মোট</span>
                            <span id="grandTotalDisplay">
                                <?php if ($directProduct): ?>
                                    ৳<?= number_format($directProduct['selling_price'] * $directQty + ($shippingZones[0]['delivery_fee'] ?? 70), 0) ?>
                                <?php else: ?>
                                    ৳০
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <button class="btn-place-order" id="placeOrderBtn" onclick="document.getElementById('checkoutForm').dispatchEvent(new Event('submit',{cancelable:true,bubbles:true}))">
                        <i class="fa fa-check-circle"></i> অর্ডার কনফার্ম করুন
                    </button>
                    <p style="font-size:0.78rem;color:var(--text3);text-align:center;margin-top:12px">
                        <i class="fa fa-lock"></i> আপনার তথ্য সম্পূর্ণ নিরাপদ
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <div class="success-icon"><i class="fa fa-check"></i></div>
        <h2 style="font-size:1.4rem;font-weight:800;color:var(--white);margin-bottom:8px">অর্ডার সফল হয়েছে! 🎉</h2>
        <p style="color:var(--text3);font-size:0.9rem">আপনার অর্ডারটি গ্রহণ করা হয়েছে। শীঘ্রই আমরা আপনার সাথে যোগাযোগ করব।</p>
        <div class="order-id-box">
            <div style="font-size:0.8rem;color:var(--text3);margin-bottom:6px">আপনার অর্ডার আইডি</div>
            <div class="order-id" id="successOrderId"></div>
        </div>
        <p style="font-size:0.82rem;color:var(--text3);margin-bottom:20px">এই নম্বরটি সংরক্ষণ করুন। অর্ডার ট্র্যাক করতে কাজে লাগবে।</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="/track.php" class="btn btn-primary" style="flex:1"><i class="fa fa-map-marker-alt"></i> অর্ডার ট্র্যাক করুন</a>
            <a href="/shop.php" class="btn btn-ghost" style="flex:1"><i class="fa fa-store"></i> আরো কেনাকাটা</a>
        </div>
    </div>
</div>

<script>
// Load cart into checkout on page load
document.addEventListener('DOMContentLoaded', function() {
    const directProductId = <?= $directProductId ?>;
    if (!directProductId) {
        // Load from cart
        const cart = JSON.parse(localStorage.getItem('bd_cart') || '[]');
        const container = document.getElementById('cartSummaryItems');
        const subtotalEl = document.getElementById('checkoutSubtotal');
        if (!cart.length) {
            if (container) container.innerHTML = '<p class="text-muted text-center" style="padding:16px;font-size:0.875rem">কার্ট খালি। <a href="/shop.php">কেনাকাটা করুন</a></p>';
            return;
        }
        let subtotal = 0;
        if (container) {
            container.innerHTML = cart.map(item => {
                subtotal += item.price * item.qty;
                return `<div class="order-summary-item">
                    <img src="${item.image || 'https://placehold.co/400x400/1a1a2e/6b6b8a?text=No+Image'}" alt="${item.title}" style="width:55px;height:55px;object-fit:cover;border-radius:8px">
                    <div class="order-summary-item-info">
                        <div class="name">${item.title}</div>
                        <div class="qty">পরিমাণ: ${item.qty}</div>
                        <div class="price">৳${(item.price * item.qty).toLocaleString('bn-BD')}</div>
                    </div>
                </div>`;
            }).join('');
        }
        if (subtotalEl) { subtotalEl.dataset.value = subtotal; subtotalEl.textContent = '৳' + subtotal.toLocaleString('bn-BD'); }
        updateCheckoutTotal();
    }
});

// Payment method click
document.querySelectorAll('.payment-method').forEach(pm => {
    pm.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
