{{-- 
    Unified Trainee Form Partial
    Used for both Create and Edit forms
--}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="trainee-form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    
    <!-- Flash Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Personal Information Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user mr-2"></i>Personal Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_first_name">First Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="trainee_first_name" 
                               id="trainee_first_name" 
                               class="form-control @error('trainee_first_name') is-invalid @enderror" 
                               value="{{ old('trainee_first_name', isset($trainee) ? $trainee->trainee_first_name : '') }}" 
                               required>
                        @error('trainee_first_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_last_name">Last Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="trainee_last_name" 
                               id="trainee_last_name" 
                               class="form-control @error('trainee_last_name') is-invalid @enderror" 
                               value="{{ old('trainee_last_name', isset($trainee) ? $trainee->trainee_last_name : '') }}" 
                               required>
                        @error('trainee_last_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" 
                               name="trainee_date_of_birth" 
                               id="trainee_date_of_birth" 
                               class="form-control @error('trainee_date_of_birth') is-invalid @enderror" 
                               value="{{ old('trainee_date_of_birth', isset($trainee) && $trainee->trainee_date_of_birth ? $trainee->trainee_date_of_birth->format('Y-m-d') : '') }}" 
                               required>
                        @error('trainee_date_of_birth')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender">Gender <span class="text-danger">*</span></label>
                        <select name="gender" 
                                id="gender" 
                                class="form-control @error('gender') is-invalid @enderror" 
                                required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender', isset($trainee) ? $trainee->gender : '') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', isset($trainee) ? $trainee->gender : '') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               name="trainee_email" 
                               id="trainee_email" 
                               class="form-control @error('trainee_email') is-invalid @enderror" 
                               value="{{ old('trainee_email', isset($trainee) ? $trainee->trainee_email : '') }}" 
                               required>
                        @error('trainee_email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_phone_number">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="trainee_phone_number" 
                               id="trainee_phone_number" 
                               class="form-control @error('trainee_phone_number') is-invalid @enderror" 
                               value="{{ old('trainee_phone_number', isset($trainee) ? $trainee->trainee_phone_number : '') }}" 
                               required>
                        @error('trainee_phone_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" 
                          id="address" 
                          class="form-control @error('address') is-invalid @enderror" 
                          rows="3">{{ old('address', isset($trainee) ? $trainee->address : '') }}</textarea>
                @error('address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="avatar">Profile Photo</label>
                <input type="file" 
                       name="trainee_avatar" 
                       id="avatar" 
                       class="form-control @error('trainee_avatar') is-invalid @enderror" 
                       accept="image/*">
                @error('trainee_avatar')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Upload profile photo (max 2MB, JPG/PNG/GIF)</small>
                
                @if($isEdit && isset($trainee) && $trainee->avatar)
                    <div class="mt-2">
                        <img src="{{ $trainee->getAvatarUrlAttribute() }}" 
                             alt="Current Avatar" 
                             class="img-thumbnail" 
                             style="max-width: 100px; max-height: 100px;">
                        <small class="text-muted d-block">Current photo</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Centre and Medical Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-hospital mr-2"></i>Centre & Medical Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="centre_name">Centre <span class="text-danger">*</span></label>
                        <select name="centre_name" 
                                id="centre_name" 
                                class="form-control @error('centre_name') is-invalid @enderror" 
                                required>
                            <option value="">Select Centre</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->centre_name }}" 
                                        {{ old('centre_name', isset($trainee) ? $trainee->centre_name : '') == $centre->centre_name ? 'selected' : '' }}>
                                    {{ $centre->centre_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('centre_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trainee_condition">Medical Condition <span class="text-danger">*</span></label>
                        <select name="trainee_condition" 
                                id="trainee_condition" 
                                class="form-control @error('trainee_condition') is-invalid @enderror" 
                                required>
                            <option value="">Select Condition</option>
                            @foreach(config('trainee.conditions') as $condition)
                                <option value="{{ $condition }}" 
                                        {{ old('trainee_condition', isset($trainee) ? $trainee->trainee_condition : '') == $condition ? 'selected' : '' }}>
                                    {{ $condition }}
                                </option>
                            @endforeach
                        </select>
                        @error('trainee_condition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="medical_notes">Medical Notes</label>
                <textarea name="medical_history" 
                          id="medical_notes" 
                          class="form-control @error('medical_history') is-invalid @enderror" 
                          rows="3" 
                          placeholder="Any important medical information, allergies, medications, etc.">{{ old('medical_history', isset($trainee) ? $trainee->medical_history : '') }}</textarea>
                @error('medical_history')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            @if($isEdit)
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" 
                        id="status" 
                        class="form-control @error('status') is-invalid @enderror">
                    <option value="active" {{ old('status', isset($trainee) ? $trainee->status : 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', isset($trainee) ? $trainee->status : '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="completed" {{ old('status', isset($trainee) ? $trainee->status : '') == 'completed' ? 'selected' : '' }}>Completed Program</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            @endif
        </div>
    </div>

    <!-- Guardian/Emergency Contact Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users mr-2"></i>Guardian & Emergency Contact
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="guardian_name">Guardian Name</label>
                        <input type="text" 
                               name="guardian_name" 
                               id="guardian_name" 
                               class="form-control @error('guardian_name') is-invalid @enderror" 
                               value="{{ old('guardian_name', isset($trainee) ? $trainee->guardian_name : '') }}" 
                               placeholder="Parent or guardian name">
                        @error('guardian_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="guardian_phone">Guardian Phone</label>
                        <input type="text" 
                               name="guardian_phone" 
                               id="guardian_phone" 
                               class="form-control @error('guardian_phone') is-invalid @enderror" 
                               value="{{ old('guardian_phone', isset($trainee) ? $trainee->guardian_phone : '') }}" 
                               placeholder="Guardian contact number">
                        @error('guardian_phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="guardian_relationship">Guardian Relationship <span class="text-danger">*</span></label>
                        <select name="guardian_relationship" 
                                id="guardian_relationship" 
                                class="form-control @error('guardian_relationship') is-invalid @enderror" 
                                required>
                            <option value="">Select relationship</option>
                            @foreach(config('trainee.guardian_relationships') as $relationship)
                                <option value="{{ $relationship }}" 
                                        {{ old('guardian_relationship', isset($trainee) ? $trainee->guardian_relationship : '') == $relationship ? 'selected' : '' }}>
                                    {{ $relationship }}
                                </option>
                            @endforeach
                        </select>
                        @error('guardian_relationship')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="guardian_email">Guardian Email <span class="text-danger">*</span></label>
                        <input type="email" 
                               name="guardian_email" 
                               id="guardian_email" 
                               class="form-control @error('guardian_email') is-invalid @enderror" 
                               value="{{ old('guardian_email', isset($trainee) ? $trainee->guardian_email : '') }}" 
                               placeholder="Guardian email address"
                               required>
                        @error('guardian_email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="guardian_address">Guardian Address</label>
                <textarea name="guardian_address" 
                          id="guardian_address" 
                          class="form-control @error('guardian_address') is-invalid @enderror" 
                          rows="3"
                          placeholder="Guardian's address (optional)">{{ old('guardian_address', isset($trainee) ? $trainee->guardian_address : '') }}</textarea>
                @error('guardian_address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" 
                               name="emergency_contact_name" 
                               id="emergency_contact_name" 
                               class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                               value="{{ old('emergency_contact_name', isset($trainee) ? $trainee->emergency_contact_name : '') }}" 
                               placeholder="Emergency contact person">
                        @error('emergency_contact_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input type="text" 
                               name="emergency_contact_phone" 
                               id="emergency_contact_phone" 
                               class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                               value="{{ old('emergency_contact_phone', isset($trainee) ? $trainee->emergency_contact_phone : '') }}" 
                               placeholder="Emergency contact phone">
                        @error('emergency_contact_phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="emergency_contact_relationship">Emergency Contact Relationship</label>
                        <input type="text" 
                               name="emergency_contact_relationship" 
                               id="emergency_contact_relationship" 
                               class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                               value="{{ old('emergency_contact_relationship', isset($trainee) ? $trainee->emergency_contact_relationship : '') }}" 
                               placeholder="Relationship to trainee">
                        @error('emergency_contact_relationship')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle mr-2"></i>Additional Information
            </h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="additional_info">Additional Notes</label>
                <textarea name="additional_notes" 
                          id="additional_info" 
                          class="form-control @error('additional_notes') is-invalid @enderror" 
                          rows="4" 
                          placeholder="Any additional information about the trainee (interests, special needs, goals, etc.)">{{ old('additional_notes', isset($trainee) ? $trainee->additional_notes : '') }}</textarea>
                @error('additional_notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" 
                           name="consent" 
                           id="consent" 
                           class="form-check-input @error('consent') is-invalid @enderror" 
                           value="1"
                           {{ old('consent') ? 'checked' : '' }}
                           required>
                    <label class="form-check-label" for="consent">
                        <strong>Consent for Registration <span class="text-danger">*</span></strong>
                        <br>
                        <small class="text-muted">
                            I confirm that I have the authority to register this trainee and that all information provided is accurate and complete. I consent to the collection and processing of this information for the purposes of providing rehabilitation services.
                        </small>
                    </label>
                    @error('consent')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save mr-2"></i>
            {{ $isEdit ? 'Update Trainee' : 'Register Trainee' }}
        </button>
        <a href="{{ route('trainees.home') }}" class="btn btn-secondary btn-lg ml-2">
            <i class="fas fa-times mr-2"></i>Cancel
        </a>
        @if($isEdit && isset($trainee))
            <a href="{{ route('trainees.show', \App\Helpers\EncryptionHelper::generateEncryptedId($trainee->id)) }}" class="btn btn-info btn-lg ml-2">
                <i class="fas fa-eye mr-2"></i>View Profile
            </a>
        @endif
    </div>
</form>

<style>
.trainee-form .card {
    border: 1px solid #e0e6ed;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.trainee-form .card-header {
    background: linear-gradient(135deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
    color: white;
    border-bottom: none;
}

.trainee-form .form-group label {
    font-weight: 600;
    color: #495057;
}

.trainee-form .form-actions {
    padding: 20px 0;
    border-top: 1px solid #e0e6ed;
    margin-top: 20px;
}

.trainee-form .text-danger {
    color: #dc3545 !important;
}

.trainee-form .img-thumbnail {
    border: 2px solid #dee2e6;
}
</style>