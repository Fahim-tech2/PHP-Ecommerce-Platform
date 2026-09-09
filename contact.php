<?php
// =============================================
// Contact Page — যোগাযোগ পেজ
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$pageTitle = 'যোগাযোগ | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc  = 'আমাদের সাথে যোগাযোগ করুন।';
include __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">📞 যোগাযোগ করুন</h1>
        <div class="breadcrumb">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <span>যোগাযোগ</span>
        </div>
    </div>
</div>
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info-card">
                <h2 style="font-size:1.3rem;font-weight:700;color:var(--white);margin-bottom:24px">আমাদের তথ্য</h2>
                <?php if ($settings['contact_phone']): ?>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa fa-phone"></i></div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3);margin-bottom:4px">ফোন নম্বর</div>
                        <a href="tel:<?= htmlspecialchars($settings['contact_phone']) ?>" style="color:var(--text);font-weight:600"><?= htmlspecialchars($settings['contact_phone']) ?></a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($settings['contact_email']): ?>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa fa-envelope"></i></div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3);margin-bottom:4px">ইমেইল</div>
                        <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>" style="color:var(--text);font-weight:600"><?= htmlspecialchars($settings['contact_email']) ?></a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($settings['address']): ?>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa fa-map-marker-alt"></i></div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3);margin-bottom:4px">ঠিকানা</div>
                        <span style="color:var(--text)"><?= htmlspecialchars($settings['address']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($settings['facebook_url']): ?>
                <div class="contact-item">
                    <div class="contact-icon" style="background:rgba(24,119,242,0.15);color:#1877f2"><i class="fab fa-facebook"></i></div>
                    <div>
                        <div style="font-size:0.8rem;color:var(--text3);margin-bottom:4px">ফেসবুক</div>
                        <a href="<?= htmlspecialchars($settings['facebook_url']) ?>" target="_blank" style="color:var(--primary-light)">আমাদের ফেসবুক পেজ</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- WhatsApp -->
                <?php if ($settings['contact_phone']): ?>
                <div style="margin-top:24px">
                    <a href="https://wa.me/880<?= ltrim($settings['contact_phone'],'0') ?>" target="_blank"
                       class="btn btn-primary btn-block" style="background:linear-gradient(135deg,#25d366,#128c7e)">
                        <i class="fab fa-whatsapp"></i> WhatsApp এ মেসেজ করুন
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Contact Form -->
            <div class="checkout-card">
                <h2 style="font-size:1.3rem;font-weight:700;color:var(--white);margin-bottom:24px">মেসেজ পাঠান</h2>
                <form id="contactForm" onsubmit="handleContactForm(event)">
                    <div class="form-group">
                        <label class="form-label">আপনার নাম <span style="color:var(--accent)">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="নাম লিখুন" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">মোবাইল নম্বর <span style="color:var(--accent)">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="01XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">বিষয়</label>
                        <select name="subject" class="form-control">
                            <option value="order">অর্ডার সম্পর্কে</option>
                            <option value="product">পণ্য সম্পর্কে</option>
                            <option value="delivery">ডেলিভারি সম্পর্কে</option>
                            <option value="return">রিটার্ন সম্পর্কে</option>
                            <option value="other">অন্যান্য</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">মেসেজ <span style="color:var(--accent)">*</span></label>
                        <textarea name="message" class="form-control" rows="5" placeholder="আপনার মেসেজ লিখুন..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fa fa-paper-plane"></i> মেসেজ পাঠান
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
function handleContactForm(e) {
    e.preventDefault();
    showToast('ধন্যবাদ! আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।', 'success');
    e.target.reset();
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
