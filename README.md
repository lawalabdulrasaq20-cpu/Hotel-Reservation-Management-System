# Hotel Reservation Management System

A complete and fully functional Hotel Reservation Management System built with PHP, MySQL, HTML, CSS, and JavaScript. Features a modern UI with video background, smooth animations, responsive design, and comprehensive admin panel.

## 🌟 Features

### Frontend Features
- **Modern Homepage** with full-screen video background and smooth animations
- **Room Listings** with search and filter functionality
- **Online Reservation System** with date availability checking
- **Booking Confirmation** with printable details
- **Responsive Design** that works on all devices
- **Smooth Animations** using AOS (Animate On Scroll)
- **Interactive Forms** with client-side validation

### Admin Panel Features
- **Secure Admin Login** with session management
- **Dashboard** with statistics and charts
- **Room Management** (Add, Edit, Delete, Update Status)
- **Reservation Management** (View, Confirm, Cancel bookings)
- **Image Upload** for room photos
- **Responsive Admin Interface**

### Technical Features
- **Secure Code** using PDO prepared statements
- **Form Validation** (client and server-side)
- **Prevent Double Bookings** with date conflict checking
- **Automatic Price Calculation**
- **CSRF Protection**
- **Error Handling** and user-friendly messages

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with CSS Variables
- **Animations**: AOS (Animate On Scroll) Library
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Playfair Display + Inter)

## 🚀 Installation Guide

### Prerequisites

- Web Server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Browser with JavaScript enabled

### Step 1: Download and Extract

1. Download the project files
2. Extract to your web server directory (e.g., `htdocs` for XAMPP, `www` for WAMP)
3. Rename the folder if desired (e.g., `hotel-reservation-system`)

### Step 2: Database Setup

1. Open phpMyAdmin or your MySQL client
2. Create a new database named `hotel_reservation_db`
3. Import the SQL file located at `sql/hotel_db.sql`
4. The database will be created with sample data

### Step 3: Database Configuration

1. Open `includes/db_connect.php`
2. Update the database connection settings:

```php
define('DB_HOST', 'localhost');      // Database host
define('DB_NAME', 'hotel_reservation_db'); // Database name
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
```

### Step 4: File Permissions

Ensure the following directories are writable by the web server:
- `assets/images/rooms/` (for room image uploads)
- `assets/images/` (for general image uploads)

### Step 5: Access the System

**Frontend Website**: `http://localhost/hotel-reservation-system/public/`

**Admin Panel**: `http://localhost/hotel-reservation-system/admin/login.php`

### Default Admin Login

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **IMPORTANT**: Change the default admin password immediately after first login!

## 📊 Database Structure

### Tables

#### `rooms`
- `id` - Primary key
- `room_number` - Room number/identifier
- `type` - Room type (Single, Double, Suite, etc.)
- `price` - Price per night
- `description` - Room description
- `image` - Room image filename
- `status` - Room status (available, occupied, maintenance)
- `max_guests` - Maximum number of guests
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `reservations`
- `id` - Primary key
- `guest_name` - Guest full name
- `email` - Guest email address
- `phone` - Guest phone number
- `room_id` - Foreign key to rooms table
- `check_in` - Check-in date
- `check_out` - Check-out date
- `total_price` - Total booking price
- `status` - Booking status (pending, confirmed, cancelled, completed)
- `special_requests` - Guest special requests
- `created_at` - Booking timestamp
- `updated_at` - Last update timestamp

#### `admin`
- `id` - Primary key
- `username` - Admin username
- `password` - Hashed password
- `email` - Admin email
- `full_name` - Admin full name
- `last_login` - Last login timestamp
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## 🔧 Configuration

### Database Settings
Edit `includes/db_connect.php` to configure your database connection.

### Site Settings
You can customize site settings in the header files or create a dedicated config file.

### Email Configuration
For booking confirmations, configure email settings in the reservation processing script.

## 🎯 Usage Guide

### For Guests

1. **Browse Rooms**: Visit the homepage and explore available rooms
2. **Search Rooms**: Use the search feature to find rooms by dates and guests
3. **Make Reservation**: Select a room and fill out the booking form
4. **Confirmation**: Receive booking confirmation with details

### For Administrators

1. **Login**: Access the admin panel with your credentials
2. **Dashboard**: View hotel statistics and recent activity
3. **Room Management**: Add, edit, or delete rooms
4. **Reservation Management**: View, confirm, or cancel bookings
5. **Reports**: Generate insights from booking data

## 🔒 Security Features

- **PDO Prepared Statements** - Prevents SQL injection
- **Password Hashing** - Secure password storage using bcrypt
- **CSRF Protection** - Token-based form protection
- **Input Validation** - Server-side validation for all inputs
- **Session Management** - Secure session handling
- **File Upload Validation** - Image upload security

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets (iPad, Android tablets)
- Mobile phones (iPhone, Android)

## 🎨 Customization

### Colors
Edit CSS variables in `assets/css/style.css`:

```css
:root {
    --primary-color: #c9a962;
    --secondary-color: #2c3e50;
    /* ... other variables
}
```

### Images
- Replace images in `assets/images/`
- Room images are uploaded to `assets/images/rooms/`

### Fonts
- Google Fonts are loaded in the header
- Change fonts by updating the font URLs

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/db_connect.php`
   - Ensure MySQL is running

2. **Images Not Uploading**
   - Check file permissions on `assets/images/rooms/`
   - Verify PHP file upload limits

3. **Pages Not Loading**
   - Check .htaccess configuration
   - Verify file paths and includes

4. **Session Issues**
   - Check PHP session configuration
   - Clear browser cookies/cache

### Debug Mode

To enable error reporting for development:

```php
// Add to top of PHP files
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Future Enhancements

Potential features to add:

- [ ] Online payment integration (Stripe, PayPal)
- [ ] Email notifications for bookings
- [ ] Multi-language support
- [ ] Advanced reporting and analytics
- [ ] Room availability calendar
- [ ] Guest reviews and ratings
- [ ] Booking modification system
- [ ] Housekeeping management
- [ ] Rate management (seasonal pricing)
- [ ] Inventory management

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Support

For support and questions:
- Check the troubleshooting section
- Review the code comments
- Check server error logs
- Ensure all requirements are met

## 🙏 Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- AOS library for animations
- Chart.js for admin charts
- All contributors and testers

---

**Hotel Reservation System** - Built with ❤️ for the hospitality industry

For more information, visit the admin dashboard or contact the development team.
