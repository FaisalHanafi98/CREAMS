# CREAMS Letters Module Summary

## Overview
The Letters module provides a comprehensive system for generating, managing, and archiving official letters with customizable templates and PDF generation capabilities.

## Controllers

### 1. LetterController.php
**Purpose**: Handles letter viewing, archiving, and basic operations

**Key Methods**:
- `index()`: Display letters archive with search and pagination
- `create()`: Show letter creation form
- `preview()`: Generate AJAX preview for letters
- `download()`: Redirect to profile download route
- `destroy()`: Delete letters with permission checks

**Key Features**:
- **Role-based access**: Non-admins only see their own letters
- **Search functionality**: Filter by reference number and subject
- **Fallback views**: Creates basic HTML if templates are missing
- **Data normalization**: Ensures letter_data arrays have default values

### 2. LetterTemplateController.php
**Purpose**: Manages letter templates and PDF generation

**Key Methods**:
- `store()`: Create new letter templates with image uploads
- `generate()`: Generate letters with PDF from profile
- `preview()`: Generate letter preview
- `newReference()`: Generate unique reference numbers
- `viewLetter()`: Display PDF in browser
- `downloadLetter()`: Download PDF files
- `destroy()`: Delete letters and associated files

**Key Features**:
- **Template management**: Header/footer content and images
- **PDF generation**: Using DomPDF with fallback to HTML
- **File handling**: Dual storage (storage/ and public/)
- **Reference generation**: Format: LTR/YYYY/MM/####
- **Image uploads**: Support for header/footer images (2MB limit)

## Models

### 1. Letter.php
**Purpose**: Represents individual letters

**Key Properties**:
- `letter_reference`: Unique reference number
- `letter_subject`: Letter subject line
- `letter_content`: Main letter content
- `letter_type`: Type of letter (official, etc.)
- `recipient_id`: ID of recipient
- `recipient_type`: Type of recipient (external, internal)
- `template_id`: Associated template
- `letter_status`: Status (draft, sent, delivered, archived)
- `letter_date`: Date of letter
- `letter_file_path`: Path to generated PDF
- `letter_data`: JSON array with additional data
- `created_by`: User who created the letter

**Key Features**:
- **Auto-generation**: Reference numbers generated on creation
- **File management**: Methods to check and retrieve PDF files
- **Data accessors**: Easy access to letter_data fields
- **Search scope**: Full-text search across multiple fields
- **Relationships**: Links to templates and users

### 2. LetterTemplate.php
**Purpose**: Manages letter templates

**Key Properties**:
- `template_name`: Name of template
- `template_content`: HTML content template
- `template_type`: Type (letter, etc.)
- `template_variables`: JSON array with header/footer data
- `is_active`: Whether template is currently active
- `created_by`: User who created template

**Key Features**:
- **Active template**: Only one active template at a time
- **Image handling**: Header and footer image support
- **Variable access**: Easy access to template variables
- **Relationship management**: Links to letters and users

## Database Schema

### letter_templates table
```sql
- id (primary key)
- template_name (string)
- template_content (text)
- template_type (string)
- template_variables (json, nullable)
- is_active (boolean, default true)
- created_by (foreign key to users)
- timestamps
```

### letters table
```sql
- id (primary key)
- letter_reference (string, unique)
- letter_subject (string)
- letter_content (text)
- letter_type (string)
- recipient_id (foreign key)
- recipient_type (string)
- template_id (foreign key to letter_templates, nullable)
- letter_status (enum: draft, sent, delivered, archived)
- letter_date (date)
- sent_date (date, nullable)
- letter_file_path (string, nullable)
- letter_data (json, nullable)
- created_by (foreign key to users)
- timestamps
```

## Views

### 1. create.blade.php
**Purpose**: Letter creation form
**Features**: 
- Form validation
- Template selection
- Rich text editor for content
- Recipient information fields

### 2. edit.blade.php
**Purpose**: Letter editing form
**Features**:
- Pre-populated form data
- Template switching
- Update validation

### 3. index.blade.php
**Purpose**: Letters archive/listing
**Features**:
- Paginated letter list
- Search functionality
- Filter by date range
- Action buttons (view, edit, delete, download)

### 4. show.blade.php
**Purpose**: Individual letter display
**Features**:
- Full letter content display
- Template information
- PDF download link
- Edit/delete actions

### 5. preview.blade.php
**Purpose**: Letter preview template
**Features**:
- Live preview of letter formatting
- Template application
- Real-time updates

### 6. preview-template.blade.php
**Purpose**: Template-based preview
**Features**:
- Applies active template
- Shows header/footer images
- Formatted content display

### 7. pdf-template.blade.php
**Purpose**: PDF generation template
**Features**:
- PDF-optimized layout
- Image handling for headers/footers
- Proper field mapping for PDF generation

## Routes

### Web Routes (in routes/web.php)
```php
// Letter Template Routes
Route::post('/letter-template/store', [LetterTemplateController::class, 'store'])->name('letter-template.store');
Route::post('/letter-template/generate', [LetterTemplateController::class, 'generate'])->name('letter-template.generate');
Route::post('/letter-template/preview', [LetterTemplateController::class, 'preview'])->name('letter-template.preview');
Route::get('/letter-template/new-reference', [LetterTemplateController::class, 'newReference'])->name('letter-template.new-reference');

// Letter Management Routes
Route::get('/letters', [LetterTemplateController::class, 'index'])->name('letters.index');
Route::get('/letters/create', [LetterTemplateController::class, 'create'])->name('letters.create');
Route::get('/letters/{id}', [LetterTemplateController::class, 'show'])->name('letters.show');
Route::get('/letters/{id}/edit', [LetterTemplateController::class, 'edit'])->name('letters.edit');
Route::put('/letters/{id}', [LetterTemplateController::class, 'update'])->name('letters.update');
Route::delete('/letters/{id}', [LetterTemplateController::class, 'destroy'])->name('letters.destroy');

// Letter File Operations
Route::get('/letters/{id}/view', [LetterTemplateController::class, 'viewLetter'])->name('letters.view');
Route::get('/letters/{id}/download', [LetterTemplateController::class, 'downloadLetter'])->name('letters.download');

// Profile Letter Operations
Route::get('/profile/letters/{id}/download', [LetterTemplateController::class, 'downloadLetter'])->name('profile.letter.download');
```

## Key Features

### 1. Template System
- **Active Template**: Only one active template at a time
- **Image Support**: Header and footer images with 2MB limit
- **Variable Storage**: JSON-based template variables
- **Content Replacement**: [CONTENT] placeholder replacement

### 2. PDF Generation
- **DomPDF Integration**: PDF generation from HTML templates
- **Dual Storage**: Files stored in both storage/ and public/ directories
- **Fallback System**: HTML generation if PDF fails
- **Image Handling**: Proper image path resolution for PDFs

### 3. Reference Number System
- **Auto-generation**: Unique reference numbers on creation
- **Format**: LTR/YYYY/MM/#### (e.g., LTR/2025/07/0001)
- **Collision Prevention**: Database-based sequence tracking

### 4. Access Control
- **Role-based Access**: Admins see all letters, others see only their own
- **Permission Checks**: Edit/delete/view permissions verified
- **Session Integration**: Uses custom session-based authentication

### 5. File Management
- **Dual Storage**: Files in both storage and public directories
- **Path Checking**: Multiple fallback paths for file access
- **Cleanup**: File deletion when letters are deleted

## Security Features

### 1. Input Validation
- **Form Validation**: Comprehensive validation rules
- **File Upload Security**: MIME type and size restrictions
- **XSS Prevention**: Proper HTML escaping in views

### 2. Access Control
- **Permission Checks**: User ownership verification
- **Role-based Access**: Admin vs regular user permissions
- **File Access**: Secure file serving with permission checks

### 3. Data Integrity
- **Transaction Wrapping**: Database operations in transactions
- **Error Handling**: Comprehensive error logging
- **Fallback Systems**: Multiple fallback mechanisms

## Integration Points

### 1. Profile Integration
- **Profile Tab**: Letters accessible from user profile
- **Letter History**: Complete letter history in profile
- **Generation Form**: Letter creation from profile interface

### 2. User System
- **Creator Tracking**: Links to Users model
- **Role Integration**: Works with CREAMS role system
- **Session Data**: Uses session-based user identification

### 3. File System
- **Storage Integration**: Laravel Storage facade
- **Public Access**: Direct public file access
- **Asset Management**: Proper asset URL generation

## Recent Updates

### 1. PDF Generation Fix
- **Field Mapping**: Corrected field name mismatches
- **Image Paths**: Fixed image path resolution for PDFs
- **Data Access**: Proper JSON field access patterns

### 2. Template Variables
- **Header/Footer**: Proper template variable access
- **Image Storage**: Correct image path storage and retrieval
- **Data Structure**: Standardized data structure access

### 3. Error Handling
- **Fallback Views**: Basic HTML when templates missing
- **Default Values**: Proper default values for missing data
- **Logging**: Comprehensive error logging

## Dependencies

### 1. Laravel Packages
- **DomPDF**: `barryvdh/laravel-dompdf` for PDF generation
- **Storage**: Laravel Storage facade
- **Carbon**: Date handling and formatting

### 2. Frontend Dependencies
- **Bootstrap**: UI framework
- **jQuery**: JavaScript functionality
- **Rich Text Editor**: For content editing

### 3. System Dependencies
- **GD Extension**: For image processing (with fallback)
- **File System**: Read/write permissions for storage
- **Database**: MySQL/MariaDB for data storage

## Usage Patterns

### 1. Letter Creation Flow
1. User accesses profile letters tab
2. Fills out letter form with subject, content, recipient
3. System generates reference number
4. Preview generated for review
5. PDF generated and stored
6. User can download immediately or later

### 2. Template Management
1. Admin creates letter template
2. Uploads header/footer images
3. Sets template content and variables
4. Activates template (deactivates others)
5. Template used for all subsequent letters

### 3. Letter Archive
1. Users view their letter history
2. Search by reference, subject, or content
3. Filter by date range
4. Download, edit, or delete letters
5. Admins can see all letters system-wide

## Known Issues & Solutions

### 1. PDF Generation Issues
- **Problem**: Empty PDFs due to image path issues
- **Solution**: Corrected storage_path() usage for images
- **Status**: Fixed in recent update

### 2. Field Name Mismatches
- **Problem**: Template using wrong field names
- **Solution**: Updated templates to use correct Letter model fields
- **Status**: Fixed in recent update

### 3. Missing Default Values
- **Problem**: recipient_id undefined errors
- **Solution**: Added default values in controller
- **Status**: Fixed in recent update

## Future Enhancements

### 1. Template System
- **Multiple Templates**: Support for multiple active templates
- **Template Categories**: Different templates for different letter types
- **Template Versioning**: Version control for templates

### 2. Workflow Features
- **Approval Workflow**: Multi-step approval process
- **Digital Signatures**: Electronic signature support
- **Bulk Operations**: Batch letter generation

### 3. Integration Features
- **Email Integration**: Direct email sending from letters
- **External Systems**: Integration with external document systems
- **API Endpoints**: RESTful API for external access

This module provides a robust foundation for official letter generation and management within the CREAMS system, with comprehensive PDF generation, template management, and archive capabilities.