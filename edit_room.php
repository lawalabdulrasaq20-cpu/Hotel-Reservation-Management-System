<?php
/**
 * Hotel Reservation System - Edit Room
 * 
 * This page allows administrators to edit existing rooms,
 * including updating images, details, and status.
 * 
 * PHP version 7.4+
 * 
 * @category Hotel_Reservation
 * @package  Admin
 * @author   Hotel Reservation System
 * @license  MIT License
 */

// Start session and check authentication
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Check if admin is logged in
requireAdminLogin();

// Get room ID from URL
$roomId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($roomId <= 0) {
    $_SESSION['flash_message'] = 'Invalid room ID.';
    $_SESSION['flash_message_type'] = 'error';
    header("Location: rooms.php");
    exit();
}

// Initialize variables
$errors = [];
$success = false;
$room = null;

// Fetch room details
try {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
    $stmt->execute();
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        $_SESSION['flash_message'] = 'Room not found.';
        $_SESSION['flash_message_type'] = 'error';
        header("Location: rooms.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Edit room error: " . $e->getMessage());
    $_SESSION['flash_message'] = 'Database error occurred.';
    $_SESSION['flash_message_type'] = 'error';
    header("Location: rooms.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
    // Sanitize input
    $formData = [
        'room_number' => isset($_POST['room_number']) ? sanitizeInput($_POST['room_number']) : $room['room_number'],
        'type' => isset($_POST['type']) ? sanitizeInput($_POST['type']) : $room['type'],
        'price' => isset($_POST['price']) ? (float)$_POST['price'] : $room['price'],
        'max_guests' => isset($_POST['max_guests']) ? (int)$_POST['max_guests'] : $room['max_guests'],
        'description' => isset($_POST['description']) ? sanitizeInput($_POST['description']) : $room['description'],
        'status' => isset($_POST['status']) ? sanitizeInput($_POST['status']) : $room['status']
    ];
    
    // Validation
    if (empty($formData['room_number'])) {
        $errors['room_number'] = 'Room number is required';
    } elseif (strlen($formData['room_number']) > 10) {
        $errors['room_number'] = 'Room number must be 10 characters or less';
    } elseif ($formData['room_number'] !== $room['room_number']) {
        // Check if room number already exists (if changed)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_number = :room_number AND id != :id");
        $stmt->bindParam(':room_number', $formData['room_number']);
        $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            $errors['room_number'] = 'Room number already exists';
        }
    }
    
    if (empty($formData['type'])) {
        $errors['type'] = 'Room type is required';
    }
    
    if ($formData['price'] <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }
    
    if ($formData['max_guests'] < 1 || $formData['max_guests'] > 10) {
        $errors['max_guests'] = 'Maximum guests must be between 1 and 10';
    }
    
    if (empty($formData['description'])) {
        $errors['description'] = 'Description is required';
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = 'Description must be at least 10 characters';
    }
    
    // Handle image upload (optional)
    $imagePath = $room['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/rooms/';
        $uploadUrl = '../assets/images/rooms/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = $_FILES['image']['name'];
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fileType, $allowedTypes)) {
            $errors['image'] = 'Only JPG, JPEG, PNG, GIF, and WebP files are allowed';
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $errors['image'] = 'Image size must be less than 5MB';
        } else {
            // Delete old image if exists
            if ($room['image'] && file_exists($uploadDir . $room['image'])) {
                unlink($uploadDir . $room['image']);
            }
            
            // Generate unique filename
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = 'room_' . time() . '_' . uniqid() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpName, $destination)) {
                $imagePath = $newFileName;
            } else {
                $errors['image'] = 'Failed to upload image';
            }
        }
    }
    
    // If no errors, update room in database
    if (empty($errors)) {
        try {
            $sql = "UPDATE rooms 
                    SET room_number = :room_number, 
                        type = :type, 
                        price = :price, 
                        max_guests = :max_guests, 
                        description = :description, 
                        status = :status";
            
            // Only update image if new one was uploaded
            if (isset($imagePath)) {
                $sql .= ", image = :image";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':room_number', $formData['room_number']);
            $stmt->bindParam(':type', $formData['type']);
            $stmt->bindParam(':price', $formData['price']);
            $stmt->bindParam(':max_guests', $formData['max_guests'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $formData['description']);
            $stmt->bindParam(':status', $formData['status']);
            
            if (isset($imagePath)) {
                $stmt->bindParam(':image', $imagePath);
            }
            
            $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Set flash message for redirect
                $_SESSION['flash_message'] = 'Room updated successfully!';
                $_SESSION['flash_message_type'] = 'success';
                
                // Update room array for display
                $room = array_merge($room, $formData);
                if (isset($imagePath)) {
                    $room['image'] = $imagePath;
                }
                
                $success = true;
            } else {
                $errors['database'] = 'Failed to update room. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Update room error: " . $e->getMessage());
            $errors['database'] = 'Database error occurred. Please try again.';
        }
    }
}

// Get admin details
$admin = getAdminDetails($pdo, $_SESSION['admin_id']);
?>

<!-- Include admin header -->
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Admin Wrapper -->
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <nav class="admin-sidebar">
        <div class="admin-sidebar-header">
            <div class="admin-logo">
                <i class="fas fa-hotel"></i>
                <span>Admin Panel</span>
            </div>
        </div>
        
        <ul class="admin-nav">
            <li>
                <a href="dashboard.php" class="admin-nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="bookings.php" class="admin-nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Reservations</span>
                </a>
            </li>
            <li>
                <a href="rooms.php" class="admin-nav-item active">
                    <i class="fas fa-bed"></i>
                    <span>Rooms</span>
                </a>
            </li>
            <li>
                <a href="add_room.php" class="admin-nav-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Room</span>
                </a>
            </li>
            <li>
                <a href="../index.php" class="admin-nav-item">
                    <i class="fas fa-home"></i>
                    <span>View Website</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="admin-nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Admin Main Content -->
    <main class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <h1>Edit Room</h1>
                <p>Update room details for Room #<?php echo htmlspecialchars($room['room_number']); ?></p>
            </div>
            <div class="header-right">
                <a href="rooms.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Rooms
                </a>
            </div>
        </header>
        
        <!-- Room Form -->
        <div class="room-form-card" data-aos="fade-up" data-aos-delay="200">
            <?php if (isset($errors['database'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errors['database']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Room updated successfully!
                </div>
            <?php endif; ?>
            
            <form action="edit_room.php?id=<?php echo $roomId; ?>" method="POST" class="room-form" enctype="multipart/form-data" id="room-form">
                <div class="form-row">
                    <!-- Room Number -->
                    <div class="form-group">
                        <label for="room_number" class="form-label">
                            <i class="fas fa-hashtag"></i> Room Number *
                        </label>
                        <input type="text" 
                               id="room_number" 
                               name="room_number" 
                               class="form-control <?php echo isset($errors['room_number']) ? 'error' : ''; ?>"
                               placeholder="e.g., 101, A1, Suite-1"
                               value="<?php echo htmlspecialchars($room['room_number']); ?>"
                               required
                               maxlength="10">
                        <?php if (isset($errors['room_number'])): ?>
                            <span class="error-message"><?php echo $errors['room_number']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Room Type -->
                    <div class="form-group">
                        <label for="type" class="form-label">
                            <i class="fas fa-bed"></i> Room Type *
                        </label>
                        <select id="type" name="type" class="form-control <?php echo isset($errors['type']) ? 'error' : ''; ?>" required>
                            <option value="Standard Single" <?php echo $room['type'] === 'Standard Single' ? 'selected' : ''; ?>>
                                Standard Single
                            </option>
                            <option value="Standard Double" <?php echo $room['type'] === 'Standard Double' ? 'selected' : ''; ?>>
                                Standard Double
                            </option>
                            <option value="Deluxe King" <?php echo $room['type'] === 'Deluxe King' ? 'selected' : ''; ?>>
                                Deluxe King
                            </option>
                            <option value="Suite" <?php echo $room['type'] === 'Suite' ? 'selected' : ''; ?>>
                                Suite
                            </option>
                            <option value="Family Room" <?php echo $room['type'] === 'Family Room' ? 'selected' : ''; ?>>
                                Family Room
                            </option>
                            <option value="Penthouse" <?php echo $room['type'] === 'Penthouse' ? 'selected' : ''; ?>>
                                Penthouse
                            </option>
                        </select>
                        <?php if (isset($errors['type'])): ?>
                            <span class="error-message"><?php echo $errors['type']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <!-- Price -->
                    <div class="form-group">
                        <label for="price" class="form-label">
                            <i class="fas fa-dollar-sign"></i> Price per Night *
                        </label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               class="form-control <?php echo isset($errors['price']) ? 'error' : ''; ?>"
                               placeholder="0.00"
                               value="<?php echo $room['price']; ?>"
                               required
                               min="0.01"
                               step="0.01">
                        <?php if (isset($errors['price'])): ?>
                            <span class="error-message"><?php echo $errors['price']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Max Guests -->
                    <div class="form-group">
                        <label for="max_guests" class="form-label">
                            <i class="fas fa-users"></i> Maximum Guests *
                        </label>
                        <select id="max_guests" name="max_guests" class="form-control <?php echo isset($errors['max_guests']) ? 'error' : ''; ?>" required>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $room['max_guests'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php if (isset($errors['max_guests'])): ?>
                            <span class="error-message"><?php echo $errors['max_guests']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle"></i> Status *
                    </label>
                    <select id="status" name="status" class="form-control <?php echo isset($errors['status']) ? 'error' : ''; ?>" required>
                        <option value="available" <?php echo $room['status'] === 'available' ? 'selected' : ''; ?>>
                            Available
                        </option>
                        <option value="occupied" <?php echo $room['status'] === 'occupied' ? 'selected' : ''; ?>>
                            Occupied
                        </option>
                        <option value="maintenance" <?php echo $room['status'] === 'maintenance' ? 'selected' : ''; ?>>
                            Maintenance
                        </option>
                    </select>
                </div>
                
                <!-- Description -->
                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Description *
                    </label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control <?php echo isset($errors['description']) ? 'error' : ''; ?>"
                              rows="4"
                              placeholder="Describe the room features, amenities, and any special details..."
                              required><?php echo htmlspecialchars($room['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="error-message"><?php echo $errors['description']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Current Image -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-image"></i> Current Image
                    </label>
                    <div class="current-image">
                        <img src="../assets/images/rooms/<?php echo htmlspecialchars($room['image'] ?? 'room-default.jpg'); ?>" 
                             alt="Current room image"
                             style="max-width: 200px; max-height: 150px; border-radius: var(--radius-md);">
                    </div>
                </div>
                
                <!-- New Image (Optional) -->
                <div class="form-group">
                    <label for="image" class="form-label">
                        <i class="fas fa-upload"></i> Upload New Image (Optional)
                    </label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           class="form-control-file <?php echo isset($errors['image']) ? 'error' : ''; ?>"
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small class="form-text text-muted">
                        Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF, WebP. Max size: 5MB.
                    </small>
                    <?php if (isset($errors['image'])): ?>
                        <span class="error-message"><?php echo $errors['image']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Image Preview -->
                <div class="form-group">
                    <div class="image-preview" id="image-preview">
                        <img id="preview-img" src="../assets/images/rooms/<?php echo htmlspecialchars($room['image'] ?? 'room-default.jpg'); ?>" 
                             alt="Image Preview" style="max-width: 100%; max-height: 100%;">
                        <span class="preview-text" style="display: none;">New image preview will appear here</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" name="update_room" class="btn btn-primary btn-lg btn-block btn-hover-scale">
                        <i class="fas fa-save"></i>
                        Update Room
                    </button>
                    <p class="form-note">* All fields marked with asterisk are required</p>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Additional CSS -->
<style>
/* Edit Room Styles - Same as add_room.php */
.room-form-card {
    background-color: var(--white);
    padding: var(--spacing-2xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    max-width: 800px;
    margin: 0 auto;
}

.room-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

.room-form .form-group {
    margin-bottom: var(--spacing-lg);
}

.room-form .form-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    font-weight: 500;
    color: var(--gray-700);
}

.room-form .form-control {
    width: 100%;
    padding: var(--spacing-md);
    font-size: var(--font-size-base);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-md);
    transition: all var(--transition-base);
}

.room-form .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.1);
    outline: none;
}

.room-form .form-control.error {
    border-color: var(--danger-color);
    background-color: rgba(231, 76, 60, 0.05);
}

.room-form textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.room-form .form-control-file {
    padding: var(--spacing-sm);
    background-color: var(--gray-50);
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-md);
    transition: all var(--transition-base);
}

.room-form .form-control-file:hover {
    border-color: var(--primary-color);
    background-color: rgba(201, 169, 98, 0.05);
}

.room-form .form-control-file:focus {
    border-color: var(--primary-color);
    outline: none;
}

/* Current Image */
.current-image {
    padding: var(--spacing-md);
    background-color: var(--gray-50);
    border-radius: var(--radius-md);
    display: inline-block;
}

/* Image Preview */
.image-preview {
    width: 100%;
    max-width: 400px;
    height: 300px;
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: var(--gray-50);
    margin: 0 auto;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-preview .preview-text {
    color: var(--gray-500);
    font-size: var(--font-size-sm);
}

/* Form Actions */
.form-actions {
    text-align: center;
    margin-top: var(--spacing-xl);
}

.form-actions .btn {
    margin-bottom: var(--spacing-md);
}

.form-note {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    text-align: center;
}

/* Error Messages */
.error-message {
    display: block;
    margin-top: var(--spacing-xs);
    font-size: var(--font-size-sm);
    color: var(--danger-color);
}

/* Alert */
.alert {
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.alert-success {
    background-color: rgba(39, 174, 96, 0.1);
    color: var(--success-color);
    border: 1px solid rgba(39, 174, 96, 0.2);
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(231, 76, 60, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .room-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .image-preview {
        max-width: 100%;
        height: 250px;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const previewImg = document.getElementById('preview-img');
    const previewText = document.querySelector('.preview-text');
    
    // Image preview functionality for new image
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    if (previewText) {
                        previewText.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('room-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
            
            // Validate required fields
            const requiredFields = ['room_number', 'type', 'price', 'description'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    showError(fieldId, 'This field is required');
                    hasErrors = true;
                }
            });
            
            // Validate price
            const priceField = document.getElementById('price');
            if (priceField.value && parseFloat(priceField.value) <= 0) {
                showError('price', 'Price must be greater than 0');
                hasErrors = true;
            }
            
            // Validate max guests
            const maxGuestsField = document.getElementById('max_guests');
            if (maxGuestsField.value && (parseInt(maxGuestsField.value) < 1 || parseInt(maxGuestsField.value) > 10)) {
                showError('max_guests', 'Must be between 1 and 10');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
            }
        });
    }
    
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        field.classList.add('error');
        
        const errorSpan = document.createElement('span');
        errorSpan.className = 'error-message';
        errorSpan.textContent = message;
        
        field.parentNode.appendChild(errorSpan);
    }
});
</script>

<?php
// Include admin footer
include __DIR__ . '/../includes/footer.php';
?>