<?php
include('../includes/auth_guard.php');
require_role(array('admin','hod'));
include('../config/db_connect.php');
// include('../includes/header.php');

$res = mysqli_query($conn, "SELECT al.*, u.username FROM activity_log al LEFT JOIN users u ON al.user_id = u.user_id ORDER BY al.action_time DESC LIMIT 200");
?>

<!-- Bootstrap 5.3.3 CSS (via jsDelivr CDN) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<!-- Bootstrap 5.3.3 JavaScript Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Optional: Bootstrap Icons (latest 1.11.3) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <?php include '../includes/navbar.php' ?>

<section class="container py-4">
  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center">
      <i class="bi bi-journal-text fs-3 me-2 text-muted"></i>
      <h2 class="h4 fw-semibold mb-0 text-uppercase letter-spacing-1">Activity Log</h2>
    </div>

    <!-- Filter controls -->
    <div class="d-flex gap-2">
      <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Search user or action...">
      <select id="filterAction" class="form-select form-select-sm">
        <option value="">All actions</option>
        <option value="upload">Upload</option>
        <option value="replace">Replace</option>
        <option value="download">Download</option>
      </select>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table align-middle table-borderless" id="activityTable">
      <thead class="border-bottom">
        <tr class="text-uppercase small text-muted">
          <th scope="col" data-sort="user" class="sortable">User</th>
          <th scope="col" data-sort="action" class="sortable">Action</th>
          <th scope="col">Upload ID</th>
          <th scope="col">IP</th>
          <th scope="col" data-sort="time" class="sortable">Time</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
          <tr>
            <td class="fw-medium"><?php echo htmlspecialchars($row['username']); ?></td>
            <td>
              <span class="text-capitalize fw-semibold
                <?php
                  switch (strtolower($row['action_type'])) {
                    case 'upload':   echo 'text-success'; break;
                    case 'replace':  echo 'text-warning'; break;
                    case 'download': echo 'text-primary'; break;
                    default:         echo 'text-secondary';
                  }
                ?>">
                <?php echo htmlspecialchars($row['action_type']); ?>
              </span>
            </td>
            <td class="text-muted"><?php echo htmlspecialchars($row['upload_id']) ?? 0; ?></td>
            <td><code class="text-secondary small"><?php echo htmlspecialchars($row['ip_address']); ?></code></td>
            <td class="text-nowrap text-muted"><?php echo htmlspecialchars(date('D, d M Y', strtotime($row['action_time']))); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Sort & Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const table = document.querySelector('#activityTable tbody');
  const rows = Array.from(table.querySelectorAll('tr'));
  const searchInput = document.querySelector('#searchInput');
  const filterAction = document.querySelector('#filterAction');
  const headers = document.querySelectorAll('.sortable');
  let sortOrder = 1;

  // Search + Filter Function
  function filterTable() {
    const search = searchInput.value.toLowerCase();
    const filter = filterAction.value.toLowerCase();

    rows.forEach(row => {
      const user = row.cells[0].textContent.toLowerCase();
      const action = row.cells[1].textContent.toLowerCase();
      const match = 
        (user.includes(search) || action.includes(search)) &&
        (!filter || action.includes(filter));
      row.style.display = match ? '' : 'none';
    });
  }

  // Sort Function
  headers.forEach(header => {
    header.style.cursor = 'pointer';
    header.addEventListener('click', () => {
      const index = Array.from(header.parentNode.children).indexOf(header);
      sortOrder *= -1;
      const sorted = [...rows].sort((a, b) => {
        const A = a.cells[index].textContent.trim().toLowerCase();
        const B = b.cells[index].textContent.trim().toLowerCase();
        return A.localeCompare(B) * sortOrder;
      });
      table.innerHTML = '';
      sorted.forEach(row => table.appendChild(row));
      headers.forEach(h => h.classList.remove('text-decoration-underline'));
      header.classList.add('text-decoration-underline');
    });
  });

  searchInput.addEventListener('input', filterTable);
  filterAction.addEventListener('change', filterTable);
});
</script>

<?php include('../includes/footer.php'); ?>

<!-- Bootstrap 5 JS (optional, for interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>