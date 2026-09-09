<?php
// =============================================
// Order Tracking Page — অর্ডার ট্র্যাকিং
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$pageTitle = 'অর্ডার ট্র্যাক করুন | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc  = 'আপনার অর্ডারের বর্তমান অবস্থা জানুন।';
include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">📦 অর্ডার ট্র্যাক করুন</h1>
        <div class="breadcrumb">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <span>অর্ডার ট্র্যাক</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="tracking-page">
            <!-- Search Form -->
            <div class="tracking-form-card">
                <h2 style="font-size:1.3rem;font-weight:700;color:var(--white);margin-bottom:6px">অর্ডারের তথ্য দিন</h2>
                <p style="font-size:0.875rem;color:var(--text3);margin-bottom:24px">অর্ডার নম্বর ও ফোন নম্বর দিয়ে আপনার অর্ডার ট্র্যাক করুন।</p>
                <form id="trackForm">
                    <div class="form-group">
                        <label class="form-label">অর্ডার নম্বর <span style="color:var(--accent)">*</span></label>
                        <input type="text" id="trackCode" class="form-control" placeholder="যেমন: ORD-78421" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">মোবাইল নম্বর</label>
                        <input type="tel" id="trackPhone" class="form-control" placeholder="01XXXXXXXXX">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                        <i class="fa fa-search"></i> অর্ডার খুঁজুন
                    </button>
                </form>
            </div>

            <!-- Result -->
            <div id="trackResult" style="display:none"></div>

            <!-- Info -->
            <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-top:20px">
                <h3 style="font-size:0.95rem;font-weight:700;color:var(--white);margin-bottom:12px">
                    <i class="fa fa-info-circle" style="color:var(--info)"></i> অর্ডার স্ট্যাটাস মানে কী?
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px">
                    <?php
                    $statuses = [
                        ['icon'=>'📋','color'=>'var(--warning)','label'=>'পেন্ডিং','desc'=>'আপনার অর্ডার গ্রহণ করা হয়েছে, শীঘ্রই প্রসেস শুরু হবে।'],
                        ['icon'=>'⚙️','color'=>'var(--info)','label'=>'প্রসেসিং','desc'=>'আপনার পণ্য প্যাক করা হচ্ছে।'],
                        ['icon'=>'🚚','color'=>'#a855f7','label'=>'কুরিয়ারে পাঠানো হয়েছে','desc'=>'পণ্য ডেলিভারির পথে রয়েছে।'],
                        ['icon'=>'✅','color'=>'var(--success)','label'=>'ডেলিভার হয়েছে','desc'=>'পণ্য সফলভাবে পৌঁছে গেছে।'],
                        ['icon'=>'❌','color'=>'var(--danger)','label'=>'বাতিল','desc'=>'অর্ডারটি বাতিল করা হয়েছে।'],
                    ];
                    foreach ($statuses as $s): ?>
                    <div style="display:flex;align-items:flex-start;gap:10px">
                        <span style="font-size:1.2rem"><?= $s['icon'] ?></span>
                        <div>
                            <span style="font-weight:600;color:<?= $s['color'] ?>"><?= $s['label'] ?>:</span>
                            <span style="font-size:0.85rem;color:var(--text3)"> <?= $s['desc'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
