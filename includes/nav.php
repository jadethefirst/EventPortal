<?php
// Make sure session is started before using $theme & $darkMode
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'spring';
$darkMode = $_SESSION['dark_mode'] ?? false;
?>

<nav>
  <ul>
    <li><a href="/index.php">Home</a></li>
    <li><a href="/admin/dashboard.php">Admin Dashboard</a></li>
    <!-- Other links -->
  </ul>

  <form id="themeForm" method="POST" action="/theme-toggle.php" style="margin-top:1em;">
    <label for="themeSelect">Theme:</label>
    <select name="theme" id="themeSelect">
      <option value="spring" <?php if ($theme == 'spring') echo 'selected'; ?>>Spring</option>
      <option value="summer" <?php if ($theme == 'summer') echo 'selected'; ?>>Summer</option>
      <option value="autumn" <?php if ($theme == 'autumn') echo 'selected'; ?>>Autumn</option>
      <option value="winter" <?php if ($theme == 'winter') echo 'selected'; ?>>Winter</option>
    </select>

    <label style="margin-left: 15px;">
      <input type="checkbox" name="dark_mode" id="darkModeCheckbox" <?php if ($darkMode) echo 'checked'; ?>>
      Dark Mode
    </label>

    <button type="submit" style="margin-left: 15px;">Apply</button>
  </form>
</nav>

<script>
  // Bonus: Submit the form automatically on select/change, or let user click Apply button
  document.getElementById('themeSelect').addEventListener('change', () => {
    // Optionally auto-submit here:
    // document.getElementById('themeForm').submit();
  });
  document.getElementById('darkModeCheckbox').addEventListener('change', () => {
    // Optionally auto-submit here:
    // document.getElementById('themeForm').submit();
  });
</script>
