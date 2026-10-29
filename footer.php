<!-- ========== FOOTER ========== -->
    <div class="page-footer">
        &copy; <?= date('Y') ?> Online Book Store. All rights reserved.
    </div>

</div><!-- end .container -->

<!-- ========== DARK MODE JAVASCRIPT ========== -->
<script>
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        const icon = document.getElementById('toggleIcon');
        const label = document.getElementById('toggleLabel');
        if (theme === 'dark') {
            icon.textContent = '☀️';
            label.textContent = 'Light Mode';
        } else {
            icon.textContent = '🌙';
            label.textContent = 'Dark Mode';
        }
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);
    });
</script>

</body>
</html>