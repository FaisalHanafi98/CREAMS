<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Rehabilitation Activity - CREAMS</title>
    
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
        
        /* Form styles */
        .form-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            padding: 20px;
            position: relative;
        }
        
        .form-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        
        .form-subtitle {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .form-body {
            padding: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--dark-color);
        }
        
        .form-row {
            margin-bottom: 15px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark-color);
        }
        
        .custom-select,
        .form-control {
            height: auto;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all var(--transition-speed) ease;
        }
        
        .custom-select:focus,
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(50, 189, 234, 0.25);
        }
        
        .input-group-text {
            background-color: var(--light-bg);
            border-color: var(--border-color);
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .form-text {
            font-size: 12px;
            color: #888;
        }
        
        .custom-control-label {
            font-weight: normal;
        }
        
        .add-item-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            padding: 10px 15px;
            color: var(--primary-color);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }
        
        .add-item-btn:hover {
            background-color: rgba(50, 189, 234, 0.05);
        }
        
        .add-item-btn i {
            margin-right: 8px;
        }
        
        .resource-item {
            display: flex;
            align-items: center;
            background: var(--light-bg);
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        
        .resource-icon {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .resource-text {
            flex-grow: 1;
        }
        
        .resource-remove {
            color: var(--danger-color);
            cursor: pointer;
            padding: 5px;
        }
        
        .objective-item {
            display: flex;
            align-items: flex-start;
            background: var(--light-bg);
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        
        .objective-number {
            min-width: 24px;
            height: 24px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .objective-input {
            flex-grow: 1;
        }
        
        .objective-remove {
            color: var(--danger-color);
            cursor: pointer;
            padding: 5px;
        }
        
        .implementation-step {
            display: flex;
            align-items: flex-start;
            background: var(--light-bg);
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        
        .step-number {
            min-width: 24px;
            height: 24px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .step-inputs {
            flex-grow: 1;
        }
        
        .step-title-input {
            margin-bottom: 5px;
        }
        
        .step-remove {
            color: var(--danger-color);
            cursor: pointer;
            padding: 5px;
        }
        
        .form-footer {
            padding: 20px;
            background-color: var(--light-bg);
            border-top: 1px solid var(--border-color);
            text-align: right;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary {
            color: #666;
            border-color: #ccc;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f5f5f5;
            color: #333;
        }
        
        /* Media queries for responsive design */
        @media (max-width: 991px) {
            .main-content {
                margin-left: 60px;
            }
            
            body {
                overflow-x: hidden;
            }
            
            .sidebar {
                width: 60px;
            }
            
            .sidebar-text {
                display: none;
            }
            
            .sidebar-icon {
                margin-right: 0;
            }
            
            .sidebar-link {
                justify-content: center;
                padding: 15px;
            }
        }
        
        @media (max-width: 767px) {
            .form-section {
                padding: 15px;
            }
            
            .form-header {
                padding: 15px;
            }
            
            .form-footer {
                padding: 15px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
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
            Create Activity
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
                <i class="fas fa-plus-circle mr-2"></i> Create Rehabilitation Activity
            </h1>
            <div class="page-actions">
                <a href="/rehabilitation/categories" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
        
        <!-- Create Activity Form -->
        <form action="/rehabilitation/activities" method="POST" class="needs-validation" novalidate>
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">New Rehabilitation Activity</h2>
                    <p class="form-subtitle">Create a new activity for trainees with specific needs</p>
                </div>
                
                <div class="form-body">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="activity_name" class="form-label">Activity Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activity_name" name="activity_name" required>
                                <div class="invalid-feedback">Please provide an activity name.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="custom-select" id="category" name="category" required>
                                    <option value="" selected disabled>Select a category</option>
                                    <option value="autism">Autism Spectrum</option>
                                    <option value="physical">Physical Disabilities</option>
                                    <option value="speech">Speech & Language</option>
                                    <option value="visual">Visual Impairment</option>
                                    <option value="hearing">Hearing Impairment</option>
                                    <option value="learning">Learning Disabilities</option>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-12">
                                <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2" required></textarea>
                                <div class="invalid-feedback">Please provide a short description.</div>
                                <small class="form-text text-muted">A brief summary of the activity's purpose (50-100 characters)</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-4">
                                <label for="difficulty_level" class="form-label">Difficulty Level <span class="text-danger">*</span></label>
                                <select class="custom-select" id="difficulty_level" name="difficulty_level" required>
                                    <option value="" selected disabled>Select difficulty</option>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                                <div class="invalid-feedback">Please select a difficulty level.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="age_range" class="form-label">Age Range <span class="text-danger">*</span></label>
                                <select class="custom-select" id="age_range" name="age_range" required>
                                    <option value="" selected disabled>Select age range</option>
                                    <option value="0-3">0-3 years</option>
                                    <option value="4-6">4-6 years</option>
                                    <option value="7-10">7-10 years</option>
                                    <option value="11-14">11-14 years</option>
                                    <option value="15-18">15-18 years</option>
                                    <option value="all">All ages</option>
                                </select>
                                <div class="invalid-feedback">Please select an age range.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="activity_type" class="form-label">Activity Type <span class="text-danger">*</span></label>
                                <select class="custom-select" id="activity_type" name="activity_type" required>
                                    <option value="" selected disabled>Select type</option>
                                    <option value="individual">Individual</option>
                                    <option value="group">Group</option>
                                    <option value="both">Both</option>
                                </select>
                                <div class="invalid-feedback">Please select an activity type.</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="duration" name="duration" min="5" max="120" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">minutes</span>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Please specify a valid duration (5-120 minutes).</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="max_participants" class="form-label">Maximum Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" max="20">
                                <small class="form-text text-muted">For group activities only</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detailed Description Section -->
                    <div class="form-section">
                        <h3 class="section-title">Detailed Description</h3>
                        
                        <div class="form-row">
                            <div class="col-md-12">
                                <label for="full_description" class="form-label">Full Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="full_description" name="full_description" rows="5" required></textarea>
                                <div class="invalid-feedback">Please provide a detailed description.</div>
                                <small class="form-text text-muted">Include the purpose, benefits, and general approach of the activity</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Learning Objectives Section -->
                    <div class="form-section">
                        <h3 class="section-title">Learning Objectives</h3>
                        <p class="mb-3">Add specific, measurable objectives that the activity aims to achieve</p>
                        
                        <div id="objectives-container">
                            <div class="objective-item">
                                <div class="objective-number">1</div>
                                <div class="objective-input">
                                    <input type="text" class="form-control" name="objectives[]" placeholder="Enter an objective" required>
                                </div>
                                <div class="objective-remove">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="add-item-btn mt-3" id="add-objective">
                            <i class="fas fa-plus"></i> Add Another Objective
                        </div>
                    </div>
                    
                    <!-- Required Resources Section -->
                    <div class="form-section">
                        <h3 class="section-title">Required Resources</h3>
                        <p class="mb-3">List all materials and resources needed for this activity</p>
                        
                        <div id="resources-container">
                            <div class="resource-item">
                                <div class="resource-icon">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div class="resource-text">
                                    <input type="text" class="form-control" name="resources[]" placeholder="Enter a resource" required>
                                </div>
                                <div class="resource-remove">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="add-item-btn mt-3" id="add-resource">
                            <i class="fas fa-plus"></i> Add Another Resource
                        </div>
                    </div>
                    
                    <!-- Implementation Steps Section -->
                    <div class="form-section">
                        <h3 class="section-title">Implementation Steps</h3>
                        <p class="mb-3">Provide step-by-step instructions for implementing this activity</p>
                        
                        <div id="steps-container">
                            <div class="implementation-step">
                                <div class="step-number">1</div>
                                <div class="step-inputs">
                                    <div class="step-title-input">
                                        <input type="text" class="form-control" name="step_titles[]" placeholder="Step title (e.g., 'Introduction')" required>
                                    </div>
                                    <textarea class="form-control" name="step_descriptions[]" rows="2" placeholder="Step description" required></textarea>
                                </div>
                                <div class="step-remove">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="add-item-btn mt-3" id="add-step">
                            <i class="fas fa-plus"></i> Add Another Step
                        </div>
                    </div>
                    
                    <!-- Adaptations Section -->
                    <div class="form-section">
                        <h3 class="section-title">Adaptations</h3>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="lower_adaptations" class="form-label">For Lower Functioning</label>
                                <textarea class="form-control" id="lower_adaptations" name="lower_adaptations" rows="3"></textarea>
                                <small class="form-text text-muted">How to simplify this activity for trainees with more severe disabilities</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="higher_adaptations" class="form-label">For Higher Functioning</label>
                                <textarea class="form-control" id="higher_adaptations" name="higher_adaptations" rows="3"></textarea>
                                <small class="form-text text-muted">How to make this activity more challenging for advanced trainees</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Tracking Section -->
                    <div class="form-section">
                        <h3 class="section-title">Progress Tracking</h3>
                        
                        <div class="form-row">
                            <div class="col-md-12">
                                <label for="progress_metrics" class="form-label">Progress Tracking Methods <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="progress_metrics" name="progress_metrics" rows="3" required></textarea>
                                <div class="invalid-feedback">Please specify how progress will be tracked.</div>
                                <small class="form-text text-muted">Describe specific metrics, observations, or assessments to track trainee progress</small>
                            </div>
                        </div>
                        
                        <div class="form-row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">Milestones</label>
                                <div id="milestones-container">
                                    <div class="resource-item">
                                        <div class="resource-icon">
                                            <i class="fas fa-flag"></i>
                                        </div>
                                        <div class="resource-text">
                                            <input type="text" class="form-control" name="milestones[]" placeholder="Enter a milestone" required>
                                        </div>
                                        <div class="resource-remove">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="add-item-btn mt-3" id="add-milestone">
                                    <i class="fas fa-plus"></i> Add Another Milestone
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Additional Information</h3>
                        
                        <div class="form-row">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                <small class="form-text text-muted">Any additional information or tips for implementing this activity</small>
                            </div>
                        </div>
                        
                        <div class="form-row mt-3">
                            <div class="col-md-12">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="published" name="published" checked>
                                    <label class="custom-control-label" for="published">Publish this activity immediately</label>
                                </div>
                                <small class="form-text text-muted">Uncheck to save as draft</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='/rehabilitation/categories'">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Activity
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple sidebar toggle
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
            
            // Form validation
            const form = document.querySelector('.needs-validation');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
            
            // Dynamic form elements
            
            // Add objective
            const addObjectiveBtn = document.getElementById('add-objective');
            const objectivesContainer = document.getElementById('objectives-container');
            
            addObjectiveBtn.addEventListener('click', function() {
                const objectiveCount = objectivesContainer.querySelectorAll('.objective-item').length + 1;
                const newObjective = document.createElement('div');
                newObjective.className = 'objective-item';
                newObjective.innerHTML = `
                    <div class="objective-number">${objectiveCount}</div>
                    <div class="objective-input">
                        <input type="text" class="form-control" name="objectives[]" placeholder="Enter an objective" required>
                    </div>
                    <div class="objective-remove">
                        <i class="fas fa-times"></i>
                    </div>
                `;
                objectivesContainer.appendChild(newObjective);
                
                // Add remove functionality
                newObjective.querySelector('.objective-remove').addEventListener('click', function() {
                    removeItem(newObjective, objectivesContainer, '.objective-item', '.objective-number');
                });
            });
            
            // Add resource
            const addResourceBtn = document.getElementById('add-resource');
            const resourcesContainer = document.getElementById('resources-container');
            
            addResourceBtn.addEventListener('click', function() {
                const newResource = document.createElement('div');
                newResource.className = 'resource-item';
                newResource.innerHTML = `
                    <div class="resource-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="resource-text">
                        <input type="text" class="form-control" name="resources[]" placeholder="Enter a resource" required>
                    </div>
                    <div class="resource-remove">
                        <i class="fas fa-times"></i>
                    </div>
                `;
                resourcesContainer.appendChild(newResource);
                
                // Add remove functionality
                newResource.querySelector('.resource-remove').addEventListener('click', function() {
                    newResource.remove();
                });
            });
            
            // Add implementation step
            const addStepBtn = document.getElementById('add-step');
            const stepsContainer = document.getElementById('steps-container');
            
            addStepBtn.addEventListener('click', function() {
                const stepCount = stepsContainer.querySelectorAll('.implementation-step').length + 1;
                const newStep = document.createElement('div');
                newStep.className = 'implementation-step';
                newStep.innerHTML = `
                    <div class="step-number">${stepCount}</div>
                    <div class="step-inputs">
                        <div class="step-title-input">
                            <input type="text" class="form-control" name="step_titles[]" placeholder="Step title (e.g., 'Introduction')" required>
                        </div>
                        <textarea class="form-control" name="step_descriptions[]" rows="2" placeholder="Step description" required></textarea>
                    </div>
                    <div class="step-remove">
                        <i class="fas fa-times"></i>
                    </div>
                `;
                stepsContainer.appendChild(newStep);
                
                // Add remove functionality
                newStep.querySelector('.step-remove').addEventListener('click', function() {
                    removeItem(newStep, stepsContainer, '.implementation-step', '.step-number');
                });
            });
            
            // Add milestone
            const addMilestoneBtn = document.getElementById('add-milestone');
            const milestonesContainer = document.getElementById('milestones-container');
            
            addMilestoneBtn.addEventListener('click', function() {
                const newMilestone = document.createElement('div');
                newMilestone.className = 'resource-item';
                newMilestone.innerHTML = `
                    <div class="resource-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="resource-text">
                        <input type="text" class="form-control" name="milestones[]" placeholder="Enter a milestone" required>
                    </div>
                    <div class="resource-remove">
                        <i class="fas fa-times"></i>
                    </div>
                `;
                milestonesContainer.appendChild(newMilestone);
                
                // Add remove functionality
                newMilestone.querySelector('.resource-remove').addEventListener('click', function() {
                    newMilestone.remove();
                });
            });
            
            // Initial remove buttons functionality
            document.querySelectorAll('.objective-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = this.closest('.objective-item');
                    removeItem(item, objectivesContainer, '.objective-item', '.objective-number');
                });
            });
            
            document.querySelectorAll('.resource-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = this.closest('.resource-item');
                    item.remove();
                });
            });
            
            document.querySelectorAll('.step-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = this.closest('.implementation-step');
                    removeItem(item, stepsContainer, '.implementation-step', '.step-number');
                });
            });
            
            // Helper function to remove items and renumber
            function removeItem(item, container, itemSelector, numberSelector) {
                item.remove();
                
                // Renumber remaining items
                const items = container.querySelectorAll(itemSelector);
                items.forEach((item, index) => {
                    const numberElement = item.querySelector(numberSelector);
                    if (numberElement) {
                        numberElement.textContent = index + 1;
                    }
                });
            }
            
            // Fix avatar images
            const avatarImages = document.querySelectorAll('.user-avatar img');
            avatarImages.forEach(function(img) {
                img.addEventListener('error', function() {
                    this.src = '/images/default-avatar.png';
                });
            });
        });
    </script>