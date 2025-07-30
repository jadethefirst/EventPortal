<?php
// includes/footer.php
// This file contains the common footer for all pages.
// It should be included at the end of the page body before </body> tag.

// You can add analytics scripts, site-wide footer links, or closing tags here.
?>

<footer>
    <div class="container footer-content">
        <p>&copy; <?php echo date('Y'); ?> EventPortal. All rights reserved.</p>

        <!-- Optional: Add quick links or social media icons -->
        <nav class="footer-nav">
            <a href="/about.php">About</a> |
            <a href="/contact.php">Contact</a> |
            <a href="/help/help_faq.php">FAQ</a>
        </nav>
    </div>
</footer>

<!-- Optional: Global JavaScript files -->
<script src="/js/main.js"></script>

</body>
</html>
