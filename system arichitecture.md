# System Architecture & Technical Specifications

## 1. System & Environment Overview

- **Application Name**: Laravel File Tracking System
- **Framework Version**: **Laravel 12.x** (`^12.0`)
- **Language / Runtime**: **PHP 8.2+**
- **Test Framework**: **Pest PHP 3.8 / Laravel Feature Test Suite**
- **Database Engine**: MySQL 8.0+ / PostgreSQL (with composite indexing & relational integrity constraints)
- **Asset Pipeline & Development Server**: Vite & Concurrently
- **Asynchronous Task Architecture**: Database/Redis Queue (`ShouldQueue` Notifications)

---

## 2. Core Objectives

1. **Enterprise Document & File Movement Tracking**: Provide immutable tracking of physical and digital file movements across ministry departments.
2. **Strict Authorization & Role Boundaries**: Enforce role-based access control (Super Admin, Head of Department (HOD), Officer) with server-side policy verification.
3. **High Security & Adversarial Resilience**: Harden the application against SQL Injection, IDOR, XSS, Path Traversal, Unsafe Uploads, and Privilege Escalation.
4. **Controlled Public Transparency**: Provide a Records Department-controlled public search capability exposing strictly minimal, privacy-safe metadata without internal operational leakage.
5. **Turnaround & Overdue Accountability**: Enforce return timers for non-records departments, countdown resets upon return to Records custody, and automated dual-ping notifications for overdue files (> 8 hours).

---

## 3. Core Functions & Workflows

### A. File Registry & Scoped Identity
- Files belong to an origin department (`department_id`) and maintain a current location (`current_department_id`).
- Unique constraint `(file_number, department_id)` allows identical file numbers in different departments without identity collision.

### B. File Transfer Sequence
- **Standard Sequence**: Records Creator &rarr; Records Admin &rarr; Permanent Secretary &rarr; Records Admin &rarr; Handling Department Officer &rarr; Department HOD &rarr; Records Admin.
- **Permanent Secretary Review Gate**: Files must be reviewed by the Permanent Secretary before dispatch to any handling department.

### C. Return Timer & Custody Countdown Reset
- **Records Timeframe Assignment**: Records Department staff can specify a return time limit (`return_minutes`) on file dispatch, generating a `return_deadline`.
- **Counter Stop on Return**: When the file returns to Records Department custody, `return_deadline` is set to `null`, **stopping the timer countdown**.
- **Re-Assignment**: Subsequent dispatches from Records can assign fresh return timeframes.

### D. Overdue File Tracking (> 8 Hours) & Dual Ping Notifications
- **Overdue Detection (`isOverdue()`)**: Flags files held in non-records departments for **> 8 hours** or past return deadline.
- **Overdue Registry Filter**: Records Admin can filter files by `Overdue (>8 Hours)` status.
- **Ping File Action (`POST /files/{file}/ping-overdue`)**: Sends an immediate database `FileOverdueNotification` to both:
  1. The current **File Holder** officer.
  2. The **Head of Department (HOD)** of the holding department.

### E. Physical Custody Requirement for Operation Completion
- Marking file operations completed (`completeOperations`) requires the file to be currently held by the Records Department (`current_department_id` is Records).
- Attempting to mark operations completed while held by another department returns an error and renders a disabled lock button in the UI.

### F. Records-Controlled Public Visibility Control
- Records HOD (`role === 'admin'` in Records) or Super Admin can toggle `is_public` (ON/OFF).
- Public search returns a minimal DTO payload (7 safe fields: file number, file name, title, origin department, current department, status, registration date). All staff names, remarks, and download links are excluded.

### G. Super Admin-Only Account Deletion
- Account deletion (`destroy`) is restricted exclusively to `super_admin` across all controllers (`ProfileController`, `AdminUserController`, `UserController`).
- Self-deletion buttons removed from user profiles and department admin user lists.

---

## 4. Architectural Layers & Design Patterns

```
+-----------------------------------------------------------------------+
|                            PRESENTATION LAYER                         |
|      Blade Views (Bootstrap 5, Safe Escaping {{ e($str) }}, Modals)   |
+-----------------------------------------------------------------------+
                                   |
                                   v
+-----------------------------------------------------------------------+
|                            ROUTING & MIDDLEWARE                       |
|   web.php | SecurityHeadersMiddleware | ForcePasswordChangeMiddleware |
|             Throttle (throttle:30,1) | Auth & Role Guards             |
+-----------------------------------------------------------------------+
                                   |
                                   v
+-----------------------------------------------------------------------+
|                            CONTROLLER LAYER                           |
|   FileRecordController | FileTransferController | PublicFileSearch    |
|   ProfileController    | UserController        | NotificationController|
+-----------------------------------------------------------------------+
                                   |
                                   v
+-----------------------------------------------------------------------+
|                      AUTHORIZATION & DOMAIN LOGIC                     |
|           FileRecordPolicy | CacheService | AuditLog Observer          |
+-----------------------------------------------------------------------+
                                   |
                                   v
+-----------------------------------------------------------------------+
|                            DATA PERSISTENCE                           |
|    FileRecord | FileMovement | FileTransfer | User | Department        |
|    Composite Indexes: (file_number, department_id), (is_public)       |
+-----------------------------------------------------------------------+
```

---

## 5. Security Architecture & Threat Mitigation

| Security Domain | Risk & Threat Model | Architectural Mitigation |
| :--- | :--- | :--- |
| **SQL Injection (SQLi)** | Arbitrary SQL payload execution via input fields. | 100% parameter-bound Eloquent queries & query builder scopes (`scopeByOriginDepartmentAndNumber`, `scopePubliclyViewable`, `scopeOverdue`). |
| **Broken Access Control (IDOR)** | Tampering with file UUIDs to hijack or view unauthorized records. | Server-side `FileRecordPolicy` authorization evaluated on every action (`$this->authorize(...)`). |
| **Cross-Site Scripting (XSS)** | Malicious HTML/JS injection in file remarks or names. | Escaped Blade output tags `{{ ... }}` and formatted safe escaping `nl2br(e($remarks))`. |
| **Unsafe File Uploads** | Executable file extension upload (`.php.png` or shell scripts). | Client extension ignored; resolved via `$file->extension()`. Filename sanitized via `Str::slug()`. Restricted MIME types (PDF, DOC, DOCX, JPG, PNG) and 10MB limit. |
| **Path Traversal in Downloads** | Downloading system files via path manipulating parameters. | Enforced private storage paths (`storage/app/private/...`) using `Storage::disk('private')->download()`. |
| **Privilege Escalation** | Escalating role to Super Admin via form parameter tampering. | Role assignment restricted to Super Admin controller actions. Mass-assignment protected fields. |
| **Rate Limiting** | Brute force or denial-of-service on sensitive endpoints. | Applied `throttle:30,1` on transfers/toggles and `throttle:60,1` on AJAX endpoints. |
| **Security Headers** | Clickjacking, MIME-sniffing, HTTP downgrade. | `SecurityHeadersMiddleware` enforcing HSTS (`max-age=31536000`), X-Frame-Options (`SAMEORIGIN`), X-Content-Type-Options (`nosniff`). |

---

## 6. Database Indexing & Performance Optimizations

1. **Composite Unique Index**: `(file_number, department_id)` on `file_records` prevents duplicate file number creation per department.
2. **Performance Indexes**:
   - `file_records(current_department_id)` & `file_records(department_id)`
   - `file_records(is_public)` & `file_records(status)`
   - `file_records(created_by)` & `file_records(current_user_id)`
3. **Cache Layer (`CacheService.php`)**:
   - Caches active departments and designations.
   - Boot hooks on `Department` and `Designation` automatically invalidate cache on mutation.
4. **Asynchronous Queued Notifications**:
   - Notifications (`FileTransferredNotification`, `FileAssignmentPendingNotification`, `FileOverdueNotification`) implement `ShouldQueue` to prevent synchronous SMTP/database latency.
