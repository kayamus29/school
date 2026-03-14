# Portal Browser Verification Checklist

## Prerequisites
1. Start dev server: `php artisan serve`
2. Ensure database has test data (students, parents, accountants)
3. Have credentials ready for each role

---

## 1. Parent Portal Verification

### Test Case 1.1: Parent with Single Child
**Credentials**: Find a parent with 1 linked child
```sql
SELECT u.email, u.id, COUNT(spi.student_id) as child_count
FROM users u
JOIN student_parent_infos spi ON spi.parent_user_id = u.id
WHERE u.role = 'parent'
GROUP BY u.id
HAVING child_count = 1;
```

**Steps**:
1. Login with parent email (password: `password`)
2. **Expected**: Redirect directly to `/portal/parent/child/{student_id}`
3. **Verify**: Child dashboard shows attendance, marks
4. **Verify**: No "Switch Child" dropdown visible

**Pass Criteria**: ✅ Direct navigation to child dashboard

---

### Test Case 1.2: Parent with Multiple Children
**Credentials**: Find a parent with 2+ linked children

**Steps**:
1. Login with parent email
2. **Expected**: Land on `/portal/parent` (selection screen)
3. **Verify**: All children displayed as cards
4. Click "View Dashboard" for Child A
5. **Expected**: Navigate to `/portal/parent/child/{childA_id}`
6. **Verify**: "Switch Child" dropdown visible in header
7. Click dropdown, select Child B
8. **Expected**: Navigate to `/portal/parent/child/{childB_id}`
9. **Verify**: Dashboard updates to Child B's data

**Pass Criteria**: ✅ Selection screen shown, switching works

---

### Test Case 1.3: Parent Unauthorized Access (403 Test)
**Steps**:
1. Login as Parent A (with child ID = 5)
2. Manually navigate to `/portal/parent/child/999` (unlinked student)
3. **Expected**: HTTP 403 Forbidden
4. **Verify**: Error page shown, not child dashboard

**Pass Criteria**: ✅ 403 response for unlinked child

---

## 2. Student Portal Verification

### Test Case 2.1: Student Dashboard Access
**Credentials**: Any student user

**Steps**:
1. Login as student
2. **Expected**: Land on `/portal/student` (dashboard)
3. **Verify**: Attendance stats visible
4. **Verify**: Recent attendance table populated
5. **Verify**: Notices section visible
6. Click "See all" on attendance
7. **Expected**: Navigate to `/portal/student/attendance`
8. **Verify**: Paginated attendance history

**Pass Criteria**: ✅ Dashboard loads, navigation works

---

### Test Case 2.2: Student Unauthorized Access (403 Test)
**Steps**:
1. Login as Student
2. Manually navigate to `/portal/parent`
3. **Expected**: HTTP 403 Forbidden (role middleware)
4. Manually navigate to `/accounting/dashboard`
5. **Expected**: HTTP 403 Forbidden

**Pass Criteria**: ✅ Student cannot access other portals

---

## 3. Accountant Portal Verification

### Test Case 3.1: Accountant Dashboard Access
**Credentials**: Accountant user

**Steps**:
1. Login as accountant
2. Navigate to `/accounting/dashboard`
3. **Expected**: Financial dashboard loads
4. **Verify**: Fee stats, payment history visible
5. **Verify**: No academic edit buttons (attendance, marks)

**Pass Criteria**: ✅ Accountant sees financial data only

---

### Test Case 3.2: Accountant Unauthorized Access (403 Test)
**Steps**:
1. Login as Accountant
2. Manually navigate to `/attendances/take`
3. **Expected**: HTTP 403 Forbidden (permission check)
4. Manually navigate to `/marks/create`
5. **Expected**: HTTP 403 Forbidden

**Pass Criteria**: ✅ Accountant cannot access academic mutation routes

---

## 4. Cross-Portal Navigation

### Test Case 4.1: Logout and Role Switching
**Steps**:
1. Login as Parent, view Child A
2. Logout
3. Login as Student
4. **Expected**: Student dashboard, NOT parent context
5. Logout
6. Login as Parent again
7. **Expected**: Child selection screen (if multi-child)

**Pass Criteria**: ✅ No context leakage between sessions

---

## 5. UI/UX Checks

### Test Case 5.1: Parent Child Switcher Visibility
- [ ] Switcher visible ONLY when parent has 2+ children
- [ ] Switcher shows all linked children
- [ ] Active child highlighted in dropdown

### Test Case 5.2: Navigation Clarity
- [ ] Each portal has distinct header/branding
- [ ] No "Home" controller routes visible in portal URLs
- [ ] Breadcrumbs/titles clearly indicate current portal

---

## Summary Report Template

```
VERIFICATION RESULTS
====================
Parent Portal (Single Child): [ PASS / FAIL ]
Parent Portal (Multi-Child): [ PASS / FAIL ]
Parent 403 Protection: [ PASS / FAIL ]
Student Portal Access: [ PASS / FAIL ]
Student 403 Protection: [ PASS / FAIL ]
Accountant Portal Access: [ PASS / FAIL ]
Accountant 403 Protection: [ PASS / FAIL ]
Cross-Portal Isolation: [ PASS / FAIL ]

ISSUES FOUND:
1. [Description]
2. [Description]
```
