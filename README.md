# 🌟 CREAMS - Community REhAbilitation Management System

![CREAMS Logo](public/images/creams-logo.png)

## 📋 Overview
The **Community REhAbilitation Management System (CREAMS)** is a comprehensive web-based application designed to streamline operations in rehabilitation centres serving children with special needs. This system provides an integrated solution for managing participants, personnel, activities, centre resources, and more, improving administrative efficiency and enhancing service delivery.

## ✨ Recent Enhancements & Features

### 🏠 Home Page Improvements
- **Modernized UI/UX**: Redesigned header with 80% width coverage for better visual balance
- **Enhanced Navigation**: Added clearer navigation with hover effects and active states
- **Video Background**: Implemented video background with fallback images for better engagement
- **Impact Statistics**: Added animated statistics counters for key metrics
- **Responsive Design**: Improved responsive behavior across all device sizes
- **Footer Upgrade**: Implemented comprehensive footer with quick links, newsletter signup, and contact information

### 📞 Contact Page
- **Intuitive Layout**: Clean, professional contact interface with clear sections
- **Interactive Map**: Google Maps integration to help visitors locate facilities
- **Contact Form**: User-friendly form with validation for direct communication
- **Volunteer Information**: Details about volunteer opportunities and requirements
- **Staff Profiles**: Introduction to key personnel and their contact information
- **FAQ Section**: Common questions with expandable answers

### 🤝 Volunteer Page
- **Engaging Hero Section**: Video hero section with clear call-to-action
- **Volunteer Opportunities**: Card-based layout detailing different volunteer roles
- **Multi-step Application Form**: User-friendly application process with step-by-step progression
- **Volunteer Testimonials**: Authentic stories from current volunteers
- **Comprehensive FAQ**: Detailed answers to questions about the volunteer program
- **Training Information**: Section explaining volunteer training and support resources

## 🛠️ Technical Features
- **Laravel Framework**: Built on Laravel for robust backend functionality
- **Responsive Design**: Bootstrap framework ensuring compatibility across all devices
- **Modern JavaScript**: Enhanced interactivity with vanilla JS and jQuery
- **CSS Animations**: AOS (Animate On Scroll) for engaging scroll animations
- **Modular Components**: Reusable layouts and partials for consistent design and easier maintenance
- **Form Validation**: Client and server-side validation for data integrity
- **Optimized Assets**: Compressed images and efficient code structure for faster loading

## 📋 Prerequisites
Before installing CREAMS, ensure your system meets the following requirements:
- **PHP 8.0+**: The backbone of Laravel ([Download PHP](https://www.php.net/downloads.php))
- **Composer**: PHP dependency manager ([Download Composer](https://getcomposer.org/download/))
- **Node.js & npm**: For compiling assets ([Download Node.js](https://nodejs.org/))
- **XAMPP/WAMP/MAMP**: Local development environment with Apache and MySQL ([Download XAMPP](https://www.apachefriends.org/download.html))
- **Git**: Version control system ([Download Git](https://git-scm.com/downloads))

## 🚀 Installation & Setup Guide

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/FaisalHanafi/CREAMS.git
cd CREAMS
```

### 2️⃣ Install PHP Dependencies
```bash
composer install
```

### 3️⃣ Install Frontend Dependencies
```bash
npm install
npm run dev  # Compile assets for development
# OR
npm run prod  # Compile assets for production
```

### 4️⃣ Configure Environment
```bash
# Create .env file from example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5️⃣ Configure Database
1. Create a new database in MySQL (through phpMyAdmin or command line)
2. Update the `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creams
DB_USERNAME=root
DB_PASSWORD=
```

### 6️⃣ Run Migrations
```bash
php artisan migrate
```

### 7️⃣ Seed the Database (Optional)
```bash
php artisan db:seed
```

### 8️⃣ Configure Storage Permissions
```bash
# On Unix-based systems
chmod -R 775 storage bootstrap/cache
```

### 9️⃣ Create Symbolic Link for Storage
```bash
php artisan storage:link
```

### 🔟 Clear Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 1️⃣1️⃣ Start the Development Server
```bash
php artisan serve
```

### 1️⃣2️⃣ Access the Application
Visit `http://127.0.0.1:8000` in your web browser.

## 🧪 Testing
Run the automated tests to ensure everything is working properly:
```bash
php artisan test
```

## 📁 Project Structure Overview

### 📂 Key Directories
- **`app/`**: Core application code
- **`resources/views/`**: Blade templates for UI
  - **`layouts/`**: Reusable layout components (header, footer)
  - **`home.blade.php`**: Home page template
  - **`contact.blade.php`**: Contact page template
  - **`volunteer.blade.php`**: Volunteer page template
- **`public/`**: Publicly accessible assets
  - **`css/`**: Stylesheets
  - **`js/`**: JavaScript files
  - **`images/`**: Image assets
  - **`videos/`**: Video assets

## 🔧 Customization Guide

### 📱 Modifying Pages
1. **Home Page**: Edit `resources/views/home.blade.php`
2. **Contact Page**: Edit `resources/views/contact.blade.php`
3. **Volunteer Page**: Edit `resources/views/volunteer.blade.php`

### 🎨 Changing Styles
1. **Home Styles**: Edit `public/css/homestyle.css`
2. **Header Styles**: Edit `public/css/headerstyle.css`
3. **Footer Styles**: Edit `public/css/footerstyle.css`
4. **Contact Styles**: Edit `public/css/contactstyle.css`
5. **Volunteer Styles**: Edit `public/css/volunteerstyle.css`

### 📋 Adding/Modifying Content
- **Images**: Add to `public/images/`
- **Videos**: Add to `public/videos/`

## 🌐 Deployment Instructions
For deploying to a production server:

1. **Shared Hosting**:
   - Upload all files to your web server via FTP
   - Point your domain to the `public/` directory
   - Configure `.env` with production settings
   - Run all artisan commands on the server

2. **VPS/Dedicated Server**:
   - Clone the repository on your server
   - Follow the installation steps above
   - Set up a web server (Nginx or Apache) to point to the `public/` directory
   - Configure for HTTPS using Let's Encrypt or other SSL provider

## 🚨 Troubleshooting

### Common Issues
1. **View not found errors**:
   - Check file paths and ensure blade templates exist
   - Run `php artisan view:clear` to clear view cache

2. **Database connection errors**:
   - Verify credentials in `.env` file
   - Ensure database server is running

3. **Permission issues**:
   - Set proper permissions on storage and bootstrap/cache directories
   - On Unix systems: `chmod -R 775 storage bootstrap/cache`

4. **Asset loading issues**:
   - Clear browser cache
   - Verify URLs in asset() helpers
   - Run `npm run dev` to recompile assets

## 📚 Additional Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Bootstrap Documentation](https://getbootstrap.com/docs)
- [Font Awesome Icons](https://fontawesome.com/icons)

## 👥 Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgements
- The CREAMS project is a collaborative initiative between International Islamic University Malaysia (IIUM) and Jabatan Kebajikan Masyarakat Malaysia (JKM).
- Special thanks to Dr. Suriani Binti Sulaiman for the supervision and guidance.
- Thanks to the Disability Services Unit (DSU) of IIUM for their invaluable collaboration and support.

---

## 📞 Contact Information
For any questions or support, please contact:
- **Email**: dsu-creams@iium.edu.my
- **Phone**: (+60) 3642 1633 5

---

💖 **Made with love for the special needs community** 💖