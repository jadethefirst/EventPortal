document.addEventListener('DOMContentLoaded', function () {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const hour = new Date().getHours();

    // If server hasn’t set theme override, apply JS logic
    const currentLink = document.querySelector('link[rel="stylesheet"]');
    const themeRegex = /\/css\/(spring|summer|autumn|winter)(-dark)?\.css/i;
    const match = currentLink?.href.match(themeRegex);

    if (match) {
        const season = match[1];
        const isDark = match[2] === '-dark';

        const shouldBeDark = prefersDark || hour >= 19 || hour < 6;

        // Update theme only if mismatch
        if (shouldBeDark && !isDark) {
            currentLink.href = `/css/${season}-dark.css`;
        } else if (!shouldBeDark && isDark) {
            currentLink.href = `/css/${season}.css`;
        }
    }
});
