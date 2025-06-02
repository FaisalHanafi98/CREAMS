<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activity Details - CREAMS</title>
    
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
        
        /* Activity details styles */
        .activity-container {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        
        .activity-main {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .activity-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            padding: 20px;
            position: relative;
        }
        
        .activity-category {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .activity-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .activity-subtitle {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .activity-details {
            padding: 20px;
        }
        
        .activity-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .description-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .description-content {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }
        
        .objectives-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .objective-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .objective-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .objective-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        .objective-text {
            flex-grow: 1;
            font-size: 14px;
        }
        
        .resources-section {
            margin-bottom: 20px;
        }
        
        .resource-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .resource-tag {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            background-color: var(--light-color);
            border-radius: 15px;
            font-size: 12px;
            color: #555;
        }
        
        .resource-tag i {
            margin-right: 5px;
            color: var(--primary-color);
        }
        
        .activity-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .sidebar-card-header {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--light-color);
        }
        
        .sidebar-card-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
        }
        
        .sidebar-card-body {
            padding: 15px;
        }
        
        .related-activity {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .related-activity:last-child {
            border-bottom: none;
        }
        
        .related-activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
        }
        
        .related-activity-icon.autism {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        
        .related-activity-info {
            flex-grow: 1;
        }
        
        .related-activity-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 2px;
            line-height: 1.2;
        }
        
        .related-activity-difficulty {
            font-size: 12px;
            color: #888;
        }
        
        .milestone-list {
            padding: 0;
            margin: 0;
            list-style-type: none;
        }
        
        .milestone-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .milestone-item:last-child {
            border-bottom: none;
        }
        
        .milestone-check {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid var(--border-color);
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            transition: all var(--transition-speed) ease;
        }
        
        .milestone-check.completed {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .milestone-text {
            font-size: 14px;
            flex-grow: 1;
        }
        
        .progress-section {
            margin-top: 10px;
        }
        
        .progress-wrapper {
            height: 10px;
            background-color: var(--light-color);
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            width: 65%;
            border-radius: 5px;
        }
        
        .progress-text {
            font-size: 12px;
            color: #888;
            text-align: right;
        }
        
        .button-groups {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .button-groups .btn {
            flex: 1;
        }
        
        /* Mobile styles */
        @media (max-width: 991px) {
            .activity-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 767px) {
            .activity-info {
                grid-template-columns: 1fr;
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
            Activity Details
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
                <i class="fas fa-brain mr-2"></i> Activity Details
            </h1>
            <div class="page-actions">
                <a href="/rehabilitation/categories" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
                <a href="/rehabilitation/activities/1/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Activity
                </a>
            </div>
        </div>
        
        <!-- Activity Details -->
        <div class="activity-container">
            <!-- Main Content -->
            <div class="activity-main">
                <div class="activity-header">
                    <div class="activity-category">Autism Spectrum</div>
                    <h1 class="activity-title">Social Cue Recognition</h1>
                    <div class="activity-subtitle">Helps children recognize and interpret social cues and facial expressions</div>
                </div>
                
                <div class="activity-details">
                    <div class="activity-info">
                        <div class="info-item">
                            <div class="info-label">Difficulty Level</div>
                            <div class="info-value">Medium</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Age Range</div>
                            <div class="info-value">6-12 years</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Activity Type</div>
                            <div class="info-value">Group</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Duration</div>
                            <div class="info-value">30-45 minutes</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Created By</div>
                            <div class="info-value">Dr. Nurul Hafizah</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">March 15, 2023</div>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Description</h3>
                        <div class="description-content">
                            <p>This activity is designed to help children with autism spectrum disorders to better recognize and interpret social cues and facial expressions. Using a combination of visual aids, roleplay, and interactive games, children learn to identify different emotions and appropriate social responses.</p>
                            <p>The activity is structured to provide gradual exposure and practice in a supportive environment, with opportunities for immediate feedback and reinforcement.</p>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Learning Objectives</h3>
                        <ul class="objectives-list">
                            <li class="objective-item">
                                <div class="objective-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="objective-text">
                                    Recognize and identify basic emotions from facial expressions (happy, sad, angry, scared, surprised)
                                </div>
                            </li>
                            <li class="objective-item">
                                <div class="objective-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="objective-text">
                                    Understand the connection between emotions and corresponding social situations
                                </div>
                            </li>
                            <li class="objective-item">
                                <div class="objective-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="objective-text">
                                    Develop appropriate responses to different social cues
                                </div>
                            </li>
                            <li class="objective-item">
                                <div class="objective-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="objective-text">
                                    Practice initiating and maintaining simple social interactions
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Required Resources</h3>
                        <div class="resource-list">
                            <span class="resource-tag">
                                <i class="fas fa-image"></i> Emotion Flashcards
                            </span>
                            <span class="resource-tag">
                                <i class="fas fa-tv"></i> Video Materials
                            </span>
                            <span class="resource-tag">
                                <i class="fas fa-dice"></i> Social Scenario Cards
                            </span>
                            <span class="resource-tag">
                                <i class="fas fa-puzzle-piece"></i> Emotion Matching Game
                            </span>
                            <span class="resource-tag">
                                <i class="fas fa-book"></i> Social Stories Book
                            </span>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Implementation Steps</h3>
                        <div class="description-content">
                            <ol>
                                <li><strong>Introduction (5 minutes):</strong> Begin with a brief introduction about emotions and why understanding them is important in our daily interactions.</li>
                                <li><strong>Emotion Recognition (10 minutes):</strong> Use flashcards to help children identify basic emotions. Ask them to mimic the expressions shown on the cards.</li>
                                <li><strong>Social Scenarios (15 minutes):</strong> Present different social scenarios and ask children to identify appropriate emotional responses. Use visual aids and role-playing to reinforce learning.</li>
                                <li><strong>Interactive Game (10 minutes):</strong> Play an emotion matching game where children match situations with appropriate emotional responses.</li>
                                <li><strong>Reflection and Feedback (5 minutes):</strong> Discuss what children learned and provide positive reinforcement for participation and progress.</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Adaptations for Different Abilities</h3>
                        <div class="description-content">
                            <p><strong>For Lower Functioning:</strong> Focus on identifying only 2-3 basic emotions with more visual supports and physical prompting. Increase session frequency rather than duration.</p>
                            <p><strong>For Higher Functioning:</strong> Include more complex emotions (such as confused, embarrassed, proud) and more nuanced social scenarios. Add perspective-taking components.</p>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 class="section-title">Progress Tracking</h3>
                        <div class="description-content">
                            <p>Track progress through observation checklists that measure:</p>
                            <ul>
                                <li>Accuracy in identifying emotions (baseline vs. follow-up)</li>
                                <li>Response time to social cues</li>
                                <li>Frequency of appropriate social initiations</li>
                                <li>Generalization to natural settings (reports from teachers/parents)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="activity-sidebar">
                <!-- Related Activities -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h3 class="sidebar-card-title">Related Activities</h3>
                    </div>
                    <div class="sidebar-card-body">
                        <div class="related-activity">
                            <div class="related-activity-icon autism">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="related-activity-info">
                                <div class="related-activity-name">Conversation Skills Practice</div>
                                <div class="related-activity-difficulty">Medium</div>
                            </div>
                        </div>
                        <div class="related-activity">
                            <div class="related-activity-icon autism">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <div class="related-activity-info">
                                <div class="related-activity-name">Emotional Regulation</div>
                                <div class="related-activity-difficulty">Hard</div>
                            </div>
                        </div>
                        <div class="related-activity">
                            <div class="related-activity-icon autism">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="related-activity-info">
                                <div class="related-activity-name">Group Play Skills</div>
                                <div class="related-activity-difficulty">Easy</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Learning Milestones -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h3 class="sidebar-card-title">Learning Milestones</h3>
                    </div>
                    <div class="sidebar-card-body">
                        <ul class="milestone-list">
                            <li class="milestone-item">
                                <div class="milestone-check completed">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="milestone-text">Recognize basic emotions</div>
                            </li>
                            <li class="milestone-item">
                                <div class="milestone-check completed">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="milestone-text">Match emotions to situations</div>
                            </li>
                            <li class="milestone-item">
                                <div class="milestone-check">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="milestone-text">Respond appropriately to emotions</div>
                            </li>
                            <li class="milestone-item">
                                <div class="milestone-check">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="milestone-text">Initiate social interactions</div>
                            </li>
                        </ul>
                        
                        <div class="progress-section">
                            <div class="progress-wrapper">
                                <div class="progress-bar"></div>
                            </div>
                            <div class="progress-text">65% Complete</div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Usage Stats -->
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h3 class="sidebar-card-title">Activity Statistics</h3>
                    </div>
                    <div class="sidebar-card-body">
                        <div class="info-item mb-2">
                            <div class="info-label">Total Sessions</div>
                            <div class="info-value">48</div>
                        </div>
                        <div class="info-item mb-2">
                            <div class="info-label">Trainees</div>
                            <div class="info-value">24</div>
                        </div>
                        <div class="info-item mb-2">
                            <div class="info-label">Avg. Session Duration</div>
                            <div class="info-value">38 minutes</div>
                        </div>
                        <div class="info-item mb-2">
                            <div class="info-label">Success Rate</div>
                            <div class="info-value">78%</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Used</div>
                            <div class="info-value">Yesterday</div>
                        </div>
                        
                        <div class="button-groups">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-alt"></i> Full Report
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    </div>
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
            
            // Make milestone items clickable
            const milestoneItems = document.querySelectorAll('.milestone-item');
            milestoneItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const check = this.querySelector('.milestone-check');
                    check.classList.toggle('completed');
                    
                    // Update progress based on completed milestones
                    updateProgress();
                });
            });
            
            // Update progress bar based on completed milestones
            function updateProgress() {
                const totalMilestones = document.querySelectorAll('.milestone-item').length;
                const completedMilestones = document.querySelectorAll('.milestone-check.completed').length;
                const progressPercent = Math.round((completedMilestones / totalMilestones) * 100);
                
                // Update progress bar width
                document.querySelector('.progress-bar').style.width = progressPercent + '%';
                
                // Update progress text
                document.querySelector('.progress-text').textContent = progressPercent + '% Complete';
            }
        });
    </script>