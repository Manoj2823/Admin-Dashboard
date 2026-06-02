<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("connection.php");

// Admin only
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Accept id from POST (form submit) or GET (initial load)
$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: admin.php");
    exit();
}

$errors  = [];
$success = '';

// Load user
$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: admin.php");
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $role     = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    // --- Validation (all checks up-front, before any DB work) ---
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!in_array($role, ['user', 'admin'])) {
        $errors[] = "Invalid role selected.";
    }

    // Validate password length only when a new one is provided
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    }

    // Check email uniqueness (exclude current user)
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->bind_param("si", $email, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = "This email is already used by another account.";
        }
    }

    // --- Execute update only when there are zero errors ---
    if (empty($errors)) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?");
            $upd->bind_param("ssssi", $username, $email, $role, $hashed, $id);
        } else {
            $upd = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $upd->bind_param("sssi", $username, $email, $role, $id);
        }

        if ($upd->execute()) {
            $success = "User updated successfully!";
            // Reload fresh data to reflect changes in the form header & fields
            $stmt2 = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $user = $stmt2->get_result()->fetch_assoc();
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        .edit-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            overflow: hidden;
        }

        .edit-card .card-header {
            background: linear-gradient(135deg, #1a1a2e, #343a40);
            padding: 24px 28px;
            border: none;
        }

        .avatar-lg {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.15);
            color: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700;
        }

        .form-label { font-weight: 600; font-size: 0.875rem; color: #444; }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dde2ef;
            padding: 10px 14px;
            font-size: 0.93rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
        }

        .input-group-text {
            background: #f8f9ff;
            border-color: #dde2ef;
            color: #888;
            border-radius: 8px 0 0 8px;
        }

        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .input-group .form-select  { border-radius: 0 8px 8px 0; }

        .btn-save {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none; border-radius: 8px;
            padding: 10px 28px; font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn-save:hover { opacity: 0.88; }

        .section-divider {
            border-top: 1px dashed #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
    <span class="navbar-brand fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</span>
    <div class="d-flex align-items-center gap-3">
        <a href="admin.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
        <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
</nav>

<div class="container py-5" style="max-width: 560px;">

    <div class="card edit-card">
        <!-- Header -->
        <div class="card-header d-flex align-items-center gap-3">
            <div class="avatar-lg"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
            <div>
                <h5 class="mb-0 fw-bold text-white"><?php echo htmlspecialchars($user['username']); ?></h5>
                <small class="text-white-50">User ID #<?php echo $user['id']; ?> &bull; Edit Profile</small>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body p-4">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger d-flex gap-2 py-2 mb-4" style="border-radius:8px;font-size:0.88rem;">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-4" style="border-radius:8px;font-size:0.88rem;">
                    <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_user.php">
                <!-- Hidden id — ensures $id is always available on POST regardless of URL -->
                <input type="hidden" name="id" value="<?php echo intval($user['id']); ?>">

                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control"
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>

                <!-- Role -->
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield"></i></span>
                        <select name="role" class="form-select">
                            <option value="user"  <?php echo $user['role'] === 'user'  ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- New Password (optional) -->
                <div class="mb-1">
                    <label class="form-label">
                        New Password
                        <span class="text-muted fw-normal">(optional — leave blank to keep current)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passInput" class="form-control"
                               placeholder="Enter new password">
                        <button type="button" class="btn btn-outline-secondary"
                                style="border-radius:0 8px 8px 0;border-color:#dde2ef;"
                                onclick="togglePass()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted">Minimum 8 characters if changing.</div>
                </div>

                <div class="d-grid mt-4">
                    <button name="update" type="submit" class="btn btn-save btn-primary text-white">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePass() {
        const input = document.getElementById('passInput');
        const icon  = document.getElementById('eyeIcon');
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }
</script>
</body>
</html>