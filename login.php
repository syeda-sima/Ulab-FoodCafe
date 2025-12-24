<?php
require_once 'config.php';

// Redirect if already logged in (based on role)
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') {
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
    } elseif ($user['role'] === 'cafeteria_staff') {
        header('Location: ' . SITE_URL . '/staff/dashboard.php');
    } else {
        header('Location: ' . SITE_URL . '/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['verified'] == 0) {
                $error = 'Please verify your email address first';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ' . SITE_URL . '/admin/dashboard.php');
                } elseif ($user['role'] === 'cafeteria_staff') {
                    header('Location: ' . SITE_URL . '/staff/dashboard.php');
                } else {
                    header('Location: ' . SITE_URL . '/index.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="container">
    <div class="card" style="max-width: 500px; margin: 2rem auto;">
        <div class="card-header">
            <h2 class="card-title">Login</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

