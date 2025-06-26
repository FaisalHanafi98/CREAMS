@extends('layouts.app')

@section('title', 'Edit ' . $staffMember->name . ' - Staff Profile | CREAMS')

@section('styles')
<style>
    :root {
        --primary-color: #32bdea;
        --secondary-color: #25a6cf;
        --success-color: #1cc88a;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-color: #2c3e50;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .edit-header {
        background: linear-gradient(135deg, var(--warning-color), #e67e22);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2.5rem;
        margin-bottom: 2rem;
        border: none;
    }

    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid var(--primary-color);
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--secondary-color);
    }

    .form-floating {
        margin-bottom: 1.5rem;
    }

    .form-floating > .form-control {
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-floating > .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(50, 189, 234, 0.25);
    }

    .form-floating > label {
        color: #6c757d;
        font-weight: 500;
    }

    .form-select {
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(50, 189, 234, 0.25);
    }

    .avatar-upload {
        text-align: center;
        margin-bottom: 2rem;
    }

    .current-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid var(--primary-color);
        object-fit: cover;
        margin-bottom: 1rem;
    }

    .upload-btn {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .upload-btn:hover {
        background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(50, 189, 234, 0.4);
    }

    .role-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        display: inline-block;
        margin: 0.25rem;
    }

    .role-admin { background: linear-gradient(45deg, #e74a3b, #c0392b); color: white; }
    .role-supervisor { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }
    .role-teacher { background: linear-gradient(45deg, #1cc88a, #17a673); color: white; }
    .role-ajk { background: linear-gradient(45deg, #3498db, #2980b9); color: white; }

    .action-btn {
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin: 0.5rem;
        cursor: pointer;
    }

    .btn-save {
        background: linear-gradient(45deg, var(--success-color), #17a673);
        color: white;
    }

    .btn-save:hover {
        background: linear-gradient(45deg, #17a673, #138f62);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
    }

    .btn-cancel {
        background: linear-gradient(45deg, #6c757d, #5a6268);
        color: white;
    }

    .btn-cancel:hover {
        background: linear-gradient(45deg, #5a6268, #495057);
        color: white;
        transform: translateY(-2px);
    }

    .btn-view {
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-view:hover {
        background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        color: white;
        transform: translateY(-2px);
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: var(--danger-color);
    }

    .form-control.is-invalid {
        border-color: var(--danger-color);
    }

    .alert {
        border-radius: 10px;
        border: none;
        margin-bottom: 2rem;
    }

    .alert-success {
        background: linear-gradient(45deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(45deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 1.5rem;
        }
        
        .action-btn {
            width: 100%;
            margin: 0.25rem 0;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teachershome') }}">Staff Directory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.view', $staffMember->id) }}">{{ $staffMember->name }}</a></li>
            <li class="breadcrumb-item active">Edit Profile</li>
        </ol>
    </nav>

    <!-- Edit Header -->
    <div class="edit-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-edit me-3"></i>Edit Staff Profile
                    </h1>
                    <p class="mb-0">Update information for {{ $staffMember->name }}</p>
                </div>
                <div class="col-md-4 text-center">
                    <span class="role-badge role-{{ strtolower($staffMember->role) }}">{{ ucfirst($staffMember->role) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('staff.update', $staffMember->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Personal Information Section -->
                <div class="form-card">
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $staffMember->name) }}" 
                                           placeholder="Full Name" required>
                                    <label for="name">Full Name</label>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $staffMember->email) }}" 
                                           placeholder="Email Address" required>
                                    <label for="email">Email Address</label>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $staffMember->phone) }}" 
                                           placeholder="Phone Number">
                                    <label for="phone">Phone Number</label>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $staffMember->date_of_birth ? \Carbon\Carbon::parse($staffMember->date_of_birth)->format('Y-m-d') : '') }}" 
                                           placeholder="Date of Birth">
                                    <label for="date_of_birth">Date of Birth</label>
                                    @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-floating">
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" placeholder="Address" 
                                      style="height: 100px;">{{ old('address', $staffMember->address) }}</textarea>
                            <label for="address">Address</label>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating">
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" placeholder="Bio / Qualifications" 
                                      style="height: 120px;">{{ old('bio', $staffMember->bio) }}</textarea>
                            <label for="bio">Bio / Qualifications</label>
                            @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-briefcase me-2"></i>Professional Information
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('iium_id') is-invalid @enderror" 
                                           id="iium_id" name="iium_id" value="{{ old('iium_id', $staffMember->iium_id) }}" 
                                           placeholder="IIUM ID" required>
                                    <label for="iium_id">IIUM ID</label>
                                    @error('iium_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label fw-bold">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach(['admin', 'supervisor', 'teacher', 'ajk'] as $role)
                                        <option value="{{ $role }}" 
                                                {{ old('role', $staffMember->role) === $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="centre_id" class="form-label fw-bold">Centre Assignment</label>
                            <select class="form-select @error('centre_id') is-invalid @enderror" 
                                    id="centre_id" name="centre_id" required>
                                <option value="">Select Centre</option>
                                @foreach($centres as $centre)
                                <option value="{{ $centre->centre_id }}" 
                                        {{ old('centre_id', $staffMember->centre_id) === $centre->centre_id ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('centre_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center">
                        <button type="submit" class="action-btn btn-save">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('staff.view', $staffMember->id) }}" class="action-btn btn-view">
                            <i class="fas fa-eye me-2"></i>View Profile
                        </a>
                        <a href="{{ route('teachershome') }}" class="action-btn btn-cancel">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Avatar Upload -->
                <div class="form-card">
                    <h3 class="section-title">
                        <i class="fas fa-camera me-2"></i>Profile Picture
                    </h3>
                    
                    <div class="avatar-upload">
                        @if($staffMember->avatar)
                            <img src="{{ asset('storage/avatars/' . $staffMember->avatar) }}" 
                                 alt="{{ $staffMember->name }}" class="current-avatar" id="avatar-preview">
                        @else
                            <div class="current-avatar bg-light d-flex align-items-center justify-content-center" id="avatar-preview">
                                <i class="fas fa-user fa-2x text-muted"></i>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <input type="file" id="avatar" name="avatar" accept="image/*" 
                                   class="d-none" onchange="previewAvatar(this)">
                            <label for="avatar" class="upload-btn">
                                <i class="fas fa-upload me-2"></i>Change Picture
                            </label>
                        </div>
                        
                        <small class="text-muted d-block mt-2">
                            Recommended: Square image, max 2MB
                        </small>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="form-card">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle me-2"></i>Current Information
                    </h3>
                    
                    <div class="mb-3">
                        <strong>Current Role:</strong><br>
                        <span class="role-badge role-{{ strtolower($staffMember->role) }}">
                            {{ ucfirst($staffMember->role) }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Member Since:</strong><br>
                        {{ \Carbon\Carbon::parse($staffMember->created_at)->format('F Y') }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Last Updated:</strong><br>
                        {{ \Carbon\Carbon::parse($staffMember->updated_at)->diffForHumans() }}
                    </div>
                    
                    @if($staffMember->centre_id && isset($centre))
                    <div class="mb-3">
                        <strong>Current Centre:</strong><br>
                        {{ $centre->centre_name }}
                    </div>
                    @endif
                </div>

                <!-- Help & Tips -->
                <div class="form-card">
                    <h3 class="section-title">
                        <i class="fas fa-lightbulb me-2"></i>Tips
                    </h3>
                    
                    <div class="small text-muted">
                        <p><i class="fas fa-check text-success me-1"></i> 
                           Use professional profile pictures</p>
                        <p><i class="fas fa-check text-success me-1"></i> 
                           Keep contact information updated</p>
                        <p><i class="fas fa-check text-success me-1"></i> 
                           Include relevant qualifications in bio</p>
                        <p><i class="fas fa-check text-success me-1"></i> 
                           Ensure centre assignment is correct</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'current-avatar';
                img.alt = 'Preview';
                img.id = 'avatar-preview';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation feedback
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        document.querySelector('.form-card').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
});

// Real-time validation
document.querySelectorAll('.form-control, .form-select').forEach(field => {
    field.addEventListener('blur', function() {
        if (this.hasAttribute('required') && !this.value.trim()) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
@endsection