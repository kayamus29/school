<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ExamRuleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\GradeRuleController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AssignedTeacherController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\ResultsDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::prefix('school')->name('school.')->group(function () {
        Route::post('session/create', [SchoolSessionController::class, 'store'])->name('session.store');
        Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');

        Route::post('semester/create', [SemesterController::class, 'store'])->name('semester.create');
        Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('final.marks.submission.status.update');

        Route::post('attendance/type/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('attendance.type.update');
        Route::post('default-weights/update', [AcademicSettingController::class, 'updateDefaultWeights'])->name('default.weights.update');

        // Class
        Route::post('class/create', [SchoolClassController::class, 'store'])->name('class.create');
        Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');

        // Sections
        Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
        Route::post('section/update', [SectionController::class, 'update'])->name('section.update');

        // Courses
        Route::post('course/create', [CourseController::class, 'store'])->name('course.create');
        Route::post('course/update', [CourseController::class, 'update'])->name('course.update');

        // Teacher
        Route::post('teacher/create', [UserController::class, 'storeTeacher'])->name('teacher.create');
        Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');
        Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');
        Route::post('teacher/assign/bulk', [AssignedTeacherController::class, 'bulkAssign'])->name('teacher.assign.bulk');

        // Student
        Route::post('student/create', [UserController::class, 'storeStudent'])->name('student.create');
        Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    });


    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Attendance
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    // Classes and sections
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');
    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    // Teachers
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->name('teacher.create.show');
    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');

    //Students
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/financial/{id}', [App\Http\Controllers\StudentFinancialProfileController::class, 'show'])->name('student.financial.show');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance.show');

    // Marks
    Route::get('/marks/create', [MarkController::class, 'create'])->name('course.mark.create');
    Route::post('/marks/store', [MarkController::class, 'store'])->name('course.mark.store');
    Route::get('/marks/results', [MarkController::class, 'index'])->name('course.mark.list.show');
    Route::get('/marks/view', [MarkController::class, 'showCourseMark'])->name('course.mark.show');
    Route::get('/marks/final/submit', [MarkController::class, 'showFinalMark'])->name('course.final.mark.submit.show');
    Route::post('/marks/final/submit', [MarkController::class, 'storeFinalMark'])->name('course.final.mark.submit.store');

    // Results Dashboard
    Route::get('/results/teacher', [ResultsDashboardController::class, 'teacherView'])->name('results.teacher');
    Route::get('/results/section', [ResultsDashboardController::class, 'sectionView'])->name('results.section');
    Route::get('/results/student', [ResultsDashboardController::class, 'studentView'])->name('results.student');
    Route::get('/results/student-react', [ResultsDashboardController::class, 'studentViewReact'])->name('results.student.react');
    Route::get('/results/admin', [ResultsDashboardController::class, 'adminView'])->name('results.admin');
    Route::get('/ajax/results/breakdown', [ResultsDashboardController::class, 'getBreakdownAjax'])->name('ajax.results.breakdown');
    Route::post('/report/comments/store', [App\Http\Controllers\ReportCommentController::class, 'store'])->name('report.comments.store');

    // Exams
    Route::get('/exams/view', [ExamController::class, 'index'])->name('exam.list.show');
    Route::post('/exams/create', [ExamController::class, 'store'])->name('exam.create');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exam.create.show');
    Route::get('/exams/add-rule', [ExamRuleController::class, 'create'])->name('exam.rule.create');
    Route::post('/exams/add-rule', [ExamRuleController::class, 'store'])->name('exam.rule.store');
    Route::get('/exams/edit-rule', [ExamRuleController::class, 'edit'])->name('exam.rule.edit');
    Route::post('/exams/edit-rule', [ExamRuleController::class, 'update'])->name('exam.rule.update');
    Route::get('/exams/view-rule', [ExamRuleController::class, 'index'])->name('exam.rule.show');
    Route::get('/exams/grade/create', [GradingSystemController::class, 'create'])->name('exam.grade.system.create');
    Route::post('/exams/grade/create', [GradingSystemController::class, 'store'])->name('exam.grade.system.store');
    Route::get('/exams/grade/view', [GradingSystemController::class, 'index'])->name('exam.grade.system.index');
    Route::get('/exams/grade/add-rule', [GradeRuleController::class, 'create'])->name('exam.grade.system.rule.create');
    Route::post('/exams/grade/add-rule', [GradeRuleController::class, 'store'])->name('exam.grade.system.rule.store');
    Route::get('/exams/grade/view-rules', [GradeRuleController::class, 'index'])->name('exam.grade.system.rule.show');
    Route::post('/exams/grade/delete-rule', [GradeRuleController::class, 'destroy'])->name('exam.grade.system.rule.delete');

    // Promotions
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    // New Flexible Promotion Flow Routes
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/policy', [PromotionController::class, 'policySettings'])->name('policy');
        Route::post('/policy', [PromotionController::class, 'storePolicy'])->name('policy.store');
        Route::get('/review', [PromotionController::class, 'reviewBoard'])->name('review');
        Route::post('/review/update', [PromotionController::class, 'updateReview'])->name('review.update');
        Route::post('/review/finalize', [PromotionController::class, 'finalizeBatch'])->name('review.finalize');
        Route::get('/projection', [PromotionController::class, 'studentProjection'])->name('student.projection');
    });

    // Accounting Dashboard
    Route::get('/accounting/dashboard', [App\Http\Controllers\AccountingDashboardController::class, 'index'])->name('accounting.dashboard');
    Route::get('/accounting/analytics', [App\Http\Controllers\FinancialAnalyticsController::class, 'index'])->name('accounting.analytics.index');

    // Fee Heads
    Route::get('/accounting/fees/heads', [App\Http\Controllers\FeeHeadController::class, 'index'])->name('accounting.fees.heads.index');
    Route::post('/accounting/fees/heads', [App\Http\Controllers\FeeHeadController::class, 'store'])->name('accounting.fees.heads.store');
    Route::delete('/accounting/fees/heads/{id}', [App\Http\Controllers\FeeHeadController::class, 'destroy'])->name('accounting.fees.heads.destroy');

    // Class Fees
    Route::get('/accounting/fees/assign', [App\Http\Controllers\ClassFeeController::class, 'index'])->name('accounting.fees.class.index');
    Route::post('/accounting/fees/assign', [App\Http\Controllers\ClassFeeController::class, 'store'])->name('accounting.fees.class.store');
    Route::post('/accounting/fees/bulk-bill', [App\Http\Controllers\ClassFeeController::class, 'generateBills'])->name('accounting.fees.class.bulk_bill');
    Route::delete('/accounting/fees/assign/{id}', [App\Http\Controllers\ClassFeeController::class, 'destroy'])->name('accounting.fees.class.destroy');

    // AJAX Fee Management Endpoints
    Route::prefix('ajax/accounting/fees')->group(function () {
        Route::get('/list', [App\Http\Controllers\ClassFeeController::class, 'getFeesAjax'])->name('accounting.fees.ajax.list');
        Route::post('/store', [App\Http\Controllers\ClassFeeController::class, 'storeAjax'])->name('accounting.fees.ajax.store');
        Route::delete('/destroy/{id}', [App\Http\Controllers\ClassFeeController::class, 'destroyAjax'])->name('accounting.fees.ajax.destroy');
        Route::get('/bulk-preview', [App\Http\Controllers\ClassFeeController::class, 'getBulkPreviewAjax'])->name('accounting.fees.ajax.bulk_preview');
        Route::post('/copy-term', [App\Http\Controllers\ClassFeeController::class, 'copyTermFeesAjax'])->name('accounting.fees.ajax.copy_term');
    });

    // Student Fees
    Route::get('/accounting/fees/student', [App\Http\Controllers\StudentFeeController::class, 'index'])->name('accounting.fees.student.index');
    Route::get('/accounting/fees/student/outstanding/{student_id}', [App\Http\Controllers\StudentFeeController::class, 'getOutstanding'])->name('accounting.fees.student.outstanding');
    Route::post('/accounting/fees/student', [App\Http\Controllers\StudentFeeController::class, 'store'])->name('accounting.fees.student.store');
    Route::delete('/accounting/fees/student/{id}', [App\Http\Controllers\StudentFeeController::class, 'destroy'])->name('accounting.fees.student.destroy');

    // Payments
    Route::get('/accounting/payments', [App\Http\Controllers\PaymentController::class, 'index'])->name('accounting.payments.index');
    Route::get('/accounting/payments/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('accounting.payments.create');
    Route::post('/accounting/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('accounting.payments.store');
    Route::get('/accounting/payments/{id}', [App\Http\Controllers\PaymentController::class, 'show'])->name('accounting.payments.show');
    Route::get('/accounting/payments/student/{id}/details', [App\Http\Controllers\PaymentController::class, 'getStudentDetails'])->name('accounting.payments.student.details');

    // Expenses
    Route::get('/accounting/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('accounting.expenses.index');
    Route::get('/accounting/my-expenses', [App\Http\Controllers\ExpenseController::class, 'myExpenses'])->name('accounting.expenses.my');


    Route::get('/accounting/debtors', [App\Http\Controllers\AccountingDashboardController::class, 'debtors'])->name('accounting.debtors.index');
    Route::post('/accounting/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('accounting.expenses.store');
    Route::post('/accounting/expenses/{id}/status', [App\Http\Controllers\ExpenseController::class, 'updateStatus'])->name('accounting.expenses.updateStatus');
    Route::post('/accounting/expenses/{id}/correct', [App\Http\Controllers\ExpenseController::class, 'correct'])->name('accounting.expenses.correct');
    Route::delete('/accounting/expenses/{id}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('accounting.expenses.destroy');

    // Staff Management
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/edit/{id}', [StaffController::class, 'edit'])->name('staff.edit');
    Route::post('/staff/update/{id}', [StaffController::class, 'update'])->name('staff.update');

    // Staff Attendance
    Route::get('/staff/attendance', [StaffAttendanceController::class, 'index'])->name('staff.attendance.index');
    Route::post('/staff/attendance/check-in', [StaffAttendanceController::class, 'checkIn'])->name('staff.attendance.checkin');
    Route::post('/staff/attendance/check-out', [StaffAttendanceController::class, 'checkOut'])->name('staff.attendance.checkout');

    // Audit Logs
    Route::get('/audit/logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit/logs/{id}', [AuditLogController::class, 'show'])->name('audit.show');

    // Academic settings
    Route::get('/academics/settings', [AcademicSettingController::class, 'index']);
    Route::post('/academics/settings/attendance-type/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('school.attendance.type.update');
    Route::post('/academics/settings/final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('school.final.marks.submission.status.update');
    Route::post('/academics/settings/default-weights/update', [AcademicSettingController::class, 'updateDefaultWeights'])->name('school.default.weights.update');
    Route::post('/academics/settings/financial-withholding/update', [AcademicSettingController::class, 'updateFinancialWithholding'])->name('school.financial.withholding.update');
    Route::post('/academics/settings/final-grades/update', [AcademicSettingController::class, 'updateFinalGrades'])->name('school.final.grades.update');
    Route::get('/academics/graduation', [App\Http\Controllers\GraduationController::class, 'index'])->name('academics.graduation.index');
    Route::post('/academics/graduation/{id}/finalize', [App\Http\Controllers\GraduationController::class, 'finalize'])->name('academics.graduation.finalize');

    // Site Settings (Whitelabeling)
    Route::get('/settings/site', [SiteSettingController::class, 'edit'])->name('settings.site.edit');
    Route::post('/settings/site', [SiteSettingController::class, 'update'])->name('settings.site.update');

    // Calendar events
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    // Routines
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');

    // Syllabus
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    // Notices
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create', [NoticeController::class, 'store'])->name('notice.store');

    // Courses
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // Student Lifecycle
    Route::post('/students/{id}/deactivate', [App\Http\Controllers\StudentStatusController::class, 'deactivate'])->name('student.deactivate');
    Route::post('/students/{id}/reactivate', [App\Http\Controllers\StudentStatusController::class, 'reactivate'])->name('student.reactivate');

    // Assignment
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    // Update password
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.edit');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.update');

    // ===========================================
    // PORTALS (New Implementation)
    // ===========================================

    // Student Portal
    Route::prefix('portal/student')->name('student.')->group(function () {
        Route::get('/', [App\Http\Controllers\StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [App\Http\Controllers\StudentPortalController::class, 'attendance'])->name('attendance');
        Route::get('/marks', [App\Http\Controllers\StudentPortalController::class, 'marks'])->name('marks');
        Route::get('/fees', [App\Http\Controllers\StudentPortalController::class, 'fees'])->name('fees');
        Route::get('/timetable', [App\Http\Controllers\StudentPortalController::class, 'timetable'])->name('timetable');
    });

});

