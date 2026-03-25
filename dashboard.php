<?php
require_once 'config.php';
require_once 'functions.php';

// Check if admin is logged in
requireAdmin();

// Get statistics
$stats = getDashboardStats() ?? [];

$stats = array_merge([
  'total'   => 0,
  'active'  => 0,
  'expired' => 0,
  'recent'  => []
], $stats);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Audio Guide System</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <h2>🎧 Audio Guide</h2>
        <p>Admin Panel</p>
      </div>

      <nav class="nav">
        <a href="dashboard.php" class="nav-item active">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="manage-links.php" class="nav-item">
          <span class="icon">🔗</span>
          <span>Manage Links</span>
        </a>
        <a href="generate-link.php" class="nav-item">
          <span class="icon">➕</span>
          <span>Create New Link</span>
        </a>
        <a href="logout.php" class="nav-item logout">
          <span class="icon">🚪</span>
          <span>Logout</span>
        </a>
      </nav>

      <div class="user-info">
        <p>Logged in as:</p>
        <p><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="page-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back! Here's your audio guide system summary.</p>
      </header>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">📝</div>
          <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Links</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div class="stat-info">
            <h3><?php echo $stats['active']; ?></h3>
            <p>Active Links</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon red">⏰</div>
          <div class="stat-info">
            <h3><?php echo $stats['expired']; ?></h3>
            <p>Expired Links</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon purple">⚙️</div>
          <div class="stat-info">
            <h3><?php echo LINK_EXPIRY_HOURS; ?>h</h3>
            <p>Link Duration</p>
          </div>
        </div>
      </div>

      <!-- Recent Links -->
      <div class="section">
        <div class="section-header">
          <h2>Recent Links</h2>
          <a href="manage-links.php" class="btn btn-primary">View All</a>
        </div>

        <?php if (count($stats['recent']) > 0): ?>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Destination</th>
                  <th>Created</th>
                  <th>Expires</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($stats['recent'] as $link): ?>
                  <?php
                  $isActive = time() <= $link['expires_at'];
                  $statusClass = $isActive ? 'status-active' : 'status-expired';
                  $statusText = $isActive ? 'Active' : 'Expired';
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($link['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($link['destination']); ?></td>
                    <td><?php echo date('M j, g:i A', $link['created_at']); ?></td>
                    <td><?php echo date('M j, g:i A', $link['expires_at']); ?></td>
                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <p>No links created yet. <a href="generate-link.php">Create your first link</a></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Quick Actions -->
      <div class="section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
          <a href="generate-link.php" class="action-card">
            <div class="action-icon">➕</div>
            <h3>Create Link</h3>
            <p>Generate a new audio guide link</p>
          </a>

          <a href="manage-links.php" class="action-card">
            <div class="action-icon">🔗</div>
            <h3>Manage Links</h3>
            <p>View and manage all links</p>
          </a>

          <a href="manage-links.php?filter=active" class="action-card">
            <div class="action-icon">✅</div>
            <h3>Active Links</h3>
            <p>View currently active links</p>
          </a>
        </div>
      </div>
    </main>
  </div>
</body>

</html>