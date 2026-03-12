// Setup data
$teacher = \App\Models\User::factory()->create([
'role_id' => 3, // Teacher
'school_id' => 1,
]);
$teacher->assignRole('Teacher');

$session_id = 1;
$class_id = 1;
$section_id = 1;

// Assign as Class Teacher (NULL section)
\App\Models\AssignedTeacher::create([
'teacher_id' => $teacher->id,
'session_id' => $session_id,
'class_id' => $class_id,
'section_id' => null,
'course_id' => null,
'semester_id' => 1
]);

// Test 1: Class Teacher Accessing Section 1
$assignments = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id);

// Apply FIX logic manually to replicate what Controller does (or we could instantiate controller but that's harder in
tinker script without request)
// We are testing IF the database query structure we wrote works.
$assignments->where(function($q) use ($section_id) {
$q->where('section_id', $section_id)
->orWhereNull('section_id');
});

echo "Test 1 (Class Teacher -> Section $section_id): " . ($assignments->exists() ? "PASS" : "FAIL") . PHP_EOL;

// Test 2: Section Teacher strict check
// Clear
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();

// Assign to Section 2
\App\Models\AssignedTeacher::create([
'teacher_id' => $teacher->id,
'session_id' => $session_id,
'class_id' => $class_id,
'section_id' => 2,
'course_id' => null,
'semester_id' => 1
]);

// Accessing Section 1
$assignments2 = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id);

$assignments2->where(function($q) use ($section_id) { // requesting section 1
$q->where('section_id', $section_id)
->orWhereNull('section_id');
});

echo "Test 2 (Section 2 Teacher -> Section 1): " . (!$assignments2->exists() ? "PASS" : "FAIL") . PHP_EOL;

// Test 3: Section Teacher accessing their OWN section
$assignments3 = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id);

$assignments3->where(function($q) use ($section_id) { // requesting section 2 (same as assigned)
$section_target = 2; // Target matches assignment
$q->where('section_id', $section_target)
->orWhereNull('section_id');
});

echo "Test 3 (Section 2 Teacher -> Section 2): " . ($assignments3->exists() ? "PASS" : "FAIL") . PHP_EOL;

// Cleanup
$teacher->delete();
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();