# Student Information System Module

Base path: `/api/v1/students`

## Features

- Student CRUD
- Admission data nested in create/update payloads
- Parent or guardian mapping through `student_guardian`
- Student lifecycle through `status`: `active`, `inactive`, `alumni`
- Document upload through `student_documents`

## Database Tables

### `students`
- `id`
- `school_id`
- `user_id`
- `uuid`
- `admission_no`
- `first_name`
- `last_name`
- `preferred_name`
- `email`
- `phone`
- `gender`
- `date_of_birth`
- `admission_date`
- `blood_group`
- `status`
- `photo_path`
- `address`
- `medical_notes`
- timestamps

### `guardians`
- `id`
- `school_id`
- `uuid`
- `first_name`
- `last_name`
- `email`
- `phone`
- `relationship_type`
- `occupation`
- `address`
- timestamps

### `student_guardian`
- `id`
- `school_id`
- `student_id`
- `guardian_id`
- `relationship`
- `is_primary`
- `is_emergency_contact`
- `pickup_authorized`
- timestamps

### `student_documents`
- `id`
- `school_id`
- `student_id`
- `uploaded_by`
- `document_type`
- `title`
- `disk`
- `file_path`
- `metadata`
- timestamps

## REST Endpoints

- `GET /api/v1/students`
- `POST /api/v1/students`
- `GET /api/v1/students/{student}`
- `PUT /api/v1/students/{student}`
- `DELETE /api/v1/students/{student}`
- `POST /api/v1/students/{student}/documents`

## Sample Create Request

```http
POST /api/v1/students
Authorization: Bearer <token>
X-Tenant-Code: greenwood
Content-Type: application/json
```

```json
{
  "admission_no": "ADM-2026-1001",
  "first_name": "Aarav",
  "last_name": "Mehta",
  "preferred_name": "Aarav",
  "email": "aarav.mehta@student.greenwood.edu",
  "phone": "7000000001",
  "gender": "male",
  "date_of_birth": "2012-02-10",
  "admission_date": "2026-04-15",
  "blood_group": "A+",
  "status": "active",
  "address": {
    "line1": "14 Residency Road",
    "city": "Bengaluru",
    "state": "Karnataka",
    "postal_code": "560025"
  },
  "medical_notes": "Asthma inhaler required",
  "guardians": [
    {
      "id": 1,
      "relationship": "Father",
      "is_primary": true,
      "is_emergency_contact": true,
      "pickup_authorized": true
    }
  ],
  "enrollment": {
    "academic_year_id": 1,
    "school_class_id": 1,
    "section_id": 1,
    "roll_number": "10A-12",
    "status": "active",
    "joined_on": "2026-04-15"
  },
  "admission": {
    "academic_year_id": 1,
    "applied_class_id": 1,
    "status": "admitted",
    "applied_on": "2026-04-08",
    "admitted_on": "2026-04-15",
    "remarks": "Entrance assessment cleared"
  }
}
```

## Sample Create Response

```json
{
  "message": "Student created successfully.",
  "data": {
    "id": 2,
    "uuid": "34e1f9c2-4da9-43f9-a122-1a26f1658315",
    "admission_no": "ADM-2026-1001",
    "first_name": "Aarav",
    "last_name": "Mehta",
    "preferred_name": "Aarav",
    "email": "aarav.mehta@student.greenwood.edu",
    "phone": "7000000001",
    "gender": "male",
    "date_of_birth": "2012-02-10T00:00:00.000000Z",
    "admission_date": "2026-04-15T00:00:00.000000Z",
    "blood_group": "A+",
    "status": "active",
    "full_name": "Aarav Mehta"
  }
}
```

## Sample Document Upload Request

```http
POST /api/v1/students/2/documents
Authorization: Bearer <token>
X-Tenant-Code: greenwood
Content-Type: multipart/form-data
```

Form fields:
- `document_type`: `birth_certificate`
- `title`: `Birth Certificate`
- `file`: binary file

## Sample Document Upload Response

```json
{
  "message": "Student document uploaded successfully.",
  "data": {
    "id": 1,
    "student_id": 2,
    "document_type": "birth_certificate",
    "title": "Birth Certificate",
    "disk": "local",
    "file_path": "students/2/documents/abc123.pdf"
  }
}
```
