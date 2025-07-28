@extends('layouts.app')

@section('title', 'Generate Letter')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt mr-2"></i>Generate New Letter
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Alert Message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <!-- Letter Form -->
                    <form action="{{ route('letters.store') }}" method="POST" id="letterForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Letter Date -->
                                <div class="form-group">
                                    <label for="letter_date">Letter Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('letter_date') is-invalid @enderror" 
                                           id="letter_date" 
                                           name="letter_date" 
                                           value="{{ old('letter_date', date('Y-m-d')) }}" 
                                           required>
                                    @error('letter_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Recipient Type -->
                                <div class="form-group">
                                    <label for="recipient_type">Recipient Type</label>
                                    <select class="form-control" id="recipient_type" name="recipient_type">
                                        <option value="external" {{ old('recipient_type') == 'external' ? 'selected' : '' }}>External</option>
                                        <option value="trainee" {{ old('recipient_type') == 'trainee' ? 'selected' : '' }}>Trainee</option>
                                        <option value="staff" {{ old('recipient_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                </div>

                                <!-- Trainee Selection (Hidden by default) -->
                                <div class="form-group" id="traineeSelection" style="display: none;">
                                    <label for="recipient_id">Select Trainee</label>
                                    <select class="form-control" id="recipient_id" name="recipient_id">
                                        <option value="">-- Select Trainee --</option>
                                        @if(isset($trainees))
                                            @foreach($trainees as $trainee)
                                                <option value="{{ $trainee->id }}" {{ old('recipient_id') == $trainee->id ? 'selected' : '' }}>
                                                    {{ $trainee->name }} ({{ $trainee->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Recipient Name -->
                                <div class="form-group">
                                    <label for="recipient_name">Recipient Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('recipient_name') is-invalid @enderror" 
                                           id="recipient_name" 
                                           name="recipient_name" 
                                           value="{{ old('recipient_name') }}" 
                                           placeholder="Enter recipient name"
                                           required>
                                    @error('recipient_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Recipient Address -->
                                <div class="form-group">
                                    <label for="recipient_address">Recipient Address</label>
                                    <textarea class="form-control @error('recipient_address') is-invalid @enderror" 
                                              id="recipient_address" 
                                              name="recipient_address" 
                                              rows="3" 
                                              placeholder="Enter recipient address">{{ old('recipient_address') }}</textarea>
                                    @error('recipient_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Letter Subject -->
                                <div class="form-group">
                                    <label for="letter_subject">Subject <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('letter_subject') is-invalid @enderror" 
                                           id="letter_subject" 
                                           name="letter_subject" 
                                           value="{{ old('letter_subject') }}" 
                                           placeholder="Enter letter subject"
                                           required>
                                    @error('letter_subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Letter Content -->
                                <div class="form-group">
                                    <label for="letter_content">Letter Content <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('letter_content') is-invalid @enderror" 
                                              id="letter_content" 
                                              name="letter_content" 
                                              rows="10" 
                                              placeholder="Enter letter content"
                                              required>{{ old('letter_content') }}</textarea>
                                    @error('letter_content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Template Info -->
                                @if(isset($template))
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Using template: <strong>{{ $template->template_name }}</strong>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No active template found. Please contact administrator.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info" id="previewBtn">
                                        <i class="fas fa-eye mr-2"></i>Preview Letter
                                    </button>
                                    <div>
                                        <a href="{{ route('letters.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times mr-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary ml-2" id="generateBtn">
                                            <i class="fas fa-file-pdf mr-2"></i>Generate Letter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Letter Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading preview...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmGenerate">
                    <i class="fas fa-file-pdf mr-2"></i>Generate Letter
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle recipient type change
    $('#recipient_type').on('change', function() {
        if ($(this).val() === 'trainee') {
            $('#traineeSelection').show();
            $('#recipient_name').prop('readonly', true);
        } else {
            $('#traineeSelection').hide();
            $('#recipient_id').val('');
            $('#recipient_name').prop('readonly', false);
        }
    });

    // Handle trainee selection
    $('#recipient_id').on('change', function() {
        if ($(this).val() && $('#recipient_type').val() === 'trainee') {
            var selectedText = $(this).find('option:selected').text();
            var name = selectedText.split('(')[0].trim();
            $('#recipient_name').val(name);
        }
    });

    // Preview button click
    $('#previewBtn').on('click', function() {
        var form = $('#letterForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = $('#letterForm').serialize();
        
        $('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading preview...</p></div>');
        $('#previewModal').modal('show');

        $.ajax({
            url: '{{ route('letters.preview') }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#previewContent').html(response.html);
                } else {
                    $('#previewContent').html('<div class="alert alert-danger">Failed to generate preview: ' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#previewContent').html('<div class="alert alert-danger">Error generating preview. Please try again.</div>');
            }
        });
    });

    // Confirm generate from preview
    $('#confirmGenerate').on('click', function() {
        $('#letterForm').submit();
    });

    // Initialize on page load
    $('#recipient_type').trigger('change');
});
</script>
@endpush