<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("connection.php");

// Restrict access to administrators only[cite: 9]
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all registered users from the database[cite: 9]
$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar-brand { font-weight: 700; font-size: 1.3rem; }

        .stats-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .stats-card .icon { font-size: 2.2rem; opacity: 0.85; }

        .table-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .table thead { background: #343a40; color: #fff; }
        .table thead th { font-weight: 600; border: none; }

        .table tbody tr:hover {
            background: #eef2ff;
            cursor: pointer;
        }

        .badge-admin { background: #6f42c1; }
        .badge-user { background: #0d6efd; }

        .welcome-strip {
            background: linear-gradient(135deg, #1a1a2e, #343a40);
            color: #fff;
            border-radius: 12px;
            padding: 20px 28px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-edit {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none;
            color: #fff;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-edit:hover { opacity: 0.85; color: #fff; }

        .modal-header-custom {
            background: linear-gradient(135deg, #343a40, #1a1a2e);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .detail-row { border-bottom: 1px solid #f0f0f0; padding: 10px 0; }
        .detail-row:last-child { border-bottom: none; }

        .detail-label {
            font-weight: 600;
            color: #555;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .avatar-sm {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #343a40, #6c757d);
            color: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 700;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <span class="navbar-brand"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-light"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user']); ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="welcome-strip mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">&#128081; Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h4>
                <small class="text-white-50">Here's an overview of all registered users.</small>
            </div>
            <div class="text-white-50 text-end small">
                <i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y, h:i A'); ?>
            </div>
        </div>

        <?php
        // Fetch stats[cite: 9]
        $total      = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
        $adminCount = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch_assoc()['c'];
        $userCount  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch_assoc()['c'];
        $today      = $conn->query("SELECT COUNT(*) AS c FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
        ?>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card p-3 d-flex flex-row align-items-center gap-3">
                    <div class="icon text-secondary"><i class="bi bi-people-fill"></i></div>
                    <div><div class="fs-3 fw-bold"><?php echo $total; ?></div><div class="text-muted small">Total Users</div></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card p-3 d-flex flex-row align-items-center gap-3">
                    <div class="icon" style="color:#6f42c1;"><i class="bi bi-shield-fill-check"></i></div>
                    <div><div class="fs-3 fw-bold"><?php echo $adminCount; ?></div><div class="text-muted small">Admins</div></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card p-3 d-flex flex-row align-items-center gap-3">
                    <div class="icon text-primary"><i class="bi bi-person-fill"></i></div>
                    <div><div class="fs-3 fw-bold"><?php echo $userCount; ?></div><div class="text-muted small">Regular Users</div></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card p-3 d-flex flex-row align-items-center gap-3">
                    <div class="icon text-success"><i class="bi bi-person-plus-fill"></i></div>
                    <div><div class="fs-3 fw-bold"><?php echo $today; ?></div><div class="text-muted small">Joined Today</div></div>
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table me-2 text-dark"></i>Registered Users</h5>
                <span class="badge bg-secondary"><?php echo $total; ?> records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th><i class="bi bi-person me-1"></i>Username</th>
                                <th><i class="bi bi-envelope me-1"></i>Email</th>
                                <th><i class="bi bi-tag me-1"></i>Role</th>
                                <th><i class="bi bi-clock me-1"></i>Registered At</th>
                                <th><i class="bi bi-gear me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0):
                                $i = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr onclick="showDetail(<?php echo htmlspecialchars(json_encode($row)); ?>)" title="Click to view details">
                                        <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                                    style="width:32px;height:32px;font-size:0.85rem;font-weight:600;">
                                                    <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($row['username']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?> text-white px-3 py-1">
                                                <?php echo ucfirst($row['role']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                        <td onclick="event.stopPropagation()">
                                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary btn-edit">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- User Detail Modal[cite: 9] -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;">
                <div class="modal-header modal-header-custom border-0 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm" id="modal-avatar">A</div>
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
                        <span class="text-muted" id="modal-id">—</span>
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
                    <a href="#" id="modal-edit-btn" class="btn btn-edit px-4"><i class="bi bi-pencil-square me-1"></i>Edit User</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetail(row) {
            document.getElementById('modal-avatar').textContent   = row.username.charAt(0).toUpperCase();
            document.getElementById('modal-username').textContent = row.username;
            document.getElementById('modal-role-sub').textContent = row.role.charAt(0).toUpperCase() + row.role.slice(1);
            document.getElementById('modal-id').textContent       = '#' + row.id;
            document.getElementById('modal-email').textContent    = row.email;
            document.getElementById('modal-created').textContent  = row.created_at;

            const badge = document.getElementById('modal-role-badge');
            badge.className = 'badge rounded-pill text-white px-3 py-1';
            badge.style.background = row.role === 'admin' ? '#6f42c1' : '#0d6efd';
            badge.textContent = row.role.charAt(0).toUpperCase() + row.role.slice(1);

            document.getElementById('modal-edit-btn').href = 'edit_user.php?id=' + row.id;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }
    </script>
</body>
</html>