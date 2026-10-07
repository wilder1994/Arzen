<script>
    (function () {
        try {
            var theme = localStorage.getItem('arzen-theme');
            document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
        } catch (e) {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    })();
</script>
<meta name="theme-color" content="#e7eeec">
