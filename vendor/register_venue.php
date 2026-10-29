<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('vendor');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $required = ['name', 'description', 'type', 'max_capacity', 'base_rate', 'city'];
    foreach ($required as $field)
        if (trim((string) ($_POST[$field] ?? '')) === '')
            $error = 'Please complete every required field.';
    $imagePath = null;
    if (!$error) {
        $image = $_FILES['cover_image'] ?? null;
        if (!is_array($image) || ($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Please choose a cover image to upload.';
        } elseif (($image['size'] ?? 0) > 5 * 1024 * 1024) {
            $error = 'The cover image must be 5 MB or smaller.';
        } else {
            $imageInfo = @getimagesize($image['tmp_name']);
            $mime = $imageInfo['mime'] ?? '';
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if ($imageInfo === false || !isset($extensions[$mime])) {
                $error = 'Cover image must be a valid JPG, PNG, or WebP image.';
            } else {
                $uploadDirectory = __DIR__ . '/../uploads/venues';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    $error = 'The image upload folder could not be created.';
                } else {
                    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                    if (!move_uploaded_file($image['tmp_name'], $uploadDirectory . '/' . $filename)) {
                        $error = 'The cover image could not be saved. Please try again.';
                    } else {
                        $imagePath = 'uploads/venues/' . $filename;
                    }
                }
            }
        }
    }
    if (!$error) {
        $stmt = db()->prepare('INSERT INTO venues (vendor_id,name,description,type,max_capacity,square_footage,base_rate,peak_rate,security_deposit,city,status,cover_image) VALUES (:vendor,:name,:description,:type,:capacity,:sqft,:base,:peak,:deposit,:city,"active",:image)');
        $stmt->execute(['vendor' => current_user()['id'], 'name' => trim($_POST['name']), 'description' => trim($_POST['description']), 'type' => trim($_POST['type']), 'capacity' => (int) $_POST['max_capacity'], 'sqft' => (int) ($_POST['square_footage'] ?? 0), 'base' => (float) $_POST['base_rate'], 'peak' => (float) ($_POST['peak_rate'] ?? $_POST['base_rate']), 'deposit' => (float) ($_POST['security_deposit'] ?? 0), 'city' => trim($_POST['city']), 'image' => $imagePath]);
        flash('success', 'Your banquet hall has been added to the GrandVistas selection.');
        redirect(url('vendor/dashboard.php'));
    }
}
$pageTitle = 'List your venue';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="form-card">
        <div class="eyebrow">Become a GrandVistas hall partner</div>
        <h1>Add your banquet hall</h1>
        <p class="muted">Tell families and organisers about your hall, facilities, and hospitality.</p>
        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf"
                value="<?= e(csrf_token()) ?>">
            <div class="form-grid">
                <div class="field"><label>Banquet hall name *</label><input name="name" required></div>
                <div class="field"><label>Hall type *</label><select name="type">
                        <option>Ballroom</option>
                        <option>Garden</option>
                        <option>Pavilion</option>
                        <option>Heritage</option>
                        <option>Estate</option>
                    </select></div>
                <div class="field full"><label>Description *</label><textarea name="description" rows="4"
                        required></textarea></div>
                <div class="field"><label>Maximum capacity *</label><input name="max_capacity" type="number" min="1"
                        required></div>
                <div class="field"><label>Square footage</label><input name="square_footage" type="number"></div>
                <div class="field"><label>Base rate (₹) *</label><input name="base_rate" type="number" min="0" required>
                </div>
                <div class="field"><label>Peak rate (₹)</label><input name="peak_rate" type="number" min="0"></div>
                <div class="field"><label>Security deposit (₹)</label><input name="security_deposit" type="number"
                        min="0"></div>
                <div class="field"><label>City *</label><input name="city" required></div>
                <div class="field full"><label for="cover_image">Cover image *</label><input id="cover_image"
                        name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" required><small
                        class="muted">JPG, PNG, or WebP up to 5 MB. The image is stored locally.</small></div>
            </div><button class="button primary" style="margin-top:1.5rem">Submit venue</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>