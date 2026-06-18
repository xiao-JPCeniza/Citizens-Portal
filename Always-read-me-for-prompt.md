# Citizen ID Application Portal

## Functional Requirements and System Workflow

### Project Description

Develop a web-based Citizen ID Application Portal for residents of Manolo Fortich, Bukidnon.

The system shall allow citizens to submit online applications for a Citizen ID and allow administrators to review, approve, reject, archive, and export application records.

---

# PUBLIC SIDE

## Step 1: Welcome Page

### URL

```text
/
```

### Purpose

The landing page will introduce the Citizen ID Program and provide instructions before application.

### Contents

* Citizen ID Overview
* Benefits and Purpose
* Application Requirements
* Sample Passport Photo
* Sample GCash Screenshot
* Terms and Conditions

### User Action

The applicant must check the checkbox:

```text
☑ I have read and agree to the Terms and Conditions.
```

### Validation

The "Proceed Application" button must remain disabled until the checkbox is checked.

### Button

```text
Proceed Application
```

### Next Step

Redirect user to the Citizen Application Form.

---

# Step 2: Citizen Application Form

### URL

```text
/apply
```

### Required Information

The applicant must complete the following fields:

| Field                    | Required |
| ------------------------ | -------- |
| Email Address            | Yes      |
| Full Name                | Yes      |
| GCash Number             | Yes      |
| Barangay                 | Yes      |
| Complete Address         | Yes      |
| Blood Type               | Yes      |
| Emergency Contact Person | Yes      |
| Emergency Contact Number | Yes      |
| Passport Photo Upload    | Yes      |
| GCash Screenshot Upload  | Yes      |

---

## Address Validation

Only residents within Manolo Fortich are allowed.

Barangay field should be a dropdown list containing all barangays of Manolo Fortich.

Example:

```text
Alae
Agusan Canyon
Dalirig
Damilag
Dicklum
Guilang-Guilang
Kalugmanan
Lingion
Lunocan
Maluko
Mampayag
Mantibugao
Santiago
Tankulan
```

---

## Blood Type Dropdown

```text
A+
A-
B+
B-
AB+
AB-
O+
O-
```

---

## Passport Photo Requirements

Display a sample image beside the upload field.

Requirements:

```text
White background
Face clearly visible
No hat
No sunglasses
JPG or PNG
Maximum 5MB
```

---

## GCash Screenshot Requirements

Display a sample screenshot beside the upload field.

Requirements:

```text
JPG
PNG
PDF
Maximum 5MB
```

---

## Submit Application

### System Action

Upon successful submission:


2. Save application with status:

```text
Pending Verification
```

3. Send confirmation email.

### Email Content

Subject:

```text
Citizen ID Application Received
```

Body:

```text
Thank you for submitting your Citizen ID application.

Your application is currently under verification.

Please wait for another email regarding the result of your application.

Reference Number:
CID-2026-000001
```

### End Result

Applicant waits for verification.

---

# ADMIN SIDE

## Step 1: Admin Login

### URL

```text
/admin
```

### Login Fields

```text
Email
Password
```

### Access

Only authorized administrators may access the system.

---

# Step 2: Dashboard

After login, the administrator is redirected to the Dashboard.

### Dashboard Statistics

Display:

```text
Pending Applications
Approved Applications
Rejected Applications
Archived Applications
Total Applications
```

---

# Step 3: New Applicants Queue

### Purpose

Display all newly submitted applications.

### Processing Method

Applications must appear using FIFO (First In, First Out).

Oldest applications should appear first.

### Table Columns

```text
Reference Number
Full Name
Barangay
Date Submitted
Status
Action
```

### Action Button

```text
View Application
```

---

# Step 4: Review Application

When the administrator clicks "View Application":

### Display Applicant Information

```text
Reference Number
Full Name
Email Address
GCash Number
Barangay
Complete Address
Blood Type
Emergency Contact Person
Emergency Contact Number
Submission Date
```

### Display Uploaded Files

```text
Passport Photo Preview
GCash Screenshot Preview
```

---

# Step 5: Verify Application

The administrator can either:

## Option A: Approve

### Action

Click:

```text
Approve Application
```

### System Process

1. Update status to:

```text
Approved
```

2. Record:

```text
Verified By
Verified Date
```

3. Send approval email.

### Email Content

Subject:

```text
Citizen ID Application Approved
```

Body:

```text
Congratulations.

Your Citizen ID application has been approved.

Further instructions will be provided by the administrator.
```

### Next Step

Move application to Finalization Page.

---

## Option B: Reject

### Action

Click:

```text
Reject Application
```

### Rejection Reasons

Administrator must select a reason:

```text
Invalid Passport Photo
Invalid GCash Screenshot
Incomplete Information
Unreadable Documents
Non-Resident of Manolo Fortich
Duplicate Application
Other
```

### Remarks

Administrator may provide additional comments.

### System Process

1. Update status to:

```text
Rejected
```

2. Send rejection email.

### Email Content

Subject:

```text
Citizen ID Application Rejected
```

Body:

```text
Your Citizen ID application has been rejected.

Reason:
{Remarks}
```

3. Automatically move record to Archive.

### Reapplication Rule

Rejected applicants are allowed to submit a new application.

---

# Step 6: Archive Section

### Purpose

Store all rejected applications.

### Features

* View archived applications
* Search archived applications
* Review rejection history

### Note

Archived applications should not appear in the active verification queue.

---

# Step 7: Finalization Page

### Purpose

Store all approved applications.

### Menu

```text
Finalized Applications
```

### Table Columns

```text
Reference Number
Full Name
Barangay
Blood Type
Date Approved
Approved By
```

### Features

* Search
* Filter by Barangay
* Filter by Date
* View Details

---

# Step 8: Export Records

### Button

```text
Export to Excel
```

### Export Format

```text
Reference Number
Full Name
Email Address
GCash Number
Barangay
Address
Blood Type
Emergency Contact Person
Emergency Contact Number
Passport Photo Preview
```

### File Type

```text
.xlsx
```

---

# Future Enhancements

Phase 2

* QR Code Verification
* Citizen ID Card Generation
* Printable Citizen ID
* SMS Notifications
* Application Tracking Portal
* Online Payment Validation
* Digital Citizen ID PDF
* Reprint Requests
* Renewal Requests

---

# Recommended Development Stack

* PHP 8.4
* Laravel 12
* Livewire 3
* Volt
* Tailwind CSS 4
* MySQL 8
* Laravel Breeze
* Laravel Excel
* Laravel Mail
* Spatie Permission
* Queue Jobs for Emails
* Responsive Mobile Design

This system should follow FIFO verification processing, maintain application history, and support future expansion for a full Citizen ID Management System.































# Process #
# Citizen ID Application Portal - System Requirements and Development Guide

## Project Overview

Develop a web-based Citizen ID Application Portal using:

* Laravel 12
* PHP 8.4+
* Livewire 3
* Volt
* Tailwind CSS 4
* MySQL 8
* Laravel Breeze Authentication
* Laravel Excel for XLS Export
* Laravel Mail for Notifications

The system will allow residents of Manolo Fortich, Bukidnon to apply for a Citizen ID online and allow administrators to verify, approve, reject, and export applications.

---

# Public Portal

## Welcome Page

### Route

```php
/
```

### Features

Display the following:

* Citizen ID Program Overview
* Purpose and Benefits
* Requirements
* Sample Passport Photo
* Sample GCash Screenshot
* Terms and Conditions

### Terms and Conditions

User must check:

```text
I agree to the Terms and Conditions.
```

### Validation

The Proceed button must remain disabled until the checkbox is checked.

### Action

```text
Proceed Application
```

Redirect to Application Form.

---

# Citizen Application Form

## Route

```php
/apply
```

## Required Fields

| Field                    | Type        | Required |
| ------------------------ | ----------- | -------- |
| Email Address            | Email       | Yes      |
| Full Name                | Text        | Yes      |
| GCash Number             | Text        | Yes      |
| Province                 | Manolo Fortich    | Yes      | -> lock
| Barangay                 | Dropdown    | Yes      |
| Full Address             | Textarea    | Yes      |
| Blood Type               | Dropdown    | Yes      |
| Emergency Contact Person | Text        | Yes      |
| Emergency Contact Number | Text        | Yes      |
| Passport Photo           | File Upload | Yes      |
| GCash Screenshot         | File Upload | Yes      |

---

## Barangay Restriction

Only allow barangays under Manolo Fortich, Bukidnon.

Example:

```text
Alae
Agusan Canyon
Damilag
Dalirig
Dicklum
Guilang-Guilang
Kalugmanan
Lingion
Lunocan
Maluko
Mampayag
Mantibugao
Santiago
Tankulan
...
```

Use a dropdown field.

---

## Blood Type Dropdown

```text
A+
A-
B+
B-
AB+
AB-
O+
O-
```

---

## Passport Photo Requirements

Display sample image.

Requirements:

```text
White background
Face clearly visible
No sunglasses
No hats
Recent photo
JPG 
Maximum 5MB
```

---

## GCash Screenshot Requirements

Display sample screenshot.

Requirements:

```text
JPG
PNG
Maximum 5MB
```

---

## Submission

Upon successful submission:

### Status

```text
Pending Verification
```

### Send Email

Subject:

```text
Citizen ID Application Received
```

Message:

```text
Thank you for submitting your Citizen ID application.

Your application is currently under verification.

Please wait for another email regarding the result of your application.

```

---

# Admin Portal

## Route

```php
/admin
```

---

# Authentication

Use Laravel Breeze + Livewire.

### Roles

```text
Super Admin
Administrator
Verifier
```

---

# Dashboard

## Summary Cards

Display:

```text
Pending Applications
Approved Applications
Rejected Applications
Archived Applications
Total Applications
```

---

# New Applicants Queue

## Processing Method

First In First Out (FIFO)

SQL Ordering:

```sql
ORDER BY created_at ASC
```

Oldest applications appear first.

---

## Table Columns

| Column           |
| ---------------- |
| Full Name        |
| Barangay         |
| Date Applied     |
| Status           |
| Action           |

Action:

```text
View
```

---

# Applicant Details Page

## Route

```php
/admin/applications/{id}
```

Recommended: Dedicated Page instead of Modal.

---

## Applicant Information

Display:

```text
Reference Number
Full Name
Email Address
GCash Number
Barangay
Full Address
Blood Type
Emergency Contact Person
Emergency Contact Number
Application Date
```

---

## Uploaded Documents

### Passport Photo

Display Preview

### GCash Screenshot

Display Preview

---

# Verification Process

## Reject Application

### Button

```text
Reject
```

### Rejection Reasons

```text
Invalid Passport Photo
Invalid GCash Screenshot
Incomplete Information
Unreadable Documents
Non-Resident of Manolo Fortich
Duplicate Application
Other
```

### Additional Remarks

Textarea field.

### Result

Status:

```text
Rejected
```

### Email Notification

Subject:

```text
Citizen ID Application Rejected
```

Message:

```text
Your Citizen ID application has been rejected.

Reason:
{remarks}
```

### Archive Rule

Rejected applications are automatically moved to the Archive Section.

Citizen may submit a new application.

---

## Approve Application

### Button

```text
Approve
```

### Result

Status:

```text
Approved
```

### Save

```text
Verified By
Verified Date
```

### Email Notification

Subject:

```text
Citizen ID Application Approved
```

Message:

```text
Congratulations!

Your Citizen ID application has been approved.
```

Redirect application to Finalization Section.

---

# Finalization Page

## Menu

```text
Finalized Applications
```

---

## Table Columns

| Column           |
| ---------------- |
| Full Name        |
| Barangay         |
| Blood Type       |
| Date Approved    |
| Approved By      |

---

## Filters

```text
Search
Barangay
Status
Date Range
```

---

# Export to Excel

## Button

```text
Export XLS
```

Package:

```bash
composer require maatwebsite/excel
```

Export Format:

```text
Reference Number
Full Name
Email
GCash Number
Barangay
Address
Blood Type
Emergency Contact Person
Emergency Contact Number
Status
Date Applied
Date Approved
```

---

# Email Notifications

## Submission

Status: Pending Verification

## Approval

Status: Approved

## Rejection

Status: Rejected

---

# Activity Logs

Track:

```text
Admin Login
Admin Logout
Application Viewed
Application Approved
Application Rejected
Export Generated
```

---

# Database Design

## Table: applicants

```sql
id BIGINT PRIMARY KEY

reference_no VARCHAR(50) UNIQUE

email VARCHAR(255)

full_name VARCHAR(255)

gcash_number VARCHAR(20)

barangay VARCHAR(100)

address TEXT

blood_type VARCHAR(5)

emergency_contact_person VARCHAR(255)

emergency_contact_number VARCHAR(20)

passport_photo VARCHAR(255)

gcash_screenshot VARCHAR(255)

status ENUM(
    'Pending',
    'Approved',
    'Rejected'
)

rejection_reason TEXT NULL

verified_by BIGINT NULL

verified_at TIMESTAMP NULL

created_at TIMESTAMP

updated_at TIMESTAMP
```

---

## Table: admins

```sql
id BIGINT PRIMARY KEY

name VARCHAR(255)

email VARCHAR(255)

password VARCHAR(255)

role VARCHAR(50)

created_at TIMESTAMP

updated_at TIMESTAMP
```

---

## Table: activity_logs

```sql
id BIGINT PRIMARY KEY

admin_id BIGINT

action VARCHAR(255)

description TEXT

created_at TIMESTAMP
```

---

# Entity Relationship Diagram (ERD)

```text
+-------------------+
|      ADMINS       |
+-------------------+
| id PK             |
| name              |
| email             |
| password          |
| role              |
+-------------------+
          |
          |
          v

+-------------------+
|  ACTIVITY_LOGS    |
+-------------------+
| id PK             |
| admin_id FK       |
| action            |
| description       |
| created_at        |
+-------------------+

          ^
          |
          |

+-------------------+
|    APPLICANTS     |
+-------------------+
| id PK             |
| email             |
| full_name         |
| gcash_number      |
| province          |-> Manolo Fortich ( lock )
| barangay          |
| address           |
| blood_type        |
| emergency_contact |
| emergency_number  |
| passport_photo    |
| gcash_screenshot  |
| status            |
| rejection_reason  |
| verified_by FK    |
| verified_at       |
| created_at        |
| updated_at        |
+-------------------+
```

---

# Recommended Livewire Structure

```text
app/Livewire/

Public/
├── WelcomePage.php
├── ApplicationForm.php

Admin/
├── Dashboard.php
├── ApplicantsTable.php
├── ApplicantView.php
├── FinalizationTable.php

Exports/
├── CitizenExport.php
```

---

# Future Enhancements

Phase 2 Features:

* QR Code Verification
* Digital Citizen ID
* Printable Citizen ID
* SMS Notifications
* Barangay Verification Workflow
* Reprint Requests
* Renewal Requests
* Application Tracking Page
* GCash API Verification
* Citizen Portal Login
* Analytics Dashboard

---

# Development Standards

Follow Laravel Best Practices:

* Service Layer Architecture
* Repository Pattern
* Form Requests Validation
* Policy Authorization
* Queueable Emails
* Database Seeders
* Feature Tests
* Responsive Mobile-First UI
* Livewire Components
* Tailwind CSS 4
* Clean Code Principles
* SOLID Principles
* PSR-12 Coding Standards

The project should be production-ready, scalable, secure, and optimized for deployment on a government or municipal server.
