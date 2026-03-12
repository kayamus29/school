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
'section_id' => null, // Crucial
'course_id' => null,
'semester_id' => 1
]);

// Simulate Controller Logic
$query1 = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id);

// Current Logic (Strict Section Filter)
$query1->where('section_id', $section_id);

$count1 = $query1->count();

echo "Current Logic (Class Teacher accessing Section $section_id): " . ($count1 > 0 ? "ALLOWED" : "DENIED") . PHP_EOL;

// Proposed Logic (Allow Null)
$query2 = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id)
->where(function($q) use ($section_id) {
$q->where('section_id', $section_id)
->orWhereNull('section_id');
});

$count2 = $query2->count();

echo "Proposed Logic (Class Teacher accessing Section $section_id): " . ($count2 > 0 ? "ALLOWED" : "DENIED") . PHP_EOL;

// Negative Test: Assign strictly to Section 2, try to access Section 1
// Clear previous assignment
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();

\App\Models\AssignedTeacher::create([
'teacher_id' => $teacher->id,
'session_id' => $session_id,
'class_id' => $class_id,
'section_id' => 2, // Only Section 2
'course_id' => null,
'semester_id' => 1
]);

$query3 = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
->where('session_id', $session_id)
->where(function($q) use ($section_id) { // requesting section 1
$q->where('section_id', $section_id) // 1 != 2
->orWhereNull('section_id'); // 2 != null
});

$count3 = $query3->count();
echo "Negative Test (Section 2 Teacher accessing Section 1): " . ($count3 > 0 ? "ALLOWED" : "DENIED") . PHP_EOL;

// Clean up
$teacher->delete();
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();