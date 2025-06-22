@extends('layouts.app')

@section('title', 'Trainee Registration')

@section('styles')
<style>
    .registration-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .progress-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 2rem;
        color: white;
        position: relative;
    }
    
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
        position: relative;
    }
    
    .progress-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255,255,255,0.3);
        z-index: 1;
    }
    
    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 2;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        margin: 0 auto 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle {
        background: white;
        color: var(--primary-color);
        transform: scale(1.1);
    }
    
    .step.completed .step-circle {
        background: #2ed573;
    }
    
    .step-label {
        font-size: 0.875rem;
        opacity: 0.8;
    }
    
    .step.active .step-label {
        opacity: 1;
        font-weight: 500;
    }
    
    .form-content {
        padding: 2rem;
    }
    
    .form-section {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .form-section.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(50, 189, 234, 0.1);
    }
    
    .photo-upload {
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .photo-upload:hover {
        border-color: var(--primary-color);
        background: #f7fafc;
    }
    
    .photo-upload.has-image {
        padding: 1rem;
    }
    
    .upload-preview {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        margin: 0 auto;
        object-fit: cover;
    }
    
    .condition-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .condition-card {
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .condition-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .condition-card.selected {
        border-color: var(--primary-color);
        background: rgba(50, 189, 234, 0.05);
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .summary-section {
        background: #f7fafc;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .summary-title {
        font-weight: 600;
        margin-bottom: 1rem;
        color: #2d3748;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #718096;
    }
    
    .summary-value {
        font-weight: 500;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .required {
        color: #e53e3e;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .registration-container {
            margin: 1rem;
        }
        
        .progress-steps {
            font-size: 0.875rem;
        }
        
        .step-circle {
            width: 32px;
            height: 32px;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Trainee Registration</h1>
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">></span>
        <a href="{{ route('traineeshome') }}">Trainees</a>
        <span class="separator">></span>
        <span class="current">Registration</span>
    </div>
</div>

<div class="registration-container">
    <div class="progress-header">
        <h1>Trainee Registration</h1>
        <div class="progress-steps">
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Basic Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Medical Info</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Guardian Info</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Additional Info</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-circle">5</div>
                <div class="step-label">Review</div>
            </div>
        </div>
    </div>
    
    <form class="form-content" id="traineeRegistrationForm" action="{{ route('traineesregistrationstore') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="form-section active" data-section="1">
            <h2>Basic Information</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="trainee_first_name" class="form-control @error('trainee_first_name') is-invalid @enderror" value="{{ old('trainee_first_name') }}" required>
                    @error('trainee_first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="trainee_last_name" class="form-control @error('trainee_last_name') is-invalid @enderror" value="{{ old('trainee_last_name') }}" required>
                    @error('trainee_last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth <span class="required">*</span></label>
                    <input type="date" name="trainee_date_of_birth" class="form-control @error('trainee_date_of_birth') is-invalid @enderror" value="{{ old('trainee_date_of_birth') }}" max="{{ date('Y-m-d') }}" required>
                    @error('trainee_date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Gender <span class="required">*</span></label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label>Photo</label>
                <div class="photo-upload" onclick="document.getElementById('photo').click()">
                    <input type="file" id="photo" name="trainee_avatar" accept="image/*" style="display: none;" onchange="previewPhoto(this)">
                    <div id="uploadText">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e0" stroke-width="2" style="margin: 0 auto;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <p style="margin-top: 1rem; color: #718096;">Click to upload photo<br><small>JPG, PNG up to 2MB</small></p>
                    </div>
                    <img id="photoPreview" class="upload-preview" style="display: none;">
                </div>
                @error('trainee_avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="trainee_email" class="form-control @error('trainee_email') is-invalid @enderror" value="{{ old('trainee_email') }}" placeholder="trainee@example.com">
                    @error('trainee_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Phone Number <span class="required">*</span></label>
                    <input type="tel" name="trainee_phone_number" class="form-control @error('trainee_phone_number') is-invalid @enderror" value="{{ old('trainee_phone_number') }}" required placeholder="+60 12-345 6789">
                    @error('trainee_phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label>Centre <span class="required">*</span></label>
                <select name="centre_name" class="form-control @error('centre_name') is-invalid @enderror" required>
                    <option value="">Select Centre</option>
                    @foreach($centres ?? [] as $centre)
                        <option value="{{ $centre->centre_name }}" {{ old('centre_name') == $centre->centre_name ? 'selected' : '' }}>
                            {{ $centre->centre_name }}
                        </option>
                    @endforeach
                </select>
                @error('centre_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <!-- Step 2: Medical Information -->
        <div class="form-section" data-section="2">
            <h2>Medical Information</h2>
            <div class="form-group">
                <label>Primary Condition <span class="required">*</span></label>
                <div class="condition-cards">
                    <div class="condition-card" onclick="selectCondition(this, 'autism')">
                        <h4>Autism Spectrum Disorder</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Social communication and behavioral challenges</p>
                    </div>
                    <div class="condition-card" onclick="selectCondition(this, 'adhd')">
                        <h4>ADHD</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Attention and hyperactivity challenges</p>
                    </div>
                    <div class="condition-card" onclick="selectCondition(this, 'dyslexia')">
                        <h4>Dyslexia</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Reading and language processing challenges</p>
                    </div>
                    <div class="condition-card" onclick="selectCondition(this, 'cerebral_palsy')">
                        <h4>Cerebral Palsy</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Movement and posture challenges</p>
                    </div>
                    <div class="condition-card" onclick="selectCondition(this, 'down_syndrome')">
                        <h4>Down Syndrome</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Genetic condition affecting development</p>
                    </div>
                    <div class="condition-card" onclick="selectCondition(this, 'other')">
                        <h4>Other</h4>
                        <p style="font-size: 0.875rem; color: #718096; margin-top: 0.5rem;">Specify in medical history</p>
                    </div>
                </div>
                <input type="hidden" name="trainee_condition" required>
                @error('trainee_condition')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Medical History</label>
                <textarea name="medical_history" rows="4" class="form-control @error('medical_history') is-invalid @enderror" placeholder="Please provide relevant medical history, medications, allergies, etc.">{{ old('medical_history') }}</textarea>
                @error('medical_history')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Doctor/Specialist Name</label>
                    <input type="text" name="doctor_name" class="form-control @error('doctor_name') is-invalid @enderror" value="{{ old('doctor_name') }}" placeholder="Dr. Ahmad">
                    @error('doctor_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Doctor Contact</label>
                    <input type="tel" name="doctor_contact" class="form-control @error('doctor_contact') is-invalid @enderror" value="{{ old('doctor_contact') }}" placeholder="+60 12-345 6789">
                    @error('doctor_contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label>Special Requirements</label>
                <textarea name="special_requirements" rows="3" class="form-control @error('special_requirements') is-invalid @enderror" placeholder="Dietary restrictions, mobility aids, communication preferences, etc.">{{ old('special_requirements') }}</textarea>
                @error('special_requirements')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <!-- Step 3: Guardian Information -->
        <div class="form-section" data-section="3">
            <h2>Guardian Information</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Guardian Name <span class="required">*</span></label>
                    <input type="text" name="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror" value="{{ old('guardian_name') }}" required>
                    @error('guardian_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Relationship <span class="required">*</span></label>
                    <select name="guardian_relationship" class="form-control @error('guardian_relationship') is-invalid @enderror" required>
                        <option value="">Select Relationship</option>
                        <option value="parent" {{ old('guardian_relationship') == 'parent' ? 'selected' : '' }}>Parent</option>
                        <option value="sibling" {{ old('guardian_relationship') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                        <option value="grandparent" {{ old('guardian_relationship') == 'grandparent' ? 'selected' : '' }}>Grandparent</option>
                        <option value="legal_guardian" {{ old('guardian_relationship') == 'legal_guardian' ? 'selected' : '' }}>Legal Guardian</option>
                        <option value="other" {{ old('guardian_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('guardian_relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Guardian Email <span class="required">*</span></label>
                    <input type="email" name="guardian_email" class="form-control @error('guardian_email') is-invalid @enderror" value="{{ old('guardian_email') }}" required>
                    @error('guardian_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Guardian Phone <span class="required">*</span></label>
                    <input type="tel" name="guardian_phone" class="form-control @error('guardian_phone') is-invalid @enderror" value="{{ old('guardian_phone') }}" required>
                    @error('guardian_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label>Guardian Address <span class="required">*</span></label>
                <textarea name="guardian_address" rows="3" class="form-control @error('guardian_address') is-invalid @enderror" required placeholder="Full address including postcode">{{ old('guardian_address') }}</textarea>
                @error('guardian_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Emergency Contact Name <span class="required">*</span></label>
                <input type="text" name="emergency_name" class="form-control @error('emergency_name') is-invalid @enderror" value="{{ old('emergency_name') }}" required placeholder="Alternative contact if guardian unavailable">
                @error('emergency_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Emergency Phone <span class="required">*</span></label>
                    <input type="tel" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone') }}" required>
                    @error('emergency_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Emergency Relationship <span class="required">*</span></label>
                    <input type="text" name="emergency_relationship" class="form-control @error('emergency_relationship') is-invalid @enderror" value="{{ old('emergency_relationship') }}" required placeholder="e.g., Aunt, Uncle">
                    @error('emergency_relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Step 4: Additional Information -->
        <div class="form-section" data-section="4">
            <h2>Additional Information</h2>
            <div class="form-group">
                <label>Preferred Activities</label>
                <div style="display: grid; gap: 0.5rem; margin-top: 0.5rem;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="speech" name="activities[]" value="speech_therapy">
                        <label for="speech" style="margin: 0;">Speech Therapy</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="occupational" name="activities[]" value="occupational_therapy">
                        <label for="occupational" style="margin: 0;">Occupational Therapy</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="physical" name="activities[]" value="physical_therapy">
                        <label for="physical" style="margin: 0;">Physical Therapy</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="behavioral" name="activities[]" value="behavioral_therapy">
                        <label for="behavioral" style="margin: 0;">Behavioral Therapy</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="sensory" name="activities[]" value="sensory_integration">
                        <label for="sensory" style="margin: 0;">Sensory Integration</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="communication" name="activities[]" value="communication_skills">
                        <label for="communication" style="margin: 0;">Communication Skills</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="additional_notes" rows="4" class="form-control @error('additional_notes') is-invalid @enderror" placeholder="Any other information that might help us provide better care">{{ old('additional_notes') }}</textarea>
                @error('additional_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>How did you hear about us?</label>
                <select name="referral_source" class="form-control @error('referral_source') is-invalid @enderror">
                    <option value="">Select Option</option>
                    <option value="doctor" {{ old('referral_source') == 'doctor' ? 'selected' : '' }}>Doctor/Hospital Referral</option>
                    <option value="school" {{ old('referral_source') == 'school' ? 'selected' : '' }}>School Recommendation</option>
                    <option value="social_media" {{ old('referral_source') == 'social_media' ? 'selected' : '' }}>Social Media</option>
                    <option value="website" {{ old('referral_source') == 'website' ? 'selected' : '' }}>Website</option>
                    <option value="friend" {{ old('referral_source') == 'friend' ? 'selected' : '' }}>Friend/Family</option>
                    <option value="other" {{ old('referral_source') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('referral_source')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="consent" name="consent" required>
                    <label for="consent" style="margin: 0;">I consent to the collection and processing of this data for rehabilitation services <span class="required">*</span></label>
                </div>
                @error('consent')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <!-- Step 5: Review -->
        <div class="form-section" data-section="5">
            <h2>Review Information</h2>
            <div class="summary-section">
                <h3 class="summary-title">Basic Information</h3>
                <div class="summary-item">
                    <span class="summary-label">Name</span>
                    <span class="summary-value" id="reviewName">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Date of Birth</span>
                    <span class="summary-value" id="reviewDOB">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Centre</span>
                    <span class="summary-value" id="reviewCentre">-</span>
                </div>
            </div>
            
            <div class="summary-section">
                <h3 class="summary-title">Medical Information</h3>
                <div class="summary-item">
                    <span class="summary-label">Primary Condition</span>
                    <span class="summary-value" id="reviewCondition">-</span>
                </div>
            </div>
            
            <div class="summary-section">
                <h3 class="summary-title">Guardian Information</h3>
                <div class="summary-item">
                    <span class="summary-label">Guardian Name</span>
                    <span class="summary-value" id="reviewGuardian">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Contact</span>
                    <span class="summary-value" id="reviewGuardianContact">-</span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="previousStep()" id="prevBtn" style="display: none;">Previous</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn">Next</button>
            <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit Registration</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 5;
    
    function updateProgress() {
        // Update step indicators
        document.querySelectorAll('.step').forEach((step, index) => {
            if (index + 1 < currentStep) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (index + 1 === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
        
        // Update form sections
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelector(`[data-section="${currentStep}"]`).classList.add('active');
        
        // Update buttons
        document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'block';
        document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'block';
        document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
        
        // Update review if on last step
        if (currentStep === totalSteps) {
            updateReview();
        }
    }
    
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateProgress();
            }
        }
    }
    
    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateProgress();
        }
    }
    
    function validateCurrentStep() {
        const currentSection = document.querySelector(`[data-section="${currentStep}"]`);
        const requiredFields = currentSection.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value) {
                field.style.borderColor = '#e53e3e';
                isValid = false;
            } else {
                field.style.borderColor = '#e2e8f0';
            }
        });
        
        return isValid;
    }
    
    function selectCondition(element, condition) {
        document.querySelectorAll('.condition-card').forEach(card => {
            card.classList.remove('selected');
        });
        element.classList.add('selected');
        document.querySelector('[name="trainee_condition"]').value = condition;
    }
    
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').src = e.target.result;
                document.getElementById('photoPreview').style.display = 'block';
                document.getElementById('uploadText').style.display = 'none';
                document.querySelector('.photo-upload').classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function updateReview() {
        const form = document.getElementById('traineeRegistrationForm');
        document.getElementById('reviewName').textContent = 
            form.trainee_first_name.value + ' ' + form.trainee_last_name.value;
        
        if (form.trainee_date_of_birth.value) {
            document.getElementById('reviewDOB').textContent = 
                new Date(form.trainee_date_of_birth.value).toLocaleDateString();
        }
        
        if (form.centre_name.value) {
            document.getElementById('reviewCentre').textContent = 
                form.centre_name.options[form.centre_name.selectedIndex].text;
        }
        
        const conditionMap = {
            'autism': 'Autism Spectrum Disorder',
            'adhd': 'ADHD',
            'dyslexia': 'Dyslexia',
            'cerebral_palsy': 'Cerebral Palsy',
            'down_syndrome': 'Down Syndrome',
            'other': 'Other'
        };
        document.getElementById('reviewCondition').textContent = 
            conditionMap[form.trainee_condition.value] || '-';
        
        document.getElementById('reviewGuardian').textContent = 
            form.guardian_name.value || '-';
        document.getElementById('reviewGuardianContact').textContent = 
            form.guardian_phone.value || '-';
    }
    
    // Initialize
    updateProgress();
    
    // Form submission
    document.getElementById('traineeRegistrationForm').addEventListener('submit', function(e) {
        if (!validateCurrentStep()) {
            e.preventDefault();
            alert('Please complete all required fields.');
        }
    });
</script>
@endsection