<?php
require_once 'config.php';
require_once 'functions.php';

// Check if admin is logged in
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
  $tokenToDelete = $_GET['delete'];
  if (deleteLink($tokenToDelete)) {
    $successMessage = 'Link deleted successfully!';
  } else {
    $errorMessage = 'Failed to delete link.';
  }
}

// Get filter and search parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get filtered links
$links = getFilteredLinks($filter, $search);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Links - Audio Guide System</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .filters {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 8px 16px;
      border-radius: 20px;
      border: 2px solid #ddd;
      background: white;
      cursor: pointer;
      text-decoration: none;
      color: #333;
      font-weight: 600;
      transition: all 0.3s;
    }

    .filter-btn:hover {
      border-color: #667eea;
      color: #667eea;
    }

    .filter-btn.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-color: transparent;
    }

    .search-box {
      flex: 1;
      min-width: 250px;
    }

    .search-box input {
      width: 100%;
      padding: 10px 15px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    .search-box input:focus {
      outline: none;
      border-color: #667eea;
    }

    .actions {
      display: flex;
      gap: 8px;
    }

    .alert {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .link-url {
      font-family: monospace;
      font-size: 0.85rem;
      color: #0066cc;
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
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
        <a href="dashboard.php" class="nav-item">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="manage-links.php" class="nav-item active">
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
        <h1>Manage Links</h1>
        <p>View, search, and manage all audio guide links</p>
      </header>

      <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
      <?php endif; ?>

      <?php if (isset($errorMessage)): ?>
        <div class="alert alert-error"><?php echo $errorMessage; ?></div>
      <?php endif; ?>

      <div class="section">
        <!-- Filters -->
        <form method="GET" class="filters">
          <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
            All Links
          </a>
          <a href="?filter=active" class="filter-btn <?php echo $filter === 'active' ? 'active' : ''; ?>">
            Active
          </a>
          <a href="?filter=expired" class="filter-btn <?php echo $filter === 'expired' ? 'active' : ''; ?>">
            Expired
          </a>

          <div class="search-box">
            <input type="text" name="search"
              placeholder="Search by email, destination, or token..."
              value="<?php echo htmlspecialchars($search); ?>">
          </div>

          <button type="submit" class="btn btn-primary btn-sm">Search</button>

        </form>

        <!-- Results Count -->
        <p style="margin: 20px 0; color: #666;">
          Found <strong><?php echo count($links); ?></strong> link(s)
        </p>

        <!-- Links Table -->
        <?php if (count($links) > 0): ?>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Customer Email</th>
                  <th>Destination</th>
                  <th>Audio File</th>
                  <th>Created</th>
                  <th>Expires</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($links as $link): ?>
                  <?php
                  $statusClass = $link['is_active'] ? 'status-active' : 'status-expired';
                  $statusText = $link['is_active'] ? 'Active' : 'Expired';
                  $linkUrl = SITE_URL . 'index.php?token=' . $link['token'];
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($link['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($link['destination']); ?></td>
                    <td><?php echo htmlspecialchars($link['audio_file']); ?></td>
                    <td><?php echo date('M j, g:i A', $link['created_at']); ?></td>
                    <td><?php echo date('M j, g:i A', $link['expires_at']); ?></td>
                    <td>
                      <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                      </span>
                    </td>
                    <td>
                      <div class="actions">
                        <button class="btn btn-success btn-sm"
                          onclick="copyLink('<?php echo $linkUrl; ?>')">
                          📋 Copy
                        </button>
                        <a href="?delete=<?php echo urlencode($link['token']); ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>"
                          class="btn btn-danger btn-sm"
                          onclick="return confirm('Are you sure you want to delete this link?')">
                          🗑️ Delete
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <p>No links found matching your criteria.</p>
            <?php if (!empty($search)): ?>
              <p><a href="?filter=<?php echo $filter; ?>">Clear search</a></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    function copyLink(url) {
      navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
      }).catch(err => {
        console.error('Copy failed:', err);
        prompt('Copy this link:', url);
      });
    }
  </script>
</body>

</html>