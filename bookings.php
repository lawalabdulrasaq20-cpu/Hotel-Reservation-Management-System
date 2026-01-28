<?php
/**
 * Hotel Reservation System
 * Admin – Bookings Management
 * PHP 7.4+
 */

// ==================================================
// SESSION & AUTH
// ==================================================
session_start();

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$admin = getAdminDetails($pdo, $_SESSION['admin_id']);

// ==================================================
// FLASH MESSAGE
// ==================================================
$message = '';
$messageType = 'success';

// ==================================================
// HANDLE ACTIONS
// ==================================================
if (isset($_GET['action'], $_GET['id'])) {
    $bookingId = (int) $_GET['id'];
    $action    = sanitizeInput($_GET['action']);

    try {

        // ---------------- UPDATE STATUS ----------------
        if ($action === 'update_status' && isset($_GET['status'])) {

            $newStatus = sanitizeInput($_GET['status']);
            $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];

            if (in_array($newStatus, $allowedStatuses, true)) {

                $pdo->beginTransaction();

                // Update booking
                $stmt = $pdo->prepare(
                    "UPDATE reservations SET status = :status WHERE id = :id"
                );
                $stmt->execute([
                    ':status' => $newStatus,
                    ':id'     => $bookingId
                ]);

                // Update room status based on booking status
                $roomStatus = match ($newStatus) {
                    'confirmed'           => 'occupied',
                    'cancelled',
                    'completed'           => 'available',
                    default               => null
                };

                if ($roomStatus !== null) {
                    $stmt = $pdo->prepare(
                        "UPDATE rooms r
                         JOIN reservations b ON r.id = b.room_id
                         SET r.status = :room_status
                         WHERE b.id = :id"
                    );
                    $stmt->execute([
                        ':room_status' => $roomStatus,
                        ':id'          => $bookingId
                    ]);
                }

                $pdo->commit();
                $message = 'Booking status updated successfully.';
            }
        }

        // ---------------- DELETE BOOKING ----------------
        if ($action === 'delete') {

            $stmt = $pdo->prepare(
                "SELECT status FROM reservations WHERE id = :id"
            );
            $stmt->execute([':id' => $bookingId]);
            $booking = $stmt->fetch();

            if ($booking && in_array($booking['status'], ['pending', 'cancelled'], true)) {

                $stmt = $pdo->prepare(
                    "DELETE FROM reservations WHERE id = :id"
                );
                $stmt->execute([':id' => $bookingId]);

                $message = 'Booking deleted successfully.';
            } else {
                $message = 'Only pending or cancelled bookings can be deleted.';
                $messageType = 'error';
            }
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Booking error: ' . $e->getMessage());
        $message = 'A database error occurred.';
        $messageType = 'error';
    }
}

// ==================================================
// SEARCH & FILTER
// ==================================================
$searchTerm   = sanitizeInput($_GET['search'] ?? '');
$statusFilter = sanitizeInput($_GET['status'] ?? '');
$dateFilter   = sanitizeInput($_GET['date'] ?? '');

// ==================================================
// BASE QUERY
// ==================================================
$sql = "
SELECT r.*, rm.room_number, rm.type, rm.price
FROM reservations r
JOIN rooms rm ON r.room_id = rm.id
WHERE 1=1
";

$params = [];

if ($searchTerm) {
    $sql .= " AND (r.guest_name LIKE :search OR r.email LIKE :search OR rm.room_number LIKE :search)";
    $params[':search'] = "%{$searchTerm}%";
}

if ($statusFilter) {
    $sql .= " AND r.status = :status";
    $params[':status'] = $statusFilter;
}

if ($dateFilter) {
    $sql .= " AND (r.check_in = :date OR r.check_out = :date)";
    $params[':date'] = $dateFilter;
}

// ==================================================
// PAGINATION
// ==================================================
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

// Count total
$countSql = "SELECT COUNT(*) FROM ($sql) AS total";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalBookings = (int) $stmt->fetchColumn();
$totalPages = (int) ceil($totalBookings / $limit);

// Fetch data
$sql .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==================================================
// STATUS COUNTS
// ==================================================
$statusCounts = [];
$stmt = $pdo->query("SELECT status, COUNT(*) total FROM reservations GROUP BY status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = $row['total'];
}

// ==================================================
// SINGLE BOOKING VIEW
// ==================================================
$viewId = (int) ($_GET['view'] ?? 0);
$singleBooking = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare(
        "SELECT r.*, rm.room_number, rm.type, rm.price
         FROM reservations r
         JOIN rooms rm ON r.room_id = rm.id
         WHERE r.id = :id"
    );
    $stmt->execute([':id' => $viewId]);
    $singleBooking = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>


<!-- Include admin header -->


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
                <a href="bookings.php" class="admin-nav-item active">
                    <i class="fas fa-calendar-check"></i>
                    <span>Reservations</span>
                </a>
            </li>
            <li>
                <a href="rooms.php" class="admin-nav-item">
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
        <?php include __DIR__ . '/../includes/header.php'; ?>
        <!-- Admin Header -->
        <header class="admin-header">
            
            <div class="header-left">
                <h1>Reservation Management</h1>
                <p>Manage hotel bookings and guest reservations</p>
            </div>
            <div class="header-right">
                <a href="/hotel-system/reservation.php" target="_blank" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Booking
        </a>

            </div>
        </header>
        
        <!-- Flash Message -->
        <?php if ($message): ?>
        <div class="flash-message flash-<?php echo $messageType; ?>" id="flash-message">
            <div class="flash-content">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $message; ?></span>
                <button class="flash-close" onclick="closeFlashMessage()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Single Booking View -->
<?php if (!empty($singleBooking)): ?>

    <div class="booking-detail-card" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header">
            <h3><i class="fas fa-file-alt"></i> Booking Details</h3>
            <a href="bookings.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="booking-detail-content">

            <!-- Booking Information -->
            <div class="detail-section">
                <h4>Booking Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Booking ID:</span>
                        <span class="value">
                            #<?= str_pad($singleBooking['id'], 6, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Status:</span>
                        <span class="value">
                            <span class="status-badge status-<?= htmlspecialchars($singleBooking['status']); ?>">
                                <?= ucfirst($singleBooking['status']); ?>
                            </span>
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Booking Date:</span>
                        <span class="value">
                            <?= date('M j, Y H:i', strtotime($singleBooking['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Guest Information -->
            <div class="detail-section">
                <h4>Guest Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Guest Name:</span>
                        <span class="value"><?= htmlspecialchars($singleBooking['guest_name']); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($singleBooking['email']); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Phone:</span>
                        <span class="value"><?= htmlspecialchars($singleBooking['phone']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Room Information -->
            <div class="detail-section">
                <h4>Room Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Room:</span>
                        <span class="value">
                            <?= htmlspecialchars($singleBooking['type']); ?>
                            (#<?= htmlspecialchars($singleBooking['room_number']); ?>)
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Price / Night:</span>
                        <span class="value">$<?= number_format($singleBooking['price'], 2); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Check-in:</span>
                        <span class="value"><?= date('M j, Y', strtotime($singleBooking['check_in'])); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Check-out:</span>
                        <span class="value"><?= date('M j, Y', strtotime($singleBooking['check_out'])); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Total Price:</span>
                        <span class="value total-price">
                            $<?= number_format($singleBooking['total_price'], 2); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <?php if (!empty($singleBooking['special_requests'])): ?>
                <div class="detail-section">
                    <h4>Special Requests</h4>
                    <p class="special-requests">
                        <?= htmlspecialchars($singleBooking['special_requests']); ?>
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

<?php else: ?>

    <!-- LIST / TABLE VIEW GOES HERE -->
   <?php if (!empty($singleBooking) && isset($singleBooking['status'])): ?>
<!-- Actions -->
<div class="detail-actions">
    <h4>Actions</h4>
    <div class="action-buttons">

        <?php if ($singleBooking['status'] === 'pending'): ?>
            <a href="bookings.php?action=update_status&id=<?php echo (int)$singleBooking['id']; ?>&status=confirmed"
               class="btn btn-success"
               onclick="return confirm('Confirm this booking?')">
                <i class="fas fa-check"></i>
                Confirm
            </a>

            <a href="bookings.php?action=update_status&id=<?php echo (int)$singleBooking['id']; ?>&status=cancelled"
               class="btn btn-danger"
               onclick="return confirm('Cancel this booking?')">
                <i class="fas fa-times"></i>
                Cancel
            </a>

        <?php elseif ($singleBooking['status'] === 'confirmed'): ?>
            <a href="bookings.php?action=update_status&id=<?php echo (int)$singleBooking['id']; ?>&status=completed"
               class="btn btn-primary"
               onclick="return confirm('Mark as completed?')">
                <i class="fas fa-check-circle"></i>
                Complete
            </a>

            <a href="bookings.php?action=update_status&id=<?php echo (int)$singleBooking['id']; ?>&status=cancelled"
               class="btn btn-danger"
               onclick="return confirm('Cancel this booking?')">
                <i class="fas fa-times"></i>
                Cancel
            </a>

        <?php elseif ($singleBooking['status'] === 'cancelled'): ?>
            <span class="text-muted">No actions available for cancelled bookings</span>

        <?php elseif ($singleBooking['status'] === 'completed'): ?>
            <span class="text-muted">Booking completed</span>

        <?php endif; ?>

        <?php if (in_array($singleBooking['status'], ['pending', 'cancelled'], true)): ?>
            <a href="bookings.php?action=delete&id=<?php echo (int)$singleBooking['id']; ?>"
               class="btn btn-outline-danger"
               onclick="return confirm('Delete this booking permanently?')">
                <i class="fas fa-trash"></i>
                Delete
            </a>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- ================= SEARCH & FILTER SECTION ================= -->
<div class="search-filter-section" data-aos="fade-up" data-aos-delay="200">
    <form action="bookings.php" method="GET" class="search-filter-form">
        <div class="form-row">

            <!-- Search -->
            <div class="form-group">
                <label for="search" class="form-label">Search Bookings</label>
                <div class="input-group">
                    <i class="fas fa-search input-icon"></i>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Search by guest name, email, or room..."
                        value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($statusFilter ?? '') === 'pending' ? 'selected' : ''; ?>>
                        Pending (<?php echo $statusCounts['pending'] ?? 0; ?>)
                    </option>
                    <option value="confirmed" <?php echo ($statusFilter ?? '') === 'confirmed' ? 'selected' : ''; ?>>
                        Confirmed (<?php echo $statusCounts['confirmed'] ?? 0; ?>)
                    </option>
                    <option value="cancelled" <?php echo ($statusFilter ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                        Cancelled (<?php echo $statusCounts['cancelled'] ?? 0; ?>)
                    </option>
                    <option value="completed" <?php echo ($statusFilter ?? '') === 'completed' ? 'selected' : ''; ?>>
                        Completed (<?php echo $statusCounts['completed'] ?? 0; ?>)
                    </option>
                </select>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label for="date" class="form-label">Date</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="form-control"
                    value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
            </div>

            <!-- Actions -->
            <div class="form-group filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <?php if (!empty($searchTerm) || !empty($statusFilter) || !empty($dateFilter)): ?>
                    <a href="bookings.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<!-- ================= BOOKINGS TABLE ================= -->
<div class="bookings-table-card" data-aos="fade-up" data-aos-delay="400">
    <div class="card-header">
        <h3><i class="fas fa-calendar-check"></i> Reservation List</h3>
        <span class="booking-count">
            <?php echo (int)$totalBookings; ?> booking<?php echo $totalBookings !== 1 ? 's' : ''; ?>
        </span>
    </div>

<?php if (!empty($bookings)): ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Booked</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td>
                        <strong>#<?php echo str_pad((int)$booking['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </td>

                    <td>
                        <div class="guest-info">
                            <strong><?php echo htmlspecialchars($booking['guest_name']); ?></strong>
                            <small><?php echo htmlspecialchars($booking['email']); ?></small>
                        </div>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking['type']); ?>
                        (#<?php echo htmlspecialchars($booking['room_number']); ?>)
                    </td>

                    <td><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>

                    <!-- ✅ FIXED GUESTS -->
                    <td><?php echo htmlspecialchars($booking['guests'] ?? '—'); ?></td>

                    <td>$<?php echo number_format((float)$booking['total_price'], 2); ?></td>

                    <td>
                        <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                        </span>
                    </td>

                    <td><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>

                    <td>
                        <div class="action-buttons">
                            <a href="bookings.php?view=<?php echo (int)$booking['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>

                            <?php if ($booking['status'] === 'pending'): ?>
                                <a href="bookings.php?action=update_status&id=<?php echo (int)$booking['id']; ?>&status=confirmed"
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Confirm this booking?')">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                <a href="bookings.php?action=update_status&id=<?php echo (int)$booking['id']; ?>&status=cancelled"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Cancel this booking?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?> –
                <?php echo min($offset + $limit, $totalBookings); ?>
                of <?php echo $totalBookings; ?> bookings
            </div>

            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="bookings.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&date=<?php echo urlencode($dateFilter); ?>"
                       class="btn btn-sm btn-outline">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <span class="page-numbers">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="bookings.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&date=<?php echo urlencode($dateFilter); ?>"
                       class="btn btn-sm btn-outline">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h4>No Bookings Found</h4>
        <p>No bookings match your search criteria.</p>
        <a href="bookings.php" class="btn btn-primary">Clear Filters</a>
    </div>
<?php endif; ?>
</div>


<!-- Additional CSS -->
<style>
/* Bookings Styles */
.search-filter-section {
    margin-bottom: var(--spacing-xl);
}

.search-filter-form {
    background-color: var(--white);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.search-filter-form .form-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: var(--spacing-lg);
    align-items: end;
}

.search-filter-form .form-group {
    margin-bottom: 0;
}

.filter-actions {
    display: flex;
    gap: var(--spacing-sm);
    align-items: end;
}

.filter-actions .btn {
    margin-bottom: 0;
}

/* Bookings Table Card */
.bookings-table-card,
.booking-detail-card {
    background-color: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-xl);
    border-bottom: 1px solid var(--gray-200);
}

.card-header h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--secondary-color);
    margin: 0;
}

.booking-count {
    background-color: var(--primary-color);
    color: var(--white);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 500;
}

/* Guest Info */
.guest-info strong {
    display: block;
    color: var(--secondary-color);
    margin-bottom: var(--spacing-xs);
}

.guest-info small {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

/* Status Badges */
.status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 500;
    text-transform: uppercase;
}

.status-badge.status-pending {
    background-color: rgba(243, 156, 18, 0.1);
    color: var(--warning-color);
}

.status-badge.status-confirmed {
    background-color: rgba(39, 174, 96, 0.1);
    color: var(--success-color);
}

.status-badge.status-cancelled {
    background-color: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
}

.status-badge.status-completed {
    background-color: rgba(52, 152, 219, 0.1);
    color: var(--info-color);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: var(--spacing-xs);
}

.action-buttons .btn {
    padding: var(--spacing-xs);
    min-height: auto;
}

/* Booking Detail Styles */
.booking-detail-content {
    padding: var(--spacing-2xl);
}

.detail-section {
    margin-bottom: var(--spacing-2xl);
}

.detail-section h4 {
    color: var(--secondary-color);
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--gray-200);
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--gray-100);
}

.detail-item .label {
    font-weight: 500;
    color: var(--gray-600);
}

.detail-item .value {
    color: var(--secondary-color);
    font-weight: 500;
}

.detail-item .value.total-price {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--primary-color);
}

.special-requests {
    background-color: var(--gray-50);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    color: var(--gray-700);
    line-height: 1.6;
}

.detail-actions {
    background-color: var(--gray-50);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
}

.detail-actions h4 {
    color: var(--secondary-color);
    margin-bottom: var(--spacing-lg);
}

.detail-actions .action-buttons {
    justify-content: flex-start;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--spacing-3xl);
}

.empty-state i {
    font-size: var(--font-size-4xl);
    color: var(--gray-300);
    margin-bottom: var(--spacing-lg);
}

.empty-state h4 {
    color: var(--gray-600);
    margin-bottom: var(--spacing-sm);
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: var(--spacing-lg);
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-top: 1px solid var(--gray-200);
}

.pagination-info {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.pagination-links {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.page-numbers {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

/* Responsive */
@media (max-width: 1200px) {
    .search-filter-form .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .search-filter-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .pagination {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-actions .action-buttons {
        flex-direction: column;
    }
}
/* ======================================
   ADMIN PAGE CONTENT FIX (IMPORTANT)
====================================== */

/* Ensure admin wrapper fills screen */
/* ================================
   ADMIN LAYOUT (FIXED SIDEBAR)
================================ */

/* Wrapper */
.admin-wrapper {
    display: flex;
}

.admin-sidebar {
    width: 260px;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
}

.admin-main {
    margin-left: 260px;
    width: calc(100% - 260px);
    min-height: 100vh;
}

.admin-header {
    background: #f5f7fa;
    padding: 24px;
}


/* Prevent frontend footer from overlapping admin */
.admin-main footer,
.admin-wrapper footer,
.admin-main .site-footer {
    display: none !important; /* 🔥 hide frontend footer in admin */
}

/* Make tables scroll instead of overflowing */
.bookings-table-card {
    width: 100%;
    overflow-x: auto;
}

/* Ensure table never gets cut */
.admin-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
}

/* Keep actions visible */
.admin-table th,
.admin-table td {
    white-space: nowrap;
}

/* Buttons stay inline */
.action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
}

/* ======================================
   RESPONSIVE SAFETY
====================================== */

@media (max-width: 992px) {
    .admin-main {
        margin-left: 260px;
    }

    .admin-table {
        min-width: 1000px;
    }
}

@media (max-width: 768px) {
    .admin-main {
        margin-left: 0;
        padding: 16px;
    }
}

</style>

<?php
// Include admin footer
include __DIR__ . '/../includes/footer.php';
?>