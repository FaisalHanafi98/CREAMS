<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trainee Profile - {{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .profile-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .profile-section h2 {
            margin: 0 0 15px 0;
            color: #007bff;
            font-size: 16px;
            border-bottom: 1px solid #007bff;
            padding-bottom: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .condition-badge {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            display: inline-block;
        }
        
        .activities-list {
            list-style: none;
            padding: 0;
        }
        
        .activities-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activities-list li:last-child {
            border-bottom: none;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
            float: right;
            margin-left: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            text-align: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CREAMS - Trainee Profile</h1>
        <p>Community-based REhAbilitation Management System</p>
        <p>Generated on: {{ $downloadDate }}</p>
    </div>

    <!-- Personal Information Section -->
    <div class="profile-section">
        <h2>Personal Information</h2>
        
        @if($trainee->avatar_url)
            <img src="{{ public_path($trainee->avatar_url) }}" alt="Trainee Avatar" class="avatar">
        @endif
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Full Name:</span>
                <span class="info-value">{{ $trainee->trainee_first_name }} {{ $trainee->trainee_last_name }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Age:</span>
                <span class="info-value">{{ $age }} years old</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Gender:</span>
                <span class="info-value">{{ $trainee->gender ?? 'Not specified' }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Date of Birth:</span>
                <span class="info-value">{{ $trainee->trainee_date_of_birth ? $trainee->trainee_date_of_birth->format('d M Y') : 'Not specified' }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $trainee->trainee_email }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $trainee->trainee_phone_number ?? 'Not specified' }}</span>
            </div>
            
            <div class="info-item full-width">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $trainee->trainee_address ?? 'Not specified' }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Centre:</span>
                <span class="info-value">{{ $trainee->centre_name ?? 'Not specified' }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Condition:</span>
                <span class="info-value">
                    <span class="condition-badge">{{ $trainee->trainee_condition }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="profile-section">
        <h2>Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number">{{ $totalActivities }}</div>
                <div class="stat-label">Total Activities</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $trainee->created_at->diffInDays() }}</div>
                <div class="stat-label">Days Enrolled</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $trainee->status ? ucfirst($trainee->status) : 'Active' }}</div>
                <div class="stat-label">Status</div>
            </div>
        </div>
    </div>

    <!-- Guardian Information Section -->
    <div class="profile-section">
        <h2>Guardian Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $guardian['name'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Relationship:</span>
                <span class="info-value">{{ $guardian['relationship'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $guardian['phone'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $guardian['email'] }}</span>
            </div>
            
            <div class="info-item full-width">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $guardian['address'] }}</span>
            </div>
        </div>
    </div>

    <!-- Emergency Contact Section -->
    @if($trainee->emergency_contact_name)
    <div class="profile-section">
        <h2>Emergency Contact</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $trainee->emergency_contact_name }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Relationship:</span>
                <span class="info-value">{{ $trainee->emergency_contact_relationship ?? 'Not specified' }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $trainee->emergency_contact_phone ?? 'Not specified' }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Medical Information Section -->
    @if($trainee->medical_history || $trainee->additional_notes)
    <div class="profile-section">
        <h2>Medical Information</h2>
        
        @if($trainee->medical_history)
        <div class="info-item full-width" style="margin-bottom: 15px;">
            <span class="info-label">Medical History:</span>
            <div class="info-value">{{ $trainee->medical_history }}</div>
        </div>
        @endif
        
        @if($trainee->additional_notes)
        <div class="info-item full-width">
            <span class="info-label">Additional Notes:</span>
            <div class="info-value">{{ $trainee->additional_notes }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Activities Section -->
    @if($trainee->activities && $trainee->activities->count() > 0)
    <div class="profile-section">
        <h2>Recent Activities</h2>
        <ul class="activities-list">
            @foreach($trainee->activities->take(10) as $activity)
            <li>
                <strong>{{ $activity->activity_name }}</strong>
                <span style="float: right; color: #666;">
                    {{ $activity->created_at->format('d M Y') }}
                </span>
                <br>
                <small style="color: #666;">
                    Type: {{ $activity->activity_type ?? 'N/A' }}
                    @if($activity->activity_description)
                    | {{ Str::limit($activity->activity_description, 100) }}
                    @endif
                </small>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="footer">
        <p>This document was generated by CREAMS (Community-based REhAbilitation Management System)</p>
        <p>Generated by: {{ session('name') }} ({{ session('role') }}) on {{ $downloadDate }}</p>
        <p>© {{ date('Y') }} IIUM & JKM Malaysia. All rights reserved.</p>
    </div>
</body>
</html>