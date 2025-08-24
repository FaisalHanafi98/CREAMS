# CREAMS System Business Logic Documentation

## 📋 Volunteer Application Module

### Core Business Rules

#### 1. **Centre Assignment Logic**
- **Rule**: Volunteers are automatically assigned to the centre of the admin who approves their application
- **Implementation**: `volunteers.reviewed_by` → `users.centre_id` → `centres.centre_name`
- **Example**: 
  - Admin "Mohd Hassan" from Centre 05 (PPDK IIUM) approves volunteer "Sarah"
  - Sarah is automatically assigned to Centre 05 (PPDK IIUM)
  - No direct `centre_id` stored in volunteers table

#### 2. **Application Status Flow**
```
applied → reviewed → approved/rejected → active/inactive
```

**Status Definitions**:
- `applied`: Initial volunteer application submitted
- `reviewed`: Admin has reviewed the application  
- `approved`: Admin approves - volunteer can start activities
- `rejected`: Admin rejects - volunteer cannot participate
- `active`: Approved volunteer is currently participating
- `inactive`: Approved volunteer is temporarily not participating

#### 3. **Access Control**
- **Admin Users**: Can approve/reject applications for their centre only
- **Supervisor Users**: Can view applications for their centre
- **Regular Users**: Cannot access volunteer management

#### 3.1. **Available Centres for User Registration**
```
01 - Gombak (Main headquarters - IIUM)
02 - Kuantan (Regional centre - Pahang)  
03 - Shah Alam (Autism specialist centre)
04 - Pagoh (Vocational training focus)
05 - Gambang (Rural outreach centre)
06 - Nilai (University-based research centre)
07 - Cyberjaya (Technology-focused centre)
```

#### 4. **Data Relationships**
```
Volunteer Table Structure:
- id (Primary Key)
- name, email, phone, address (Personal Info)
- status (Application Status)
- reviewed_by (Foreign Key → users.id)
- reviewed_at (Timestamp)
- review_notes (Admin Comments)

Relationships:
volunteers.reviewed_by → users.id → users.centre_id → centres.centre_name
```

#### 5. **Audit Trail**
- **Who approved**: `reviewed_by` field stores admin user ID
- **When approved**: `reviewed_at` timestamp
- **Why approved/rejected**: `review_notes` field
- **Which centre**: Derived through admin's centre_id

---

## 📄 Letter Generator Module

### Core Business Rules

#### 1. **Letter Creation & Audit Tracking**
- **Rule**: Every letter must track both creator and generator for audit purposes
- **Implementation**: 
  - `created_by`: User who created/initiated the letter
  - `generated_by`: User who actually generated the letter (same as created_by)
- **Database Constraint**: Both fields are `NOT NULL` for accountability

#### 2. **Centre Association**
- **Rule**: Letters are associated with the centre of the user who generates them
- **Implementation**: `letters.centre_id` = `session('centre_id')`
- **Example**: Admin from Centre 05 generates letter → letter belongs to Centre 05

#### 3. **Letter Reference System**
- **Format**: `LTR/YYYY/MM/NNNN`
- **Example**: `LTR/2025/08/0001`
- **Auto-Generation**: System automatically creates unique sequential references
- **Uniqueness**: Database ensures no duplicate references

#### 4. **Letter Types & Templates**
```
Letter Types:
- official_letter: Official correspondence
- trainee: Letters to trainees
- staff: Letters to staff members
- general: General purpose letters
- certificate: Certificate letters
```

#### 5. **Template System**
- **Active Template**: Only one template can be active at a time per centre
- **Template Variables**: Support for header/footer images and text
- **Template Inheritance**: Letters inherit formatting from active template

#### 6. **Data Flow**
```
Letter Generation Process:
1. User selects active template
2. User fills letter details (recipient, content, etc.)
3. System creates Letter record with:
   - Auto-generated reference number
   - User's centre_id
   - created_by = user_id
   - generated_by = user_id
   - Template association
4. PDF generation (if enabled)
5. Audit trail creation
```

#### 7. **Access Control**
- **Admin Users**: Can create any type of letter
- **Supervisor Users**: Can create staff and general letters
- **Teacher Users**: Can create trainee and general letters
- **AJK Users**: Limited access based on centre

#### 8. **Letter Status Management**
```
Status Flow: draft → generated → sent → delivered
```

**Status Definitions**:
- `draft`: Letter created but not finalized
- `generated`: Letter finalized and ready to send
- `sent`: Letter has been sent to recipient
- `delivered`: Letter confirmed delivered to recipient

---

## 🔗 Integration Between Modules

### 1. **Centre-Based Segregation**
- Both volunteers and letters are segregated by centre
- Admins can only manage records for their assigned centre
- Centre assignment happens automatically based on admin's centre_id

### 2. **Audit Requirements**
- **Volunteers**: Track which admin approved/rejected each application
- **Letters**: Track which user created/generated each letter
- **Purpose**: Accountability, compliance, and operational oversight

### 3. **User Role Integration**
```
Admin (Centre-specific):
- Approve/reject volunteer applications for their centre
- Generate all types of letters for their centre
- Full audit trail access

Supervisor (Centre-specific):
- View volunteer applications for their centre
- Generate limited letter types for their centre
- Read-only access to some audit data

Teacher:
- Limited volunteer viewing (if any)
- Generate trainee-related letters
- Basic audit access

AJK:
- Basic system access
- Centre-specific data access only
```

### 4. **Data Consistency Rules**
- User's centre_id must exist in centres table
- Volunteer's reviewed_by must exist in users table
- Letter's template_id must exist in letter_templates table
- All audit fields (timestamps, user IDs) are mandatory

---

## 🚨 Critical Business Constraints

### 1. **Data Integrity**
- **No orphaned records**: All foreign keys must be valid
- **No missing audit data**: created_by, generated_by, reviewed_by must be populated
- **Centre validation**: All operations must respect centre boundaries

### 2. **Security Rules**
- **Centre isolation**: Users cannot access data from other centres
- **Role-based access**: Actions restricted by user role
- **Audit immutability**: Historical audit data cannot be modified

### 3. **Operational Rules**
- **Letter references must be unique**: System prevents duplicates
- **Template consistency**: Only one active template per centre
- **Status progression**: Status changes must follow defined workflows

---

## 🔧 Technical Implementation Notes

### Database Relationships
```sql
-- Volunteer centre assignment
SELECT v.*, u.centre_id, c.centre_name 
FROM volunteers v
JOIN users u ON v.reviewed_by = u.id
JOIN centres c ON u.centre_id = c.centre_id;

-- Letter audit trail
SELECT l.*, u1.name as created_by_name, u2.name as generated_by_name
FROM letters l
JOIN users u1 ON l.created_by = u1.id
JOIN users u2 ON l.generated_by = u2.id;
```

### Model Relationships
```php
// Volunteer Model
public function reviewedByUser() {
    return $this->belongsTo(User::class, 'reviewed_by');
}

public function centre() {
    return $this->hasOneThrough(Centre::class, User::class, 
        'id', 'centre_id', 'reviewed_by', 'centre_id');
}

// Letter Model
public function creator() {
    return $this->belongsTo(User::class, 'created_by');
}

public function generator() {
    return $this->belongsTo(User::class, 'generated_by');
}
```

This documentation serves as the definitive guide for understanding how the Volunteer Application and Letter Generator modules work within the CREAMS system.