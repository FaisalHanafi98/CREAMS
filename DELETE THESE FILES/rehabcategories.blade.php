<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rehabilitation Categories - CREAMS</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #32bdea;
            --secondary-color: #c850c0;
            --success-color: #2ed573;
            --danger-color: #ff4757;
            --warning-color: #ffa502;
            --info-color: #1e90ff;
            --dark-color: #1a2a3a;
            --light-color: #f8f9fa;
            --border-color: #e9ecef;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Topbar styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 15px 0 0;
        }
        
        .sidebar-toggle {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .topbar-logo {
            width: 190px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .topbar-logo a {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 1px;
            text-decoration: none;
            width: 100%;
            height: 100%;
        }
        
        .topbar-logo i {
            margin-right: 10px;
        }
        
        .topbar-title {
            margin-left: 20px;
            font-size: 18px;
            font-weight: 600;
            background-image: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            padding-bottom: 3px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            background: var(--light-color);
            margin-left: auto;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 10px;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }
        
        .user-role {
            font-size: 12px;
            color: #888;
            margin-top: -2px;
        }
        
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            height: calc(100% - 60px);
            width: 250px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            z-index: 998;
            overflow-y: auto;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #555;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            position: relative;
        }
        
        .sidebar-link:hover {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.05);
            text-decoration: none;
        }
        
        .sidebar-link.active {
            color: var(--primary-color);
            background: rgba(50, 189, 234, 0.1);
            font-weight: 500;
        }
        
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .sidebar-icon {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .sidebar-text {
            flex-grow: 1;
            font-size: 14px;
        }
        
        .sidebar-divider {
            height: 1px;
            background: var(--border-color);
            margin: 15px 0;
        }
        
        .sidebar-title {
            padding: 0 20px;
            margin: 15px 0 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 0.5px;
        }
        
        /* Main content styles */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
            min-height: calc(100vh - 60px);
        }
        
        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Category cards */
        .categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .category-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all var(--transition-speed) ease;
            display: flex;
            flex-direction: column;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .category-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .category-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #fff;
        }
        
        .category-icon.autism {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        
        .category-icon.physical {
            background: linear-gradient(135deg, #f6d365, #fda085);
        }
        
        .category-icon.speech {
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
        }
        
        .category-icon.visual {
            background: linear-gradient(135deg, #33ccff, #00c49a);
        }
        
        .category-icon.hearing {
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
        }
        
        .category-icon.learning {
            background: linear-gradient(135deg, #c850c0, #4158d0);
        }
        
        .category-body {
            padding: 20px;
            flex-grow: 1;
        }
        
        .category-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .stat-label {
            font-size: 12px;
            color: #888;
        }
        
        .activities-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-link {
            display: flex;
            align-items: center;
            color: #555;
            text-decoration: none;
            transition: all var(--transition-speed) ease;
        }
        
        .activity-link:hover {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .activity-name {
            flex-grow: 1;
            font-size: 14px;
        }
        
        .activity-difficulty {
            font-size: 12px;
            border-radius: 12px;
            padding: 2px 8px;
            background: #f5f5f5;
        }
        
        .activity-difficulty.easy {
            background-color: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
        }
        
        .activity-difficulty.medium {
            background-color: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
        }
        
        .activity-difficulty.hard {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--danger-color);
        }
        
        .category-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </div>
        
        <div class="topbar-logo">
            <a href="/dashboard">
                <i class="fas fa-clinic-medical"></i>
                <span>CREAMS</span>
            </a>
        </div>
        
        <div class="topbar-title">
            Rehabilitation Categories
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                <img src="/images/default-avatar.png" alt="User Avatar">
            </div>
            <div class="user-info">
                <div class="user-name">Admin User</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/dashboard" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-home"></i></span>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/profile" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-user-circle"></i></span>
                    <span class="sidebar-text">My Profile</span>
                </a>
            </li>
            
            <li class="sidebar-divider"></li>
            
            <li class="sidebar-title">Management</li>
            
            <li class="sidebar-item">
                <a href="/admin/users" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-users"></i></span>
                    <span class="sidebar-text">Staffs</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/traineeshome" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-user-graduate"></i></span>
                    <span class="sidebar-text">Trainees</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/admin/centres" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-building"></i></span>
                    <span class="sidebar-text">Centres</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/rehabilitation/categories" class="sidebar-link active">
                    <span class="sidebar-icon"><i class="fas fa-heartbeat"></i></span>
                    <span class="sidebar-text">Rehabilitation</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/admin/assets" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-boxes"></i></span>
                    <span class="sidebar-text">Assets</span>
                </a>
            </li>
            
            <li class="sidebar-divider"></li>
            
            <li class="sidebar-title">Reports & Settings</li>
            
            <li class="sidebar-item">
                <a href="/admin/reports" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-chart-bar"></i></span>
                    <span class="sidebar-text">Reports</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/admin/settings" class="sidebar-link">
                    <span class="sidebar-icon"><i class="fas fa-cog"></i></span>
                    <span class="sidebar-text">Settings</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-heartbeat mr-2"></i> Rehabilitation Categories
            </h1>
            <div class="page-actions">
                <a href="/rehabilitation/categories/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Activity
                </a>
            </div>
        </div>
        
        <!-- Categories -->
        <div class="categories-container">
            <!-- Autism Spectrum -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon autism">
                            <i class="fas fa-brain"></i>
                        </div>
                        Autism Spectrum
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">12</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">48</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/1" class="activity-link">
                                <span class="activity-name">Social Cue Recognition</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/2" class="activity-link">
                                <span class="activity-name">Sensory Integration Exercise</span>
                                <span class="activity-difficulty easy">Easy</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/3" class="activity-link">
                                <span class="activity-name">Communication Board Usage</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/autism" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            
            <!-- Physical Disabilities -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon physical">
                            <i class="fas fa-walking"></i>
                        </div>
                        Physical Disabilities
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">9</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">36</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">18</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/4" class="activity-link">
                                <span class="activity-name">Motor Skills Development</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/5" class="activity-link">
                                <span class="activity-name">Grip Strength Exercise</span>
                                <span class="activity-difficulty easy">Easy</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/6" class="activity-link">
                                <span class="activity-name">Mobility Training</span>
                                <span class="activity-difficulty hard">Hard</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/physical" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            
            <!-- Speech & Language -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon speech">
                            <i class="fas fa-comments"></i>
                        </div>
                        Speech & Language
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">15</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">45</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">30</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/7" class="activity-link">
                                <span class="activity-name">Articulation Practice</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/8" class="activity-link">
                                <span class="activity-name">Vocabulary Building</span>
                                <span class="activity-difficulty easy">Easy</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/9" class="activity-link">
                                <span class="activity-name">Fluency Training</span>
                                <span class="activity-difficulty hard">Hard</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/speech" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            
            <!-- Visual Impairment -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon visual">
                            <i class="fas fa-eye"></i>
                        </div>
                        Visual Impairment
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">8</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">12</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/10" class="activity-link">
                                <span class="activity-name">Braille Reading</span>
                                <span class="activity-difficulty hard">Hard</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/11" class="activity-link">
                                <span class="activity-name">Tactile Discrimination</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/12" class="activity-link">
                                <span class="activity-name">Orientation & Mobility</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/visual" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            
            <!-- Hearing Impairment -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon hearing">
                            <i class="fas fa-deaf"></i>
                        </div>
                        Hearing Impairment
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">10</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">30</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">15</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/13" class="activity-link">
                                <span class="activity-name">Sign Language Basics</span>
                                <span class="activity-difficulty easy">Easy</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/14" class="activity-link">
                                <span class="activity-name">Lip Reading Practice</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/15" class="activity-link">
                                <span class="activity-name">Auditory Training</span>
                                <span class="activity-difficulty hard">Hard</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/hearing" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            
            <!-- Learning Disabilities -->
            <div class="category-card">
                <div class="category-header">
                    <h2 class="category-title">
                        <div class="category-icon learning">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        Learning Disabilities
                    </h2>
                </div>
                <div class="category-body">
                    <div class="category-stats">
                        <div class="stat-item">
                            <div class="stat-value">14</div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">42</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">28</div>
                            <div class="stat-label">Trainees</div>
                        </div>
                    </div>
                    <ul class="activities-list">
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/16" class="activity-link">
                                <span class="activity-name">Reading Comprehension</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/17" class="activity-link">
                                <span class="activity-name">Math Skills Development</span>
                                <span class="activity-difficulty medium">Medium</span>
                            </a>
                        </li>
                        <li class="activity-item">
                            <a href="/rehabilitation/activities/18" class="activity-link">
                                <span class="activity-name">Memory Enhancement</span>
                                <span class="activity-difficulty easy">Easy</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="category-footer">
                    <a href="/rehabilitation/categories/learning" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script>
    // Simple sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const body = document.body;
        
        sidebarToggle.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
            
            // Update main content margin
            const mainContent = document.querySelector('.main-content');
            if (body.classList.contains('sidebar-collapsed')) {
                mainContent.style.marginLeft = '60px';
            } else {
                mainContent.style.marginLeft = '250px';
            }
        });
        
        // Sidebar collapsed state for mobile
        function handleResize() {
            if (window.innerWidth <= 768) {
                body.classList.add('sidebar-collapsed');
                const mainContent = document.querySelector('.main-content');
                mainContent.style.marginLeft = '60px';
            }
        }
        
        // Call on load
        handleResize();
        
        // Listen for window resize
        window.addEventListener('resize', handleResize);
        
        // Fix avatar images
        const avatarImages = document.querySelectorAll('.user-avatar img');
        avatarImages.forEach(function(img) {
            img.addEventListener('error', function() {
                this.src = '/images/default-avatar.png';
            });
        });
        
        // Animate stats counting
        $('.stat-value').each(function() {
            const $this = $(this);
            const countTo = parseInt($this.text());
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });
        
        // Animate category cards appearance
        $('.category-card').each(function(index) {
            const $this = $(this);
            setTimeout(function() {
                $this.addClass('animated fadeInUp');
            }, 100 * index);
        });

        // Track category and activity clicks
        $('.category-footer a').click(function() {
            const categoryName = $(this).closest('.category-card').find('.category-title').text().trim();
            const categoryUrl = $(this).attr('href');
            
            // Store in recent items
            trackDetailedItem(categoryName, categoryUrl, 'Rehabilitation Category');
        });
        
        $('.activity-link').click(function() {
            const activityName = $(this).find('.activity-name').text().trim();
            const activityUrl = $(this).attr('href');
            
            // Store in recent items
            trackDetailedItem(activityName, activityUrl, 'Rehabilitation Activity');
        });
        
        // Function to track items in localStorage
        function trackDetailedItem(name, url, type) {
            // Create item object
            const detailedItem = {
                name: name,
                url: url,
                type: type,
                timestamp: new Date().toISOString()
            };
            
            // Get existing items from localStorage
            let recentItems = [];
            try {
                recentItems = JSON.parse(localStorage.getItem('recentItems')) || [];
            } catch (e) {
                console.error('Error parsing recent items:', e);
            }
            
            // Check if this item already exists
            const existingItemIndex = recentItems.findIndex(item => item.url === url);
            
            if (existingItemIndex >= 0) {
                // Remove existing item
                recentItems.splice(existingItemIndex, 1);
            }
            
            // Add new item at the beginning
            recentItems.unshift(detailedItem);
            
            // Limit to 10 items
            recentItems = recentItems.slice(0, 10);
            
            // Save back to localStorage
            localStorage.setItem('recentItems', JSON.stringify(recentItems));
        }
    });
</script>
</body>
</html>