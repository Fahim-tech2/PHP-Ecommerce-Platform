<?php
// =============================================
// Frontend Footer Template
// =============================================
$settings = $settings ?? getAllSettings();
$siteName = $settings['site_title'] ?? 'বাংলা শপ';
$contactPhone = $settings['contact_phone'] ?? '';
$contactEmail = $settings['contact_email'] ?? '';
$facebookUrl  = $settings['facebook_url'] ?? '';
$instagramUrl = $settings['instagram_url'] ?? '';
$address      = $settings['address'] ?? 'ঢাকা, বাংলাদেশ';
$categories   = getCategories();
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="/" class="site-logo" style="margin-bottom:12px;display:inline-flex">
                    <div class="logo-emoji">🛍️</div>
                    <div class="logo-text" style="margin-left:8px"><?= htmlspecialchars($siteName) ?></div>
                </a>
                <p class="footer-desc"><?= htmlspecialchars($settings['tagline'] ?? 'বাংলাদেশের সেরা অনলাইন শপিং প্ল্যাটফর্ম। সারা দেশে ক্যাশ অন ডেলিভারি।') ?></p>
                <div class="social-links">
                    <?php if ($facebookUrl): ?>
                    <a href="<?= htmlspecialchars($facebookUrl) ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if ($instagramUrl): ?>
                    <a href="<?= htmlspecialchars($instagramUrl) ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if ($contactPhone): ?>
                    <a href="https://wa.me/880<?= ltrim($contactPhone,'0') ?>" class="social-link" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-title">দ্রুত লিংক</h4>
                <ul class="footer-links">
                    <li><a href="/"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> হোম</a></li>
                    <li><a href="/shop.php"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> পণ্যসমূহ</a></li>
                    <li><a href="/track.php"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> অর্ডার ট্র্যাক</a></li>
                    <li><a href="/contact.php"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> যোগাযোগ</a></li>
                    <li><a href="/faq.php"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> সাধারণ জিজ্ঞাসা</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="footer-title">ক্যাটাগরি</h4>
                <ul class="footer-links">
                    <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                    <li><a href="/shop.php?category=<?= $cat['id'] ?>"><i class="fa fa-chevron-right" style="font-size:0.7rem;margin-right:4px"></i> <?= htmlspecialchars($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="footer-title">যোগাযোগ করুন</h4>
                <ul class="footer-links">
                    <?php if ($contactPhone): ?>
                    <li><a href="tel:<?= htmlspecialchars($contactPhone) ?>"><i class="fa fa-phone" style="margin-right:6px;color:var(--primary-light)"></i> <?= htmlspecialchars($contactPhone) ?></a></li>
                    <?php endif; ?>
                    <?php if ($contactEmail): ?>
                    <li><a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><i class="fa fa-envelope" style="margin-right:6px;color:var(--primary-light)"></i> <?= htmlspecialchars($contactEmail) ?></a></li>
                    <?php endif; ?>
                    <?php if ($address): ?>
                    <li><span style="color:var(--text3);font-size:0.875rem"><i class="fa fa-map-marker-alt" style="margin-right:6px;color:var(--primary-light)"></i> <?= htmlspecialchars($address) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. সর্বস্বত্ব সংরক্ষিত।</p>
            <p style="display:flex;align-items:center;gap:12px">
                <span>💳 ক্যাশ অন ডেলিভারি</span>
                <span>🚚 দ্রুত ডেলিভারি</span>
                <span>✅ ১০০% অরিজিনাল</span>
            </p>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script src="/assets/js/main.js"></script>
<?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
