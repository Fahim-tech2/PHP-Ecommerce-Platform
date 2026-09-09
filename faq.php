<?php
// =============================================
// FAQ Page — সাধারণ জিজ্ঞাসা
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings  = getAllSettings();
$pageTitle = 'সাধারণ জিজ্ঞাসা (FAQ) | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc  = 'আমাদের সম্পর্কে সাধারণভাবে জিজ্ঞাসিত প্রশ্ন ও উত্তর। অর্ডার, ডেলিভারি, পেমেন্ট সম্পর্কে জানুন।';

$faqs = [
    'অর্ডার' => [
        ['q' => 'কিভাবে অর্ডার করব?', 'q_en' => 'order place', 'a' => 'আমাদের শপ পেজ থেকে পছন্দের পণ্য সিলেক্ট করুন, কার্টে যোগ করুন এবং চেকআউটে গিয়ে আপনার নাম, ঠিকানা ও ফোন নম্বর দিন। সফলভাবে অর্ডার হলে আপনি একটি ট্র্যাকিং কোড পাবেন।'],
        ['q' => 'অর্ডার বাতিল করা যাবে কি?', 'q_en' => 'cancel order', 'a' => 'হ্যাঁ, অর্ডার করার ২৪ ঘণ্টার মধ্যে আমাদের হেল্পলাইনে যোগাযোগ করে অর্ডার বাতিল করা যাবে। শিপমেন্ট হয়ে গেলে বাতিল সম্ভব নয়।'],
        ['q' => 'একসাথে একাধিক পণ্য অর্ডার করতে পারব?', 'q_en' => 'multiple products', 'a' => 'অবশ্যই! কার্টে যত খুশি পণ্য যোগ করুন এবং একসাথে চেকআউট করুন। একাধিক পণ্য অর্ডার করলে ডেলিভারি চার্জে ছাড় পেতে পারেন।'],
        ['q' => 'অর্ডারের স্ট্যাটাস কিভাবে জানব?', 'q_en' => 'order status track', 'a' => 'আমাদের Track পেজে গিয়ে আপনার ট্র্যাকিং কোড বা ফোন নম্বর দিলেই রিয়েল-টাইম অর্ডার স্ট্যাটাস দেখতে পাবেন।'],
        ['q' => 'অর্ডার করার পর কি পরিবর্তন করা যাবে?', 'q_en' => 'modify order', 'a' => 'অর্ডার প্রসেসিং শুরু হওয়ার আগে আমাদের সাপোর্টে যোগাযোগ করলে ঠিকানা বা পণ্যের পরিমাণ পরিবর্তন করা সম্ভব।'],
    ],
    'ডেলিভারি' => [
        ['q' => 'ডেলিভারি কতদিনে হয়?', 'q_en' => 'delivery time days', 'a' => 'ঢাকার ভেতরে সাধারণত ১–২ কার্যদিবস। ঢাকার বাইরে সারাদেশে ৩–৫ কার্যদিবস। বিশেষ পরিস্থিতিতে সামান্য বেশি সময় লাগতে পারে।'],
        ['q' => 'ডেলিভারি চার্জ কত?', 'q_en' => 'delivery charge fee', 'a' => 'ঢাকার ভেতরে ডেলিভারি চার্জ ৬০ টাকা। ঢাকার বাইরে ১০০–১২০ টাকা। নির্দিষ্ট পরিমাণের উপরে অর্ডারে ফ্রি ডেলিভারি পাওয়া যায়।'],
        ['q' => 'সারাদেশে ডেলিভারি দেওয়া হয়?', 'q_en' => 'delivery all over bangladesh', 'a' => 'হ্যাঁ! বাংলাদেশের সব জেলায় আমরা ডেলিভারি দিয়ে থাকি। প্রত্যন্ত অঞ্চলেও কুরিয়ার সার্ভিসের মাধ্যমে ডেলিভারি সম্ভব।'],
        ['q' => 'ডেলিভারি কি ট্র্যাক করা যাবে?', 'q_en' => 'track delivery', 'a' => 'হ্যাঁ, আমাদের Track পেজে ট্র্যাকিং কোড দিয়ে আপনার ডেলিভারির সর্বশেষ অবস্থান জানতে পারবেন।'],
    ],
    'পেমেন্ট' => [
        ['q' => 'কোন কোন উপায়ে পেমেন্ট করা যায়?', 'q_en' => 'payment method', 'a' => 'আমরা ক্যাশ অন ডেলিভারি (COD), বিকাশ, নগদ ও রকেটে পেমেন্ট গ্রহণ করি। সবচেয়ে জনপ্রিয় হলো ক্যাশ অন ডেলিভারি।'],
        ['q' => 'অগ্রিম পেমেন্ট করতে হবে?', 'q_en' => 'advance payment prepaid', 'a' => 'না, বেশিরভাগ ক্ষেত্রেই পণ্য হাতে পেয়ে টাকা দিলেই হবে (ক্যাশ অন ডেলিভারি)। তবে কিছু বিশেষ পণ্যে অগ্রিম পেমেন্ট প্রয়োজন হতে পারে।'],
        ['q' => 'বিকাশে পেমেন্ট করব কিভাবে?', 'q_en' => 'bkash payment', 'a' => 'অর্ডার নিশ্চিত হওয়ার পর আমাদের টিম আপনাকে বিকাশ নম্বর দেবে। সেখানে Send Money করুন এবং Transaction ID আমাদের জানান।'],
        ['q' => 'পেমেন্ট কি নিরাপদ?', 'q_en' => 'payment secure safe', 'a' => 'হ্যাঁ, সম্পূর্ণ নিরাপদ। আমরা কখনো আপনার ব্যক্তিগত আর্থিক তথ্য সংরক্ষণ করি না। ক্যাশ অন ডেলিভারিতে কোনো ঝুঁকি নেই।'],
    ],
    'রিটার্ন' => [
        ['q' => 'পণ্য রিটার্ন বা বদলানো যাবে কি?', 'q_en' => 'return exchange product', 'a' => 'হ্যাঁ, পণ্য পাওয়ার ৭ দিনের মধ্যে যদি কোনো ত্রুটি পাওয়া যায় বা ভুল পণ্য আসে তাহলে রিটার্ন বা বদলের সুবিধা আছে।'],
        ['q' => 'রিফান্ড পেতে কতদিন লাগে?', 'q_en' => 'refund money back', 'a' => 'রিটার্ন গৃহীত হওয়ার পর ৩–৭ কার্যদিবসের মধ্যে রিফান্ড আপনার বিকাশ/নগদ অ্যাকাউন্টে ফেরত দেওয়া হবে।'],
        ['q' => 'কোন পণ্য রিটার্ন করা যাবে না?', 'q_en' => 'non returnable products', 'a' => 'খাদ্য পণ্য, ব্যক্তিগত ব্যবহারের পণ্য (আন্ডারগার্মেন্ট ইত্যাদি), এবং একবার ব্যবহার করা পণ্য রিটার্ন করা যায় না।'],
    ],
    'পণ্য' => [
        ['q' => 'পণ্যগুলো কি আসল (অরিজিনাল)?', 'q_en' => 'original authentic product', 'a' => 'হ্যাঁ, আমরা ১০০% অরিজিনাল ও গ্যারান্টিযুক্ত পণ্য বিক্রি করি। নকল পণ্য পেলে সম্পূর্ণ রিফান্ড দেওয়া হবে।'],
        ['q' => 'আউট অফ স্টক পণ্য কখন পাওয়া যাবে?', 'q_en' => 'out of stock restock', 'a' => 'স্টক শেষ হওয়া পণ্যে আপনার নম্বর দিলে আমরা স্টক আসার সাথে সাথে জানাব। সাধারণত ৭–১৪ দিনে রিস্টক হয়।'],
        ['q' => 'পণ্যের ওয়ারেন্টি আছে কি?', 'q_en' => 'warranty guarantee', 'a' => 'নির্দিষ্ট পণ্যের ক্ষেত্রে ওয়ারেন্টি প্রযোজ্য। প্রতিটি পণ্যের বিবরণে ওয়ারেন্টি সংক্রান্ত তথ্য উল্লেখ থাকে।'],
    ],
];

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero faq-hero">
    <div class="faq-hero-glow faq-glow-1"></div>
    <div class="faq-hero-glow faq-glow-2"></div>
    <div class="container" style="position:relative;z-index:1">
        <div class="faq-hero-icon">❓</div>
        <h1 class="page-hero-title">সাধারণ জিজ্ঞাসা</h1>
        <p class="faq-hero-sub">আপনার মনে কোনো প্রশ্ন আছে? আমরা এখানে সাহায্য করতে প্রস্তুত।</p>

        <!-- Search Bar -->
        <div class="faq-search-wrap" id="faqSearchWrap">
            <i class="fa fa-search faq-search-icon"></i>
            <input
                type="text"
                id="faqSearchInput"
                class="faq-search-input"
                placeholder="প্রশ্ন খুঁজুন... (যেমন: ডেলিভারি, পেমেন্ট, রিটার্ন)"
                autocomplete="off"
            >
            <button class="faq-search-clear" id="faqSearchClear" style="display:none">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb" style="justify-content:center;margin-top:16px">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <span>সাধারণ জিজ্ঞাসা</span>
        </div>
    </div>
</div>

<section class="section faq-section">
    <div class="container">

        <!-- Category Tabs -->
        <div class="faq-tabs" id="faqTabs">
            <button class="faq-tab active" data-cat="সব" onclick="filterFaq(this,'সব')">
                🌐 সব
            </button>
            <?php
            $tabIcons = ['অর্ডার'=>'📦','ডেলিভারি'=>'🚚','পেমেন্ট'=>'💳','রিটার্ন'=>'🔄','পণ্য'=>'🛍️'];
            foreach (array_keys($faqs) as $cat): ?>
            <button class="faq-tab" data-cat="<?= $cat ?>" onclick="filterFaq(this,'<?= $cat ?>')">
                <?= $tabIcons[$cat] ?? '📌' ?> <?= $cat ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- No Results -->
        <div class="faq-no-results" id="faqNoResults" style="display:none">
            <div style="font-size:3.5rem;margin-bottom:16px">🔍</div>
            <h3>কোনো প্রশ্ন পাওয়া যায়নি</h3>
            <p>অন্য কীওয়ার্ড দিয়ে খোঁজার চেষ্টা করুন</p>
            <a href="/contact.php" class="btn btn-primary" style="margin-top:16px">
                <i class="fa fa-phone"></i> সরাসরি জিজ্ঞেস করুন
            </a>
        </div>

        <!-- FAQ Groups -->
        <?php $gi = 0; foreach ($faqs as $cat => $items): $gi++; ?>
        <div class="faq-group" data-cat="<?= $cat ?>" id="faqGroup<?= $gi ?>">
            <div class="faq-group-header">
                <span class="faq-group-icon"><?= $tabIcons[$cat] ?? '📌' ?></span>
                <h2 class="faq-group-title"><?= $cat ?> সংক্রান্ত প্রশ্ন</h2>
                <span class="faq-group-count"><?= count($items) ?>টি প্রশ্ন</span>
            </div>

            <div class="faq-list">
                <?php foreach ($items as $idx => $item): ?>
                <div class="faq-item" data-q="<?= htmlspecialchars(mb_strtolower($item['q'])) ?>" data-qen="<?= htmlspecialchars($item['q_en']) ?>" data-cat="<?= $cat ?>">
                    <button class="faq-question" onclick="toggleFaq(this)" id="faqQ<?= $gi ?>_<?= $idx ?>">
                        <span class="faq-q-icon">
                            <i class="fa fa-question-circle"></i>
                        </span>
                        <span class="faq-q-text"><?= htmlspecialchars($item['q']) ?></span>
                        <span class="faq-arrow">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </button>
                    <div class="faq-answer" role="region">
                        <div class="faq-answer-inner">
                            <i class="fa fa-check-circle faq-ans-icon"></i>
                            <?= htmlspecialchars($item['a']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Still Need Help CTA -->
        <div class="faq-cta" id="faqCta">
            <div class="faq-cta-glow"></div>
            <div class="faq-cta-content">
                <div class="faq-cta-icon">💬</div>
                <h3 class="faq-cta-title">আরও প্রশ্ন আছে?</h3>
                <p class="faq-cta-desc">আমাদের সাপোর্ট টিম সপ্তাহের ৭ দিন, সকাল ৯টা থেকে রাত ১০টা পর্যন্ত আপনার পাশে আছে।</p>
                <div class="faq-cta-actions">
                    <a href="/contact.php" class="btn btn-primary btn-lg" id="faqContactBtn">
                        <i class="fa fa-phone"></i> যোগাযোগ করুন
                    </a>
                    <?php $phone = $settings['contact_phone'] ?? ''; if ($phone): ?>
                    <a href="https://wa.me/880<?= ltrim($phone,'0') ?>" class="btn btn-outline btn-lg" target="_blank" rel="noopener" id="faqWhatsappBtn">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
                <div class="faq-cta-stats">
                    <div class="faq-cta-stat">
                        <span class="faq-cta-stat-num">⚡ ৫ মিনিট</span>
                        <span class="faq-cta-stat-lbl">গড় রেসপন্স টাইম</span>
                    </div>
                    <div class="faq-cta-stat">
                        <span class="faq-cta-stat-num">😊 ৯৮%</span>
                        <span class="faq-cta-stat-lbl">গ্রাহক সন্তুষ্টি</span>
                    </div>
                    <div class="faq-cta-stat">
                        <span class="faq-cta-stat-num">🕐 ৭ দিন</span>
                        <span class="faq-cta-stat-lbl">সাপোর্ট উপলব্ধ</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ========================= STYLES ========================= -->
<style>
/* ---------- Hero ---------- */
.faq-hero {
    position: relative;
    text-align: center;
    padding: 80px 0 60px;
    overflow: hidden;
}
.faq-hero-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: .35;
    pointer-events: none;
}
.faq-glow-1 {
    width: 500px; height: 500px;
    background: var(--primary);
    top: -150px; left: -100px;
}
.faq-glow-2 {
    width: 400px; height: 400px;
    background: var(--accent);
    bottom: -100px; right: -80px;
}
.faq-hero-icon {
    font-size: 4rem;
    margin-bottom: 16px;
    display: inline-block;
    animation: faqBounce 2.5s ease-in-out infinite;
}
@keyframes faqBounce {
    0%,100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-12px) rotate(8deg); }
}
.faq-hero-sub {
    color: var(--text3);
    font-size: 1.05rem;
    margin: 8px 0 28px;
}

/* ---------- Search ---------- */
.faq-search-wrap {
    position: relative;
    max-width: 580px;
    margin: 0 auto;
}
.faq-search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text3);
    font-size: 1rem;
    pointer-events: none;
}
.faq-search-input {
    width: 100%;
    padding: 16px 50px 16px 48px;
    background: var(--card);
    border: 1.5px solid var(--border2);
    border-radius: 50px;
    color: var(--text);
    font-size: 0.95rem;
    font-family: inherit;
    transition: all .25s;
    box-shadow: 0 4px 24px rgba(0,0,0,.25);
    box-sizing: border-box;
}
.faq-search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(108,99,255,.2), 0 4px 24px rgba(0,0,0,.3);
}
.faq-search-clear {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text3);
    cursor: pointer;
    padding: 4px 8px;
    font-size: 0.9rem;
    transition: color .2s;
}
.faq-search-clear:hover { color: var(--accent); }

/* ---------- Tabs ---------- */
.faq-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 40px;
    justify-content: center;
}
.faq-tab {
    padding: 9px 20px;
    border-radius: 50px;
    border: 1.5px solid var(--border2);
    background: var(--card);
    color: var(--text2);
    font-family: inherit;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .22s;
}
.faq-tab:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-1px);
}
.faq-tab.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 4px 15px rgba(108,99,255,.35);
}

/* ---------- No Results ---------- */
.faq-no-results {
    text-align: center;
    padding: 60px 0;
    color: var(--text2);
}
.faq-no-results h3 { margin-bottom: 8px; }
.faq-no-results p  { color: var(--text3); }

/* ---------- Group ---------- */
.faq-group {
    margin-bottom: 48px;
    transition: all .3s;
}
.faq-group.hidden { display: none; }

.faq-group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border2);
}
.faq-group-icon  { font-size: 1.6rem; }
.faq-group-title {
    flex: 1;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}
.faq-group-count {
    background: var(--bg2);
    color: var(--text3);
    font-size: 0.78rem;
    padding: 3px 10px;
    border-radius: 50px;
    border: 1px solid var(--border2);
}

/* ---------- FAQ Item ---------- */
.faq-list { display: flex; flex-direction: column; gap: 10px; }

.faq-item {
    background: var(--card);
    border: 1px solid var(--border2);
    border-radius: var(--radius);
    overflow: hidden;
    transition: border-color .22s, box-shadow .22s, transform .22s;
}
.faq-item:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 20px rgba(108,99,255,.12);
    transform: translateY(-1px);
}
.faq-item.faq-open {
    border-color: var(--primary);
    box-shadow: 0 6px 28px rgba(108,99,255,.18);
}
.faq-item.faq-hidden { display: none; }

/* ---------- Question Button ---------- */
.faq-question {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text);
    transition: background .2s;
}
.faq-question:hover { background: rgba(108,99,255,.05); }
.faq-open .faq-question { background: rgba(108,99,255,.07); }

.faq-q-icon {
    color: var(--primary);
    font-size: 1rem;
    flex-shrink: 0;
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(108,99,255,.12);
    border-radius: 50%;
    transition: background .2s;
}
.faq-open .faq-q-icon { background: var(--primary); color: #fff; }

.faq-q-text { flex: 1; line-height: 1.5; }

.faq-arrow {
    flex-shrink: 0;
    color: var(--text3);
    font-size: 0.8rem;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), color .2s;
}
.faq-open .faq-arrow {
    transform: rotate(180deg);
    color: var(--primary);
}

/* ---------- Answer ---------- */
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height .38s cubic-bezier(.4,0,.2,1);
}
.faq-open .faq-answer { max-height: 500px; }

.faq-answer-inner {
    display: flex;
    gap: 12px;
    padding: 0 20px 20px 20px;
    color: var(--text2);
    font-size: 0.9rem;
    line-height: 1.7;
    border-top: 1px dashed var(--border2);
    padding-top: 16px;
    margin: 0 20px;
}
.faq-ans-icon {
    color: var(--primary);
    flex-shrink: 0;
    margin-top: 3px;
    font-size: 0.95rem;
}

/* ---------- Search Highlight ---------- */
mark.faq-hl {
    background: rgba(108,99,255,.25);
    color: var(--primary);
    border-radius: 3px;
    padding: 0 2px;
}

/* ---------- CTA ---------- */
.faq-cta {
    position: relative;
    background: var(--card);
    border: 1px solid var(--border2);
    border-radius: var(--radius-lg);
    padding: 60px 40px;
    text-align: center;
    margin-top: 60px;
    overflow: hidden;
}
.faq-cta-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, rgba(108,99,255,.12) 0%, transparent 70%);
    pointer-events: none;
}
.faq-cta-content { position: relative; z-index: 1; }
.faq-cta-icon { font-size: 3.5rem; margin-bottom: 16px; animation: faqBounce 3s ease-in-out infinite; }
.faq-cta-title { font-size: 1.6rem; font-weight: 800; margin-bottom: 10px; }
.faq-cta-desc  { color: var(--text3); font-size: 0.95rem; margin-bottom: 28px; max-width: 480px; margin-left: auto; margin-right: auto; }

.faq-cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px; }

.faq-cta-stats {
    display: flex;
    gap: 32px;
    justify-content: center;
    flex-wrap: wrap;
    border-top: 1px solid var(--border2);
    padding-top: 24px;
}
.faq-cta-stat { display: flex; flex-direction: column; gap: 4px; }
.faq-cta-stat-num { font-weight: 800; font-size: 1.05rem; color: var(--text); }
.faq-cta-stat-lbl { font-size: 0.8rem; color: var(--text3); }

/* ---------- Section ---------- */
.faq-section { padding-top: 60px; }

/* ---------- Responsive ---------- */
@media (max-width: 640px) {
    .faq-cta { padding: 40px 20px; }
    .faq-question { padding: 14px 16px; font-size: 0.88rem; }
    .faq-answer-inner { margin: 0 12px; padding: 0 0 16px; font-size: 0.85rem; }
    .faq-group-title { font-size: 1rem; }
}
</style>

<!-- ========================= SCRIPT ========================= -->
<script>
// ── Toggle accordion ──────────────────────────────────────────
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const isOpen = item.classList.contains('faq-open');

    // Close all open items in same group
    item.closest('.faq-list').querySelectorAll('.faq-item.faq-open').forEach(el => {
        if (el !== item) el.classList.remove('faq-open');
    });

    item.classList.toggle('faq-open', !isOpen);
}

// ── Category filter ───────────────────────────────────────────
function filterFaq(btn, cat) {
    document.querySelectorAll('.faq-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    const groups = document.querySelectorAll('.faq-group');
    groups.forEach(g => {
        const match = cat === 'সব' || g.dataset.cat === cat;
        g.classList.toggle('hidden', !match);
    });

    // Clear search
    const inp = document.getElementById('faqSearchInput');
    inp.value = '';
    document.getElementById('faqSearchClear').style.display = 'none';
    resetSearchHighlights();
    checkNoResults();
}

// ── Search ───────────────────────────────────────────────────
const searchInput = document.getElementById('faqSearchInput');
const clearBtn    = document.getElementById('faqSearchClear');

searchInput.addEventListener('input', function () {
    const val = this.value.trim().toLowerCase();
    clearBtn.style.display = val ? 'flex' : 'none';

    // Reset tabs
    document.querySelectorAll('.faq-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.faq-tab[data-cat="সব"]').classList.add('active');
    document.querySelectorAll('.faq-group').forEach(g => g.classList.remove('hidden'));

    resetSearchHighlights();

    if (!val) { checkNoResults(); return; }

    document.querySelectorAll('.faq-item').forEach(item => {
        const q    = item.dataset.q   || '';
        const qen  = item.dataset.qen || '';
        const ansEl = item.querySelector('.faq-answer-inner');
        const ans  = ansEl ? ansEl.textContent.toLowerCase() : '';
        const hit  = q.includes(val) || qen.includes(val) || ans.includes(val);

        item.classList.toggle('faq-hidden', !hit);

        if (hit) highlightText(item, val);
    });

    // Hide groups with no visible items
    document.querySelectorAll('.faq-group').forEach(g => {
        const visible = g.querySelectorAll('.faq-item:not(.faq-hidden)').length;
        g.classList.toggle('hidden', visible === 0);
    });

    checkNoResults();
});

clearBtn.addEventListener('click', function () {
    searchInput.value = '';
    this.style.display = 'none';
    resetSearchHighlights();
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('faq-hidden'));
    document.querySelectorAll('.faq-group').forEach(g => g.classList.remove('hidden'));
    checkNoResults();
});

function checkNoResults() {
    const anyVisible = document.querySelectorAll('.faq-group:not(.hidden)').length > 0;
    document.getElementById('faqNoResults').style.display = anyVisible ? 'none' : 'block';
    document.getElementById('faqCta').style.display = anyVisible ? 'block' : 'none';
}

function highlightText(item, term) {
    const qText = item.querySelector('.faq-q-text');
    if (qText) {
        const txt = qText.getAttribute('data-orig') || qText.textContent;
        qText.setAttribute('data-orig', txt);
        qText.innerHTML = txt.replace(new RegExp('(' + escapeRe(term) + ')', 'gi'),
            '<mark class="faq-hl">$1</mark>');
    }
}

function resetSearchHighlights() {
    document.querySelectorAll('.faq-q-text[data-orig]').forEach(el => {
        el.textContent = el.getAttribute('data-orig');
        el.removeAttribute('data-orig');
    });
}

function escapeRe(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ── Open first FAQ on load (nice UX) ─────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.faq-item');
    if (first) first.classList.add('faq-open');
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
