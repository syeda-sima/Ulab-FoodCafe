# ULAB FoodCafe - Web-Based Food Ordering System

A comprehensive web-based food ordering system for the University of Liberal Arts Bangladesh (ULAB) FoodCafe, enabling students, faculty, and staff to order meals easily through an online platform.

## Features

### User Features
- **User Registration & Login**: Separate roles for Students, Faculty, and Staff with ULAB email verification
- **Daily Menu Browsing**: View categorized menu items (Breakfast, Lunch, Snacks, Drinks)
- **Online Ordering**: Add items to cart and place orders
- **Pre-Ordering**: Pre-order meals for later pickup
- **Order Tracking**: Real-time order status updates (Pending → Preparing → Ready → Completed)
- **Payment Options**: Multiple payment methods including cash, card, SSL Commerce, bKash, and Nagad
- **Feedback & Ratings**: Rate meals and provide feedback
- **Notifications**: Real-time notifications for order updates

### Staff Features
- **Order Management**: Update order status and track orders
- **Menu Management**: Add, edit, and update menu items

### Admin Features
- **Dashboard**: Monitor sales, orders, and user activity
- **Reports**: Generate sales reports and analytics
- **User Management**: Manage users and roles
- **Feedback Management**: View and analyze user feedback
- **Peak Hours Analysis**: Identify busy hours

## Technology Stack

- **Frontend**: HTML5, CSS3
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache, MySQL, PHP)

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   Place the project in: C:\xampp\htdocs\Ulab_FoodCafe
   ```

2. **Start XAMPP**
   - Start Apache and MySQL services from XAMPP Control Panel

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema from `database/schema.sql`
   - Or run the SQL file directly in phpMyAdmin

4. **Configure Database**
   - Edit `config.php` and update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'ulab_foodcafe');
     ```

5. **Configure Payment Gateway** (Optional)
   - Edit `config.php` and add your SSL Commerce credentials:
     ```php
     define('SSL_STORE_ID', 'your_store_id');
     define('SSL_STORE_PASSWORD', 'your_store_password');
     ```

6. **Access the Application**
   - Open browser and navigate to: `http://localhost/Ulab_FoodCafe`

## Project Structure

```
Ulab_FoodCafe/
├── admin/
│   ├── dashboard.php
│   ├── menu_manage.php
│   ├── reports.php
│   └── feedback_manage.php
├── assets/
│   └── css/
│       └── style.css
├── database/
│   └── schema.sql
├── includes/
│   ├── header.php
│   └── footer.php
├── payment/
│   ├── process.php
│   ├── success.php
│   ├── fail.php
│   └── cancel.php
├── staff/
│   └── dashboard.php
├── config.php
├── index.php
├── login.php
├── register.php
├── logout.php
├── menu.php
├── cart.php
├── checkout.php
├── orders.php
├── order_details.php
├── feedback.php
├── notifications.php
└── README.md
```

## Database Schema

The database includes the following tables:
- `users` - User accounts and authentication
- `menu_items` - Menu items with categories and pricing
- `orders` - Order information and status
- `order_items` - Individual items in each order
- `feedback` - User feedback and ratings
- `notifications` - System notifications

## Payment Gateway Integration

### SSL Commerce
The system includes SSL Commerce integration. Configure your store credentials in `config.php`:
- Store ID
- Store Password
- Set `SSL_IS_LIVE` to `true` for production

### bKash/Nagad
Currently includes demo integration. Replace with actual API integration for production use.

## Security Features

- Password hashing using PHP `password_hash()`
- SQL injection prevention using prepared statements
- Session management for authentication
- Role-based access control
- Email verification (framework ready)

## Future Enhancements

- Email verification system
- SMS notifications
- Mobile app integration
- Advanced analytics dashboard
- Inventory management
- Discount and coupon system
- Loyalty program

## Support

For issues or questions, please contact the development team.

## License

This project is developed for ULAB FoodCafe internal use.

## Credits

Developed for University of Liberal Arts Bangladesh (ULAB)

