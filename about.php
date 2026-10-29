<?php
# Database Connection
include "db_conn.php";

# Pull real counts from DB
include "php/func-book.php";
include "php/func-author.php";
include "php/func-category.php";

$allBooks      = get_all_books($conn);
$allBooksCount = count($allBooks);
$authors       = get_all_author($conn);
$categories    = get_all_categories($conn);

$pageTitle = "About — Online Book Store";

# Tell header.php to use simple nav on this page
$simpleNav = true;

include "header.php";
?>

<style>
    .about-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
        color: #fff; padding: 80px 0 60px; text-align: center;
        border-radius: 0 0 30px 30px; margin-bottom: 60px;
        position: relative; overflow: hidden;
    }
    .about-hero::before { content:''; position:absolute; top:-60px; right:-60px; width:280px; height:280px; background:rgba(255,255,255,0.04); border-radius:50%; }
    .about-hero::after  { content:''; position:absolute; bottom:-80px; left:-40px; width:320px; height:320px; background:rgba(255,255,255,0.03); border-radius:50%; }
    .about-hero h1 { font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; margin-bottom: 1rem; }
    .about-hero p  { font-size: 1.1rem; opacity: .8; max-width: 600px; margin: 0 auto 28px; }
    .hero-badge { display: inline-block; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 20px; padding: 5px 18px; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.05em; }
    .section-title { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-bottom: .5rem; }
    .section-sub { color: var(--text-muted); font-size: .95rem; margin-bottom: 2.5rem; }
    .feature-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; text-align: center; height: 100%; transition: transform .25s, box-shadow .25s; position: relative; overflow: hidden; }
    .feature-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, #0f3460, #e94560); opacity: 0; transition: opacity 0.3s; }
    .feature-card:hover { transform: translateY(-6px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .feature-card:hover::before { opacity: 1; }
    .feature-icon { font-size: 2.5rem; margin-bottom: 1rem; }
    .feature-card h5 { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 600; margin-bottom: .6rem; color: var(--text-primary); }
    .feature-card p  { font-size: .9rem; color: var(--text-muted); margin: 0; line-height: 1.6; }
    .stat-box { text-align: center; padding: 2.5rem 2rem; background: var(--bg-secondary); border-radius: 16px; border: 1px solid var(--border-color); transition: transform .25s, box-shadow .25s; }
    .stat-box:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .stat-box h2 { font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: var(--category-active-bg, #0d6efd); margin: 0; }
    .stat-box p  { color: var(--text-muted); font-size: .85rem; text-transform: uppercase; letter-spacing: .07em; margin: .3rem 0 0; }
    .team-section { background: var(--bg-secondary); border-radius: 20px; padding: 50px 40px; margin: 60px 0; border: 1px solid var(--border-color); }
    .mission-text { color: var(--text-secondary); line-height: 1.8; }
    .mission-emoji { font-size: 8rem; line-height: 1; }
    .mission-tagline { color: var(--text-muted); font-size: .85rem; margin-top: .5rem; }
    .timeline { position: relative; padding-left: 32px; }
    .timeline::before { content:''; position:absolute; left:10px; top:0; bottom:0; width:2px; background: var(--border-color); }
    .timeline-item { position: relative; margin-bottom: 28px; }
    .timeline-item::before { content:''; position:absolute; left:-26px; top:6px; width:12px; height:12px; border-radius:50%; background: linear-gradient(135deg, #0f3460, #e94560); border: 2px solid var(--bg-primary); }
    .timeline-item h6 { font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
    .timeline-item p  { font-size: 0.88rem; color: var(--text-muted); margin: 0; }
    .timeline-year { font-size: 0.75rem; font-weight: 700; color: #e94560; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .cta-section { background: linear-gradient(135deg, #1a1a2e, #0f3460); border-radius: 20px; padding: 50px 40px; text-align: center; margin-bottom: 60px; color: #fff; }
    .cta-section h2 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 12px; }
    .cta-section p  { opacity: 0.8; margin-bottom: 24px; }
    .btn-cta { background: linear-gradient(135deg, #e94560, #c0392b); color: #fff; border: none; padding: 12px 36px; border-radius: 30px; font-weight: 700; font-size: 1rem; text-decoration: none; display: inline-block; transition: opacity 0.2s, transform 0.2s; }
    .btn-cta:hover { opacity: 0.88; transform: scale(1.04); color: #fff; }
    .site-footer { color: var(--text-muted); font-size: .85rem; border-top: 1px solid var(--border-color); }
    @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
    .fade-up   { animation: fadeUp 0.5s ease both; }
    .fade-up-1 { animation-delay: 0.1s; } .fade-up-2 { animation-delay: 0.2s; }
    .fade-up-3 { animation-delay: 0.3s; } .fade-up-4 { animation-delay: 0.4s; }
    .fade-up-5 { animation-delay: 0.5s; } .fade-up-6 { animation-delay: 0.6s; }
</style>

    <!-- HERO -->
    <div class="about-hero fade-up">
        <h1 style="display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;">
            <span style="width:62px;height:62px;background:linear-gradient(145deg,#0d1b2e,#152340,#1a2e50);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(0,0,0,0.5),inset 0 1px 0 rgba(255,255,255,0.08);flex-shrink:0;">
                <svg width="38" height="38" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                    <rect x="6" y="10" width="14" height="19" rx="2" fill="#4caf50" transform="rotate(-8 13 19)"/>
                    <rect x="10" y="9" width="14" height="19" rx="2" fill="#e53935" transform="rotate(-2 17 18)"/>
                    <rect x="14" y="8" width="14" height="20" rx="2" fill="#1e88e5"/>
                    <rect x="14" y="8" width="3" height="20" rx="1" fill="#1565c0"/>
                    <line x1="19" y1="13" x2="26" y2="13" stroke="rgba(255,255,255,0.55)" stroke-width="1.2" stroke-linecap="round"/>
                    <line x1="19" y1="17" x2="26" y2="17" stroke="rgba(255,255,255,0.55)" stroke-width="1.2" stroke-linecap="round"/>
                    <line x1="19" y1="21" x2="26" y2="21" stroke="rgba(255,255,255,0.55)" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
            </span>
            Online Book Store
        </h1>
        <p>Your digital library — discover, read, and explore books across every genre, all in one place.</p>
        <span class="hero-badge">✨ Free to Use · No Hidden Costs</span>
    </div>

    <!-- FEATURES -->
    <div class="text-center mb-2 fade-up fade-up-1">
        <h2 class="section-title">Why Choose Us?</h2>
        <p class="section-sub">Everything you need to enjoy reading, all in one place.</p>
    </div>
    <div class="row g-4 mb-5">
        <?php
        $features = [
            ['📖','Vast Collection','Browse hundreds of books across genres — novels, poetry, programming, self-help and more.'],
            ['🔍','Easy Search','Find your next favourite book instantly with our powerful search feature.'],
            ['⚡','Instant Access','Read books online or download them instantly — no waiting, no hassle.'],
            ['🆓','Free to Use','Create a free account and get access to our entire library with no hidden costs.'],
            ['🗂️','Organised by Category','Books are neatly organised by category and author so you can find what you love.'],
            ['⭐','Reviews & Ratings','Read and write reviews, rate books, and see what the community loves most.'],
        ];
        foreach ($features as $i => $f): ?>
        <div class="col-md-4 fade-up fade-up-<?= $i+1 ?>">
            <div class="feature-card">
                <div class="feature-icon"><?= $f[0] ?></div>
                <h5><?= $f[1] ?></h5>
                <p><?= $f[2] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- STATS -->
    <div class="text-center mb-4 fade-up">
        <h2 class="section-title">Our Library in Numbers</h2>
        <p class="section-sub">Growing every day.</p>
    </div>
    <div class="row g-4 mb-5">
        <div class="col-md-4 fade-up fade-up-1"><div class="stat-box"><h2><?= $allBooksCount ?>+</h2><p>Books Available</p></div></div>
        <div class="col-md-4 fade-up fade-up-2"><div class="stat-box"><h2><?= count($authors) ?>+</h2><p>Authors</p></div></div>
        <div class="col-md-4 fade-up fade-up-3"><div class="stat-box"><h2><?= count($categories) ?>+</h2><p>Categories</p></div></div>
    </div>

    <!-- MISSION + TIMELINE -->
    <div class="team-section fade-up">
        <div class="row align-items-start">
            <div class="col-md-6">
                <h2 class="section-title">Our Mission</h2>
                <p class="mission-text">At <strong>Online Book Store</strong>, we believe that knowledge and stories should be accessible to everyone. Our mission is to build a platform where readers of all ages and backgrounds can discover great books, connect with authors, and develop a lifelong love of reading.</p>
                <p class="mission-text">Whether you're a student looking for study material, a professional seeking to grow, or simply someone who loves a good story — we have something for you.</p>
                <div class="timeline mt-4">
                    <div class="timeline-item">
                        <div class="timeline-year">2025</div>
                        <h6>Platform Launched</h6>
                        <p>Started with a small collection of books and a big dream.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2025</div>
                        <h6>Reviews & Ratings Added</h6>
                        <p>Community features introduced — readers can now share their thoughts.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2026</div>
                        <h6>100+ Books Milestone</h6>
                        <p>Crossed 50 books and expanded to 10+ categories.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center mt-4 mt-md-0">
                <div class="mission-emoji">📚</div>
                <p class="mission-tagline">Read. Learn. Grow.</p>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="cta-section fade-up">
        <h2>Ready to Start Reading?</h2>
        <p>Join thousands of readers and explore our growing library — completely free.</p>
        <a href="register.php" class="btn-cta">Join for Free →</a>
    </div>

    <!-- FOOTER -->
    <div class="text-center py-4 site-footer">
        &copy; <?= date('Y') ?> Online Book Store. All rights reserved. &nbsp;·&nbsp;
        <a href="index.php" style="color:inherit;">Home</a> &nbsp;·&nbsp;
        <a href="about.php" style="color:inherit;">About</a> &nbsp;·&nbsp;
        <a href="login.php" style="color:inherit;">Login</a>
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            const loader = document.getElementById('page-loader');
            if (loader) { loader.classList.add('hidden'); setTimeout(() => loader.remove(), 400); }
        }, 900);
    });
    window.addEventListener('scroll', function () {
        const btn = document.getElementById('backToTop');
        if (btn) btn.style.display = window.scrollY > 300 ? 'flex' : 'none';
    });
</script>

<?php include "footer.php"; ?>