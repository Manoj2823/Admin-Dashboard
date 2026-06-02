<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("connection.php");

// Ensure the user is logged in[cite: 18, 26]
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch ALL users from the DB for the main dashboard directory[cite: 18, 26]
$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #eef2ff; font-family: 'Segoe UI', sans-serif; }

        .table-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .table thead { background: #0d6efd; color: #fff; }
        .table thead th { font-weight: 600; border: none; }
        .table tbody tr {
            cursor: pointer;
            transition: background 0.15s;
        }
        .table tbody tr:hover { background: #dde8ff; }

        .badge-admin { background: #6f42c1; }
        .badge-user  { background: #198754; }

        /* Detail Modal Styling */
        .modal-header-custom {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }
        .detail-row {
            border-bottom: 1px solid #f0f0f0;
            padding: 11px 0;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            font-weight: 600;
            color: #555;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .avatar-lg {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.25);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4 shadow-sm">
    <span class="navbar-brand fw-bold"><i class="bi bi-grid-1x2-fill me-2"></i>Main Dashboard</span>
    <div class="d-flex align-items-center gap-3">
        <a href="profile.php" class="text-light text-decoration-none border rounded px-2 py-1">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user']); ?>
        </a>
        <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
</nav>

<div class="container py-5">
    <h4 class="mb-1 text-dark fw-bold">User Directory</h4>
    <p class="text-muted small mb-4"><i class="bi bi-hand-index me-1"></i>Click any row to view full details.</p>

    <div class="card table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined Date</th>
                            <th>Actions</th> <!-- Added Column[cite: 18] -->
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): ?>
                        <tr onclick="showDetail(<?php echo htmlspecialchars(json_encode($row)); ?>)" title="Click to view details">
                            <td class="ps-4 text-muted">#<?php echo $row['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                        style="width:32px;height:32px;font-size:0.85rem;font-weight:600;">
                                        <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge rounded-pill <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?> px-3 py-1">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            
                            <!-- Action cell with Stop Propagation to prevent modal trigger[cite: 18] -->
                            <td onclick="event.stopPropagation()">
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No data found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Detail Modal[cite: 18, 26] -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
            <div class="modal-header modal-header-custom border-0 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg" id="modal-avatar">A</div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modal-username">—</h5>
                        <small class="opacity-75" id="modal-role-sub">—</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="detail-row d-flex justify-content-between align-items-center">
                    <span class="detail-label"><i class="bi bi-hash me-1"></i>User ID</span>
                    <span class="text-muted fw-semibold" id="modal-id">—</span>
                </div>
                <div class="detail-row d-flex justify-content-between align-items-center">
                    <span class="detail-label"><i class="bi bi-person me-1"></i>Username</span>
                    <span class="fw-semibold" id="modal-uname">—</span>
                </div>
                <div class="detail-row d-flex justify-content-between align-items-center">
                    <span class="detail-label"><i class="bi bi-envelope me-1"></i>Email</span>
                    <span id="modal-email">—</span>
                </div>
                <div class="detail-row d-flex justify-content-between align-items-center">
                    <span class="detail-label"><i class="bi bi-shield me-1"></i>Role</span>
                    <span id="modal-role-badge">—</span>
                </div>
                <div class="detail-row d-flex justify-content-between align-items-center">
                    <span class="detail-label"><i class="bi bi-calendar3 me-1"></i>Joined</span>
                    <span class="text-muted small" id="modal-created">—</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showDetail(row) {
        document.getElementById('modal-avatar').textContent  = row.username.charAt(0).toUpperCase();
        document.getElementById('modal-username').textContent = row.username;
        document.getElementById('modal-role-sub').textContent = row.role.charAt(0).toUpperCase() + row.role.slice(1);
        document.getElementById('modal-id').textContent      = '#' + row.id;
        document.getElementById('modal-uname').textContent   = row.username;
        document.getElementById('modal-email').textContent   = row.email;
        document.getElementById('modal-created').textContent = row.created_at;

        const badge = document.getElementById('modal-role-badge');
        badge.className = 'badge rounded-pill text-white px-3 py-1';
        badge.style.background = row.role === 'admin' ? '#6f42c1' : '#198754';
        badge.textContent = row.role.charAt(0).toUpperCase() + row.role.slice(1);

        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }
</script>
</body>
</html>