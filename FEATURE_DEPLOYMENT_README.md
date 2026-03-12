# Feature Deployment README

This document covers the new academic features added to the school apps in this workspace:

- `bs_kaduna`
- `bs_kd`
- `bs_abuja`

## Features Added

### 1. Student Subject Removal

Section/class teacher can remove a subject for a specific student.

Behavior:

- Removal is per student, per session
- Removed subjects no longer appear in:
  - student course list
  - marks entry flow
  - results/report views
- Subject can be restored later

Database change:

- new table: `student_course_exclusions`

### 2. Lesson Plans

Teachers can add lesson plans by:

- typing content directly
- uploading `pdf`, `doc`, or `docx`

Admins can view all lesson plans.

Database change:

- new table: `lesson_plans`

### 3. Attendance Summary On Report

The results/report pages now show, per term:

- total school days
- days present
- attendance percentage

By default, days present is calculated from attendance records in the term date range.

### 4. Attendance Override By Class Teacher

Assigned class teacher can override the attendance summary for a student per term.

Rules:

- only the assigned class teacher can save the override
- override is per student, per term, per session
- override cannot be greater than `total_school_days`

Database change:

- new table: `attendance_summary_overrides`

### 5. Affective Area Scoring

Class teacher can score the following per student, per term:

- punctuality
- neatness
- politeness
- honesty
- performance
- attentiveness
- perseverance
- speaking
- writing

Score key:

- `5` Excellent
- `4` Very Good
- `3` Good
- `2` Fair
- `1` Poor

Database change:

- new nullable column on `student_report_comments`: `affective_scores`

### 6. New Term Auto Carry

When a new semester/term is created inside the current session, the app now copies forward the previous term setup automatically.

The copied setup includes:

- courses/subjects
- section teacher assignments
- subject teacher assignments

This reduces repeated setup work when moving from one term to the next in the same academic session.

### 7. New Session Roll Over

Admins can now roll over an existing session into a new one from Academic Settings.

The roll over creates the new session and copies:

- semesters
- classes
- sections
- courses/subjects
- teacher assignments
- promotion policies

Important:

- student accounts are not recreated
- students are not automatically enrolled into the new session
- you still use promotion after rollover to place students into the new session

### 8. Promotion Workflow

The app now supports two connected promotion flows on purpose:

- automatic promotion review
- manual promotion placement

They are not duplicates. They serve different jobs.

#### Automatic Promotion Review

This is the academic decision stage.

The system:

- reads available marks and promotion policy
- calculates a suggested status
- allows teacher/admin review
- allows manual override where necessary
- allows finalization of the decision

Possible statuses:

- `promoted`
- `probation`
- `retained`

Important:

- finalizing the review does **not** place the student into the next session
- it finalizes the academic decision only

#### Manual Promotion Placement

This is the actual enrollment/placement stage.

The manual promotion screen is used to:

- choose the destination class
- choose the destination section
- save the student into the target/latest session

This separation is intentional because schools sometimes:

- promote students even if they did not strictly meet criteria
- retain or place students differently after human review
- move students between sections manually

#### Recommended Promotion Process

For a new academic session, use this order:

1. Create the new session with `Roll Over New Session`, or create the session structure manually.
2. Confirm the new session has the expected classes, sections, semesters, and subjects.
3. Open the promotion review board for the current class and section.
4. Let the app calculate the suggested status.
5. Teacher/admin reviews, corrects if needed, and finalizes the decisions.
6. Open manual promotion.
7. Place each student into the correct destination class and section for the latest session.

Important:

- students are permanent records, so they are not added again
- promotion writes the new session placement for existing students
- rollover copies structure, not student enrollment

## Migration Trigger Page

A protected route was added so migrations can be run on shared hosting by visiting a URL.

Route:

```text
/deploy/migrate
```

Usage with secret key:

1. Add this to `.env`

```env
DEPLOY_MIGRATE_KEY=your-secret-value
```

2. Pull the latest code on the server

3. Visit:

```text
https://your-domain.com/deploy/migrate?key=your-secret-value
```

If you are already logged in as an Admin, the page can also run without the key.

It executes:

```bash
php artisan migrate --force
```

After successful deployment, rotate or remove `DEPLOY_MIGRATE_KEY`.

## Migration Safety

The migrations added for these features are non-destructive.

They do the following only:

- create `student_course_exclusions`
- create `lesson_plans`
- create `attendance_summary_overrides`
- add nullable JSON column `affective_scores` to `student_report_comments`

They do **not**:

- drop existing tables
- rename existing tables
- remove existing columns
- alter existing result or attendance data

Because of that, the migration set is low risk for the current app.

## Important Notes

### 1. Existing data should remain intact

The current live data should remain intact because these schema changes are additive.

### 2. Attendance and affective features start empty

After migration:

- no student has removed subjects until a teacher removes one
- no lesson plan exists until a teacher creates one
- no attendance override exists until a class teacher sets one
- no affective scores exist until a class teacher saves them

### 3. JSON column requirement

The `affective_scores` column uses JSON. This is normally fine on modern MySQL/MariaDB versions, but if the shared host is on a very old database version, this could be the main compatibility risk.

### 4. Run once after code pull

After pulling code on each live app, trigger the migration once for that app.

## Suggested Live Deployment Steps

1. Back up the live database
2. Pull latest code from GitHub
3. Update `.env` with `DEPLOY_MIGRATE_KEY`
4. Visit `/deploy/migrate?key=...`
5. Confirm the migration output says the new migrations ran successfully
6. Log in and test:
   - student profile subject removal
   - lesson plan create/view
   - section result attendance override
   - affective score entry
   - semester creation auto-carry
   - promotion review board and manual promotion flow
7. Remove or rotate `DEPLOY_MIGRATE_KEY`
