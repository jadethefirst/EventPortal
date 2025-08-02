<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// includes/footer.php
// Common footer for all pages, included before </body>
?>

<footer>
  <div class="container footer-content" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
    <p>&copy; <?php echo date('Y'); ?> EventPortal. All rights reserved.</p>

    <!-- Theme selector dropdown for seasonal themes -->
    <label for="themeSelect" style="font-weight: 700; color: var(--color-text); cursor: pointer;">
      Theme:
      <select id="themeSelect" aria-label="Select site theme" style="margin-left: 0.5rem; padding: 0.3rem; border-radius: var(--border-radius); border: 1px solid var(--color-secondary); cursor: pointer;">
        <option value="spring">Spring</option>
        <option value="summer">Summer</option>
        <option value="autumn">Autumn</option>
        <option value="winter">Winter</option>
      </select>
    </label>
  </div>
</footer>


<!-- Optional: Global JavaScript files -->
<script src="/js/main.js"></script>

</body>
</html>
