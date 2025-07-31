<?php
// includes/footer.php
// Common footer for all pages, included before </body>
?>

<footer>
    <div class="container footer-content" style="display:flex; justify-content: space-between; align-items: center; padding: 1rem; flex-wrap: wrap; gap: 1rem;">

        <p>&copy; <?php echo date('Y'); ?> EventPortal. All rights reserved.</p>

        <nav class="footer-nav">
            <a href="/about.php">About</a> |
            <a href="/contact.php">Contact</a> |
            <a href="/help/help_faq.php">FAQ</a>
        </nav>

        <!-- Seasonal theme dropdown menu for changing theme -->
        <form id="themeForm" style="display:flex; align-items: center; gap: 0.5rem;">
            <label for="themeSelect" style="font-weight: 700; color: var(--color-text); cursor: pointer;">Theme:</label>
            <select id="themeSelect" name="theme" style="padding: 0.3rem 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--color-secondary); font-weight: 600; cursor: pointer;">
                <option value="spring">Spring</option>
                <option value="summer">Summer</option>
                <option value="autumn">Autumn</option>
                <option value="winter">Winter</option>
            </select>
        </form>
    </div>
</footer>

<!-- Optional: Global JavaScript files -->
<script src="/js/main.js"></script>

</body>
</html>
