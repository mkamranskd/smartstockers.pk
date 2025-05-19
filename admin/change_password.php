<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
$errors = [];
$success = "";

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['userId'];

if ($_POST) {
    $current_password = $_POST['password_current'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['password_confirmation'];

    // Fetch current password from the database
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    // Check if current password (MD5) matches
    if (md5($current_password) !== $db_password) {
        $errors[] = "Current password is incorrect.";
    }

    // Check if new password matches confirmation
    if ($new_password !== $confirm_password) {
        $errors[] = "New password and confirm password do not match.";
    }

    // Validate new password strength (optional)
    if (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }

    // If no errors, update the password (MD5)
    if (empty($errors)) {
        $new_md5_password = md5($new_password);
        $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $update_stmt = $connect->prepare($update_sql);
        $update_stmt->bind_param("si", $new_md5_password, $user_id);

        if ($update_stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $errors[] = "Error updating password.";
        }
        $update_stmt->close();
    }
}
?>

<!-- Clients Table Column -->
<div class="col-lg-12">
    <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">
        <div class="table-container w-100 p-2" style="zoom:0.9;">
            <nav aria-label="breadcrumb ">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.php">&nbsp;&nbsp;&nbsp;&nbsp;<i class="bi bi-house"></i></a>
                    </li>


                    <li class="breadcrumb-item active" aria-current="page">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">

                                My Account

                            </div>
                        </div>

                        <!-- Table -->
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">

                                Change Password

                            </div>
                        </div>

                        <!-- Table -->
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="table-container p-4">
                <h5 class="mb-4">Change Password</h5>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password_current"
                            placeholder="Current Password" required>
                        <label for="password_current">Current Password</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" placeholder="New Password" required>
                        <label for="password">New Password</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password_confirmation"
                            placeholder="Confirm Password" required>
                        <label for="password_confirmation">Confirm Password</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>