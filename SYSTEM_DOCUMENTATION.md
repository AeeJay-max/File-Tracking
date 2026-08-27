# FileTrack Office Portal — System Operation & Navigation Guide
**Ministry of Sport, Recreation, Arts and Culture**

---

## 1. Executive System Overview

The **FileTrack Office Portal** is a high-security government digital document tracking and movement management system. Inspired by the Zimbabwean national colors (**Green, Gold, Red, Black, White**) and built for official ministry operations, the portal replaces physical file tracking with a streamlined digital content dispatch and audit workflow.

### Core Objectives
- **Digital Content Dispatching**: Focuses on registering, drafting, and sending general document contents and official minutes without requiring mandatory physical file uploads.
- **Direct Person-to-Person Transfer**: Enables officers to send documents directly to specific personnel across departments (with designation and department details), or directly to entire departments.
- **Time-Spent & Duration Audit Tracking**: Records exact movements from document creation to current holder, calculating precise time spent with each person (`Time with person: X days Y hours` / `Held so far: Z mins`).
- **Strict Role-Based Access Control**: Enforces precise operational boundaries for **Directors**, **Departmental Admins**, and **Officers/Users**.

---

## 2. User Roles & Organizational Hierarchy

```
                      ┌─────────────────────────┐
                      │        DIRECTOR         │
                      │  (Executive Overview)   │
                      └────────────┬────────────┘
                                   │
              ┌────────────────────┴────────────────────┐
              ▼                                         ▼
  ┌───────────────────────┐                 ┌───────────────────────┐
  │  DEPARTMENTAL ADMIN   │                 │  DEPARTMENTAL ADMIN   │
  │    (Department A)     │                 │    (Department B)     │
  └───────────┬───────────┘                 └───────────┬───────────┘
              │                                         │
              ▼                                         ▼
  ┌───────────────────────┐                 ┌───────────────────────┐
  │     USER / OFFICER    │                 │     USER / OFFICER    │
  │  (Senior Accountant)  │                 │   (Sports Officer)    │
  └───────────────────────┘                 └───────────────────────┘
```

### Role Summary & Permissions

| Role | Role Title in Portal | Scope & Key Capabilities |
| :--- | :--- | :--- |
| `super_admin` | **Director** | • Executive oversight over **all inter-departmental file transfers** system-wide.<br>• Can create and send official documents.<br>• Can create and manage **Directors**, **Departmental Admins**, and **Users**.<br>• Assigned to a specific Department (e.g. Director of Finance, Director of Sports).<br>• Access to **Database Backups** and **Department Management**. |
| `admin` | **Departmental Admin** | • Full visibility into **every file transferred TO and FROM their assigned department**.<br>• Manages staff user accounts within their department.<br>• Tracks department file activity and pending assignments. |
| `user` | **User / Officer** | • Registered staff members across all ministry departments.<br>• Can create new digital document dispatches.<br>• Can transfer held files directly to specific personnel across any department.<br>• Can update document contents while holding the file. |

---

## 3. System Navigation Guide

The portal interface features an executive left sidebar, a topbar context header, and a main workspace area.

```
┌─────────────────────────┬─────────────────────────────────────────────────────────────────┐
│  [OFFICIAL EMBLEM LOGO] │  TOPBAR: Search | Notifications [🔔] | User Profile [Director ▼] │
│                         ├─────────────────────────────────────────────────────────────────┤
│        FileTrack        │                                                                 │
│      OFFICE PORTAL      │  PAGE HEADER: Title & Quick Action Buttons                      │
│ ─────────────────────── │ ─────────────────────────────────────────────────────────────── │
│  MAIN                   │                                                                 │
│  📊 Dashboard           │  KPI SUMMARY CARDS                                              │
│  📁 Files               │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│                         │  │ Active Files │  │ Transferred  │  │ Time Held    │          │
│  ADMINISTRATION         │  └──────────────┘  └──────────────┘  └──────────────┘          │
│  👥 User Management     │                                                                 │
│  🏢 Departments         │  MOVEMENT & TIME SPENT HISTORY TABLE                            │
│  💾 System Backup       │  [ Sender → Recipient → Department → Time Spent → Contents ]    │
│                         │                                                                 │
│  ACCOUNT                │  LINKED-LIST FILE JOURNEY TIMELINE                              │
│  👤 Profile             │  [ Card 1 ] ──► [ Card 2 ] ──► [ Card 3 (Current Holder) ]      │
│  🚪 Logout              │                                                                 │
└─────────────────────────┴─────────────────────────────────────────────────────────────────┘
```

### Left Sidebar Navigation

- **Branding Header**: Prominent official Ministry Seal emblem logo centered above **FileTrack Office Portal**. Scales dynamically when toggled.
- **Main Section**:
  - **Dashboard** (`/dashboard`): Role-specific workspace displaying active files, quick stats, and recent movements.
  - **Files** (`/files`): Complete list of files accessible to your role, with real-time search, status filtering, and date range filters.
- **Administration Section** *(Visible to Directors & Departmental Admins)*:
  - **User & Officer Management** (`/users`): Create, edit, and manage accounts. Directors can select role (Director, Departmental Admin, or User) and assign mandatory departments.
  - **Departments** (`/departments`): Manage ministry departments and descriptions.
  - **System Backup** (`/admin/backup`): Generate, download, and manage database backups.
- **Account Section**:
  - **Profile** (`/profile`): Update personal details, contact number, avatar photo, and password.
  - **Logout**: Secure session termination.

### Top Navigation Bar (Topbar)

- **Sidebar Toggle Button** (`☰`): Collapse or expand the left sidebar for extra screen width.
- **Context Header**: Displays system context (*Ministry of Sport, Recreation, Arts and Culture*).
- **Notifications Dropdown** (`🔔`): Real-time notification center highlighting incoming transfers, file updates, and dispatch alerts.
- **User Menu (`Director ▼`)**: Displays active user name, badge title, and quick links to Profile or Logout.

---

## 4. Core Workflows & How the System Works

### Workflow 1: Creating a New Document Dispatch

1. Navigate to **Files** in the sidebar and click **New File** (or click **New File** on the Dashboard).
2. Enter the **File NameTitle** (e.g. *Q3 National Sports Equipment Budget Requisition*).
3. Select the **Department** that owns or originated the document.
4. Write the **Reference** in the text area.
   - *Note: Physical file upload is optional. You do not need to attach a file to create or send a document.*
5. Click **Save File**. The system registers the document with a unique File Number (e.g. `FILE-2026-0817-001`) and sets you as the initial creator and holder.

---

### Workflow 2: Sending / Transferring a Document to a Person

1. Open the file details page and click **Send / Transfer Document**.
2. Select your **Send Target**:
   - **Send Directly to Person** *(Recommended)*: Search personnel by name, department, or designation using the real-time autocomplete search bar.
   - **Send to Department**: Select a target department for general departmental receipt.
3. Write the **General Document Contents / Dispatch Message** for the recipient.
4. Click **Send Document Now**.
5. The system immediately:
   - Updates the current holder to the recipient person.
   - Logs a new **FileMovement** record capturing sender, recipient, timestamp, and contents.
   - Triggers an instant notification to the recipient officer.

---

### Workflow 3: Editing Document Contents while Preserving Audit History

1. When you hold a document, open its details page and click **Edit Document Contents**.
2. Update the **File Name** or **General Document Contents**.
3. Click **Save Changes**.
4. The system updates the active contents AND records an immutable `updated` movement entry in the history table, preserving all previous versions and sender history.

---

### Workflow 4: Viewing File Journey & Time-Spent Tracking

On any document's detail page (`/files/{uuid}`), the portal displays two comprehensive history sections:

1. **Movement & Time Spent History Table**:
   - Displays a structured table listing step numbers, **From Person**, **Sent To (Recipient)**, **Department**, **Sent Timestamp**, **Time Spent with Person** (e.g. `2 days 4 hours` or `Held so far: 12 mins`), and dispatch remarks.
2. **Linked-List File Journey Timeline**:
   - Visual card sequence showing avatars, designations, time-spent pills (`⏳ Time with person`), dates, and current holder status.

---

### Workflow 5: System Backups (Directors Only)

1. Navigate to **System Backup** (`/admin/backup`) in the left sidebar.
2. Click **Create Backup Now**.
3. The system generates a complete, self-contained SQL database dump formatted in the **Africa/Harare (CAT)** local timezone.
4. Click the **Download** button to download `.sql` backup files directly to your machine.

---

## 5. Summary of System Best Practices

- **Always verify recipient designation** when executing direct person-to-person transfers across departments.
- **Keep dispatch messages clear** in the General Contents text area to maintain a clean history log.
- **Regular Backups**: Directors should generate periodic database backups prior to major administrative changes.
- **Password Hygiene**: New accounts created with default passwords (`Password@123`) must be updated upon first login.
