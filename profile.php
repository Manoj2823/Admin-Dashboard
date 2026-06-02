<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("connection.php");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's full profile from DB
$stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #eef2ff; font-family: 'Segoe UI', sans-serif; }
        .profile-avatar {
            width: 80px; height: 80px; font-size: 2rem; font-weight: 700;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .info-card { border-radius: 14px; border: none; box-shadow: 0 2px 14px rgba(0,0,0,0.09); }
        .info-row { border-bottom: 1px solid #f0f0f0; padding: 12px 0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: 600; color: #555; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.4px; }
        .info-value { color: #222; font-size: 0.97rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4 shadow-sm">
    <span class="navbar-brand fw-bold"><i class="bi bi-person-badge me-2"></i>My Profile</span>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin.php' : 'user.php'; ?>" class="btn btn-light btn-sm text-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
        <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
</nav>

<div class="container py-5" style="max-width:560px;">
    <div class="card info-card p-4 text-center mb-4">
        <div class="d-flex justify-content-center mb-3">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($profile['username'] ?? 'U', 0, 1)); ?>
            </div>
        </div>
        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($profile['username']); ?></h4>
        <span class="badge bg-primary mt-1"><?php echo ucfirst($profile['role']); ?></span>
        <p class="text-muted small mt-1 mb-0">Member since <?php echo date('d M Y', strtotime($profile['created_at'])); ?></p>
    </div>

    <div class="card info-card px-4 py-2">
        <div class="info-row d-flex justify-content-between align-items-center">
            <span class="info-label"><i class="bi bi-hash me-1"></i>User ID</span>
            <span class="info-value text-muted">#<?php echo $profile['id']; ?></span>
        </div>
        <div class="info-row d-flex justify-content-between align-items-center">
            <span class="info-label"><i class="bi bi-person me-1"></i>Username</span>
            <span class="info-value"><?php echo htmlspecialchars($profile['username']); ?></span>
        </div>
        <div class="info-row d-flex justify-content-between align-items-center">
            <span class="info-label"><i class="bi bi-envelope me-1"></i>Email</span>
            <span class="info-value"><?php echo htmlspecialchars($profile['email']); ?></span>
        </div>
        <div class="info-row d-flex justify-content-between align-items-center">
            <span class="info-label"><i class="bi bi-shield me-1"></i>Role</span>
            <span class="badge bg-primary"><?php echo ucfirst($profile['role']); ?></span>
        </div>
        <div class="info-row d-flex justify-content-between align-items-center">
            <span class="info-label"><i class="bi bi-calendar3 me-1"></i>Joined</span>
            <span class="info-value text-muted"><?php echo date('d M Y, h:i A', strtotime($profile['created_at'])); ?></span>
        </div>
    </div>
</div>
</body>
</html>