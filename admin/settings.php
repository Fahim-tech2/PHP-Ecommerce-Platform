<?php
// =============================================
// General Settings
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

if (adminRole() !== 'admin') {
    echo "<script>alert('আপনার এই পেজে প্রবেশের অনুমতি নেই'); window.location='/admin/dashboard.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['site_title', 'tagline', 'contact_phone', 'contact_email', 'address', 'facebook_url', 'instagram_url', 'hero_title', 'hero_subtitle', 'primary_color', 'accent_color', 'currency_symbol'];
    
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            updateSetting($key, $_POST[$key]);
        }
    }

    // Images
    $logo = handleImageUpload('logo', 'settings');
    if ($logo) updateSetting('logo_url', $logo);

    $favicon = handleImageUpload('favicon', 'settings');
    if ($favicon) updateSetting('favicon_url', $favicon);

    echo "<script>alert('সেটিংস আপডেট হয়েছে!'); window.location='/admin/settings.php';</script>";
    exit;
}

$s = getAllSettings();
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <h1 class="admin-page-title">সাইট সেটিংস</h1>
        <p class="admin-page-subtitle">আপনার শপের নাম, লোগো, কালার এবং যোগাযোগের তথ্য পরিবর্তন করুন।</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="form-grid" style="grid-template-columns: 1fr 1fr; align-items: start;">
        
        <!-- General Info -->
        <div class="admin-form-card">
            <h3 class="admin-form-title"><i class="fa fa-info-circle"></i> সাধারণ তথ্য</h3>
            
            <div class="form-group mb-4">
                <label class="form-label">ওয়েবসাইটের নাম</label>
                <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($s['site_title'] ?? 'বাংলা শপ') ?>">
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">ট্যাগলাইন (Tagline)</label>
                <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($s['tagline'] ?? '') ?>">
            </div>

            <div class="form-group mb-4">
                <label class="form-label">হিরো টাইটেল (হোম পেজ ব্যানার)</label>
                <textarea name="hero_title" class="form-control" rows="2"><?= htmlspecialchars($s['hero_title'] ?? "বাংলাদেশের সেরা\nঅনলাইন শপিং") ?></textarea>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">হিরো সাবটাইটেল</label>
                <textarea name="hero_subtitle" class="form-control" rows="2"><?= htmlspecialchars($s['hero_subtitle'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">মুদ্রা (Currency Symbol)</label>
                <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($s['currency_symbol'] ?? '৳') ?>" style="max-width:100px">
            </div>
        </div>

        <div>
            <!-- Contact & Social -->
            <div class="admin-form-card mb-4">
                <h3 class="admin-form-title"><i class="fa fa-phone"></i> যোগাযোগ ও সোশ্যাল</h3>
                
                <div class="form-group mb-4">
                    <label class="form-label">ফোন নম্বর (WhatsApp সহ)</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($s['contact_phone'] ?? '') ?>">
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label">ইমেইল</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($s['contact_email'] ?? '') ?>">
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">ঠিকানা</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($s['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">ফেসবুক পেজ লিংক</label>
                    <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars($s['facebook_url'] ?? '') ?>">
                </div>
            </div>

            <!-- Design & Images -->
            <div class="admin-form-card">
                <h3 class="admin-form-title"><i class="fa fa-paint-brush"></i> ডিজাইন ও লোগো</h3>
                
                <div style="display:flex;gap:20px;margin-bottom:20px">
                    <div style="flex:1">
                        <label class="form-label">প্রাইমারি কালার</label>
                        <div style="display:flex;gap:10px">
                            <input type="color" id="primaryColorPicker" class="form-control" value="<?= htmlspecialchars($s['primary_color'] ?? '#6C63FF') ?>" style="padding:0;width:50px;height:42px">
                            <input type="text" name="primary_color" id="primaryColorText" class="form-control flex-1" value="<?= htmlspecialchars($s['primary_color'] ?? '#6C63FF') ?>">
                        </div>
                    </div>
                    <div style="flex:1">
                        <label class="form-label">অ্যাকসেন্ট কালার (বাটন/অফার)</label>
                        <div style="display:flex;gap:10px">
                            <input type="color" id="accentColorPicker" class="form-control" value="<?= htmlspecialchars($s['accent_color'] ?? '#FF6584') ?>" style="padding:0;width:50px;height:42px">
                            <input type="text" name="accent_color" id="accentColorText" class="form-control flex-1" value="<?= htmlspecialchars($s['accent_color'] ?? '#FF6584') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">সাইট লোগো</label>
                    <input type="file" name="logo" id="logoUpload" class="form-control" accept="image/*" style="padding:8px;background:var(--bg2)">
                    <div id="logoPreview" style="margin-top:10px">
                        <?php if (!empty($s['logo_url'])): ?>
                            <img src="/<?= htmlspecialchars($s['logo_url']) ?>" style="max-height:60px;background:var(--bg3);padding:10px;border-radius:var(--radius-sm)">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">ফেভিকন (ব্রাউজার ট্যাবের আইকন)</label>
                    <input type="file" name="favicon" id="faviconUpload" class="form-control" accept="image/*" style="padding:8px;background:var(--bg2)">
                    <div id="faviconPreview" style="margin-top:10px">
                        <?php if (!empty($s['favicon_url'])): ?>
                            <img src="/<?= htmlspecialchars($s['favicon_url']) ?>" style="width:32px;height:32px;border-radius:4px">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="position:sticky;bottom:20px;background:var(--card);padding:20px;border-radius:var(--radius);border:1px solid var(--border);margin-top:20px;box-shadow:var(--shadow-lg);display:flex;justify-content:flex-end">
        <button type="submit" class="btn-admin btn-admin-primary btn-lg"><i class="fa fa-save"></i> পরিবর্তন সেভ করুন</button>
    </div>
</form>

<script>
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>

            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
