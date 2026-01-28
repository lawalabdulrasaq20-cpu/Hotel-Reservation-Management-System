<?php
session_start();

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$admin = getAdminDetails($pdo, $_SESSION['admin_id']);

// --------------------------------------------------
// Fetch dashboard statistics
// --------------------------------------------------
try {
    $stats = [];

    // Total rooms
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM rooms");
    $stats['total_rooms'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Available rooms
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM rooms WHERE status = 'available'");
    $stats['available_rooms'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Occupied rooms
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT room_id) AS count
        FROM reservations
        WHERE status = 'confirmed'
        AND check_in <= CURDATE()
        AND check_out > CURDATE()
    ");
    $stats['occupied_rooms'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total reservations
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM reservations");
    $stats['total_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Pending reservations
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM reservations WHERE status = 'pending'");
    $stats['pending_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Confirmed reservations
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM reservations WHERE status = 'confirmed'");
    $stats['confirmed_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Cancelled reservations
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM reservations WHERE status = 'cancelled'");
    $stats['cancelled_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total revenue
    $stmt = $pdo->query("
        SELECT SUM(total_price) AS total
        FROM reservations
        WHERE status IN ('confirmed', 'completed')
    ");
    $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Today's check-ins
    $stmt = $pdo->query("
        SELECT COUNT(*) AS count
        FROM reservations
        WHERE check_in = CURDATE()
        AND status IN ('confirmed', 'pending')
    ");
    $stats['today_checkins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Today's check-outs
    $stmt = $pdo->query("
        SELECT COUNT(*) AS count
        FROM reservations
        WHERE check_out = CURDATE()
        AND status IN ('confirmed', 'completed')
    ");
    $stats['today_checkouts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Recent reservations
    $stmt = $pdo->query("
        SELECT r.*, rm.type, rm.room_number
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $recentReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Room type distribution
    $stmt = $pdo->query("SELECT type, COUNT(*) AS count FROM rooms GROUP BY type");
    $roomTypeDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly reservations (last 6 months)
    $stmt = $pdo->query("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(*) AS count,
            SUM(total_price) AS revenue
        FROM reservations
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Dashboard error: ' . $e->getMessage());

    $stats = [
        'total_rooms' => 0,
        'available_rooms' => 0,
        'occupied_rooms' => 0,
        'total_reservations' => 0,
        'pending_reservations' => 0,
        'confirmed_reservations' => 0,
        'cancelled_reservations' => 0,
        'total_revenue' => 0,
        'today_checkins' => 0,
        'today_checkouts' => 0
    ];

    $recentReservations = [];
    $roomTypeDistribution = [];
    $monthlyStats = [];
}

// Occupancy rate
$occupancyRate = $stats['total_rooms'] > 0
    ? round(($stats['occupied_rooms'] / $stats['total_rooms']) * 100, 1)
    : 0;

// Flash messages
$flashMessage = $_SESSION['flash_message'] ?? '';
$flashMessageType = $_SESSION['flash_message_type'] ?? 'success';

unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);

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
                <a href="dashboard.php" class="admin-nav-item active">
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
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($admin['full_name'] ?? $admin['username']); ?></p>
            </div>
            <div class="header-right">
                <div class="admin-info">
                    <span class="admin-name"><?php echo htmlspecialchars($admin['username']); ?></span>
                    <div class="admin-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
        <div class="flash-message flash-<?php echo $flashMessageType; ?>" id="flash-message">
            <div class="flash-content">
                <i class="fas fa-<?php echo $flashMessageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $flashMessage; ?></span>
                <button class="flash-close" onclick="closeFlashMessage()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="200">
            <!-- Total Rooms -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_rooms']; ?></h3>
                    <p>Total Rooms</p>
                </div>
            </div>
            
            <!-- Available Rooms -->
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['available_rooms']; ?></h3>
                    <p>Available Rooms</p>
                </div>
            </div>
            
            <!-- Occupied Rooms -->
            <div class="stat-card occupied">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['occupied_rooms']; ?></h3>
                    <p>Occupied Rooms</p>
                </div>
            </div>
            
            <!-- Occupancy Rate -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $occupancyRate; ?>%</h3>
                    <p>Occupancy Rate</p>
                </div>
            </div>
            
            <!-- Total Reservations -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_reservations']; ?></h3>
                    <p>Total Reservations</p>
                </div>
            </div>
            
            <!-- Pending Reservations -->
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_reservations']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            
            <!-- Confirmed Reservations -->
            <div class="stat-card confirmed">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['confirmed_reservations']; ?></h3>
                    <p>Confirmed</p>
                </div>
            </div>
            
            <!-- Total Revenue -->
            <div class="stat-card revenue">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Charts and Analytics -->
        <div class="analytics-grid" data-aos="fade-up" data-aos-delay="400">
            <!-- Occupancy Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Room Status Overview</h3>
                </div>
                <div class="chart-content">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
            
            <!-- Revenue Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Monthly Revenue</h3>
                </div>
                <div class="chart-content">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="recent-reservations" data-aos="fade-up" data-aos-delay="600">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Recent Reservations</h3>
                <a href="bookings.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            
            <?php if (!empty($recentReservations)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReservations as $reservation): ?>
                        <tr>
                            <td>#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($reservation['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['type']); ?> (#<?php echo htmlspecialchars($reservation['room_number']); ?>)</td>
                            <td><?php echo date('M j, Y', strtotime($reservation['check_in'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($reservation['check_out'])); ?></td>
                            <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="bookings.php?view=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No Recent Reservations</h4>
                <p>No reservations have been made recently.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions" data-aos="fade-up" data-aos-delay="800">
            <h3>Quick Actions</h3>
            <div class="actions-grid">
                <a href="add_room.php" class="action-card">
                    <i class="fas fa-plus-circle"></i>
                    <h4>Add New Room</h4>
                    <p>Create a new room listing</p>
                </a>
                <a href="bookings.php?status=pending" class="action-card">
                    <i class="fas fa-clock"></i>
                    <h4>Pending Reservations</h4>
                    <p>Review <?php echo $stats['pending_reservations']; ?> pending bookings</p>
                </a>
                <a href="rooms.php" class="action-card">
                    <i class="fas fa-bed"></i>
                    <h4>Manage Rooms</h4>
                    <p>View and edit room details</p>
                </a>
                <a href="bookings.php" class="action-card">
                    <i class="fas fa-calendar-check"></i>
                    <h4>All Reservations</h4>
                    <p>View complete booking list</p>
                </a>
            </div>
        </div>
    </main>
</div>

<!-- Additional CSS -->
<style>
/* Admin Dashboard Styles */
.admin-wrapper {
    display: flex;
    min-height: 100vh;
    background-color: var(--gray-100);
}

/* Admin Sidebar */
.admin-sidebar {
    width: 250px;
    background-color: var(--secondary-color);
    color: var(--white);
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: var(--z-fixed);
}

.admin-sidebar-header {
    padding: var(--spacing-xl);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.admin-logo {
    font-size: var(--font-size-xl);
    font-weight: 600;
}

.admin-logo i {
    margin-right: var(--spacing-sm);
    color: var(--primary-color);
}

.admin-nav {
    list-style: none;
    padding: var(--spacing-md) 0;
}

.admin-nav-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md) var(--spacing-lg);
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all var(--transition-base);
    border-left: 3px solid transparent;
}

.admin-nav-item:hover,
.admin-nav-item.active {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--white);
    border-left-color: var(--primary-color);
}

.admin-nav-item.logout {
    color: var(--danger-color);
}

.admin-nav-item.logout:hover {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--danger-color);
}

/* Admin Main Content */
.admin-main {
    flex: 1;
    margin-left: 250px;
    padding: var(--spacing-lg);
}

/* Admin Header */
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2xl);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
}

.header-left h1 {
    font-size: var(--font-size-3xl);
    color: var(--secondary-color);
    margin-bottom: var(--spacing-xs);
}

.header-left p {
    color: var(--gray-600);
}

.admin-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.admin-name {
    font-weight: 500;
    color: var(--secondary-color);
}

.admin-avatar {
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--font-size-lg);
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-2xl);
}

.stat-card {
    background-color: var(--white);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    transition: transform var(--transition-base);
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xl);
    color: var(--white);
    background-color: var(--primary-color);
}

.stat-card.available .stat-icon {
    background-color: var(--success-color);
}

.stat-card.occupied .stat-icon {
    background-color: var(--warning-color);
}

.stat-card.pending .stat-icon {
    background-color: var(--warning-color);
}

.stat-card.confirmed .stat-icon {
    background-color: var(--success-color);
}

.stat-card.revenue .stat-icon {
    background-color: var(--primary-dark);
}

.stat-content h3 {
    font-size: var(--font-size-2xl);
    color: var(--secondary-color);
    margin-bottom: var(--spacing-xs);
}

.stat-content p {
            color: var(--gray-600);
            margin: 0;
        }
        
        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }
        
        .chart-card {
            background-color: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .chart-header {
            margin-bottom: var(--spacing-lg);
        }
        
        .chart-header h3 {
            color: var(--secondary-color);
            margin-bottom: var(--spacing-sm);
        }
        
        .chart-content {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Recent Reservations */
        .recent-reservations {
            background-color: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .card-header h3 {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--secondary-color);
        }
        
        /* Admin Table */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th,
        .admin-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .admin-table th {
            background-color: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .admin-table tr:hover {
            background-color: var(--gray-50);
        }
        
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
        }
        
        /* Quick Actions */
        .quick-actions {
            background-color: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .quick-actions h3 {
            color: var(--secondary-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--spacing-lg);
        }
        
        .action-card {
            display: block;
            text-align: center;
            padding: var(--spacing-xl);
            background-color: var(--gray-50);
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--secondary-color);
            transition: all var(--transition-base);
        }
        
        .action-card:hover {
            background-color: var(--primary-color);
            color: var(--white);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .action-card i {
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-md);
            color: var(--primary-color);
            transition: color var(--transition-base);
        }
        
        .action-card:hover i {
            color: var(--white);
        }
        
        .action-card h4 {
            margin-bottom: var(--spacing-sm);
            color: inherit;
        }
        
        .action-card p {
            font-size: var(--font-size-sm);
            color: inherit;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition-base);
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                gap: var(--spacing-md);
                text-align: center;
            }
        }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Occupied', 'Maintenance'],
                datasets: [{
                    data: [
                        <?php echo $stats['available_rooms']; ?>,
                        <?php echo $stats['occupied_rooms']; ?>,
                        <?php echo $stats['total_rooms'] - $stats['available_rooms'] - $stats['occupied_rooms']; ?>
                    ],
                    backgroundColor: [
                        '#27ae60',
                        '#f39c12',
                        '#e74c3c'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Revenue Chart (if data exists)
        <?php if (!empty($monthlyStats)): ?>
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach (array_reverse($monthlyStats) as $stat) echo '"' . date('M Y', strtotime($stat['month'])) . '",'; ?>],
                datasets: [{
                    label: 'Reservations',
                    data: [<?php foreach (array_reverse($monthlyStats) as $stat) echo $stat['count'] . ','; ?>],
                    backgroundColor: '#c9a962',
                    borderColor: '#a88b42',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php else: ?>
        document.getElementById('revenueChart').parentNode.innerHTML = '<p class="text-center text-muted">No data available</p>';
        <?php endif; ?>
    </script>

<!-- Include admin footer -->
<?php include __DIR__ . '/../includes/footer.php'; ?>