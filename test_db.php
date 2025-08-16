<?php
// bootstrapper to test mysqli connection
<?php require __DIR__ . "/includes/db.php"; try { db()->query("SELECT 1"); echo "OK"; } catch (Exception $e) { echo $e->getMessage(); }
