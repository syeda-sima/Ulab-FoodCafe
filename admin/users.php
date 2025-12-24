<?php
require_once '../config.php';

if (!hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$message = '';
$message_type = '';

// Handle user role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    if ($stmt->execute()) {
        $message = 'User role updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Failed to update user role';
        $message_type = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $current_user = getCurrentUser();
    
    // Prevent admin from deleting themselves
    if ($user_id == $current_user['id']) {
        $message = 'You cannot delete your own account';
        $message_type = 'error';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = 'User deleted successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to delete user';
            $message_type = 'error';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Get all users
$conn = getDBConnection();
$users = $conn->query("SELECT id, name, email, role, phone, student_id, verified, created_at FROM users ORDER BY created_at DESC");
$conn->close();

$page_title = 'User Management';
include '../includes/header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">User Management</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Student ID</th>
                    <th>Role</th>
                    <th>Verified</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?></td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="form-control" style="display: inline-block; width: auto;" 
                                        onchange="this.form.submit()">
                                    <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="faculty" <?php echo $user['role'] === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="cafeteria_staff" <?php echo $user['role'] === 'cafeteria_staff' ? 'selected' : ''; ?>>Cafeteria Staff</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_role" value="1">
                            </form>
                        </td>
                        <td>
                            <?php if ($user['verified']): ?>
                                <span class="badge badge-success">Yes</span>
                            <?php else: ?>
                                <span class="badge badge-warning">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo ucfirst($user['role']); ?></span>
                            <?php 
                            $current_user = getCurrentUser();
                            if ($user['id'] != $current_user['id']): 
                            ?>
                                <form method="POST" style="display: inline-block; margin-left: 0.5rem;" 
                                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 2rem;">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

