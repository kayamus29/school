// Setup data
$teacher = \App\Models\User::factory()->create([
'role_id' => 3, // Teacher
'school_id' => 1,
]);
$teacher->assignRole('Teacher');
Auth::login($teacher);

$session_id = 1;
$class_id = 1;

// Assign as Class Teacher (NULL section)
\App\Models\AssignedTeacher::create([
'teacher_id' => $teacher->id,
'session_id' => $session_id,
'class_id' => $class_id,
'section_id' => null,
'course_id' => null,
'semester_id' => 1
]);

// Setup
$logFile = 'C:\\Users\\kaygo\\Desktop\\Unifiedtransform\\verify_debug.txt';
file_put_contents($logFile, "Starting Verification...\n");

function logResult($message) {
global $logFile;
file_put_contents($logFile, $message . "\n", FILE_APPEND);
echo $message . "\n";
}

// Instantiate Controller
$controller = new \App\Http\Controllers\UserController(
new \App\Repositories\SchoolClassRepository()
);

// Scenario 1: Class Teacher requesting specific section (Should Pass now with my fix)
$request = \Illuminate\Http\Request::create('/students/view/list', 'GET', [
'class_id' => $class_id,
'section_id' => 1 // Requesting Section 1
]);

try {
$response = $controller->getStudentList($request);
echo "Scenario 1 (Class Teacher -> Section 1): SUCCESS (Status: " . ($response->status() ?? 'View') . ")" . PHP_EOL;
} catch (\Exception $e) {
if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
echo "Scenario 1 (Class Teacher -> Section 1): FAILED (Status: " . $e->getStatusCode() . ")" . PHP_EOL;
} else {
echo "Scenario 1 (Class Teacher -> Section 1): ERROR (" . $e->getMessage() . ")" . PHP_EOL;
}
}

// Scenario 2: Section Teacher strict check
// Clear
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();
\App\Models\AssignedTeacher::create([
'teacher_id' => $teacher->id,
'session_id' => $session_id,
'class_id' => $class_id,
'section_id' => 2,
'course_id' => null,
'semester_id' => 1
]);

// Request Section 1
$request2 = \Illuminate\Http\Request::create('/students/view/list', 'GET', [
'class_id' => $class_id,
'section_id' => 1 // Requesting Section 1
]);

try {
$response = $controller->getStudentList($request2);
logResult("Scenario 2 (Section 2 Teacher -> Section 1): UNEXPECTED SUCCESS");
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { // catch 403
logResult("Scenario 2 (Section 2 Teacher -> Section 1): EXPECTED FAIL (Status: " . $e->getStatusCode() . ")");
} catch (\Exception $e) {
logResult("Scenario 2 (Section 2 Teacher -> Section 1): ERROR (" . $e->getMessage() . ")");
}


// Scenario 3: Section Teacher requesting NO section explicit (0)
// With Redirect fix: Should Redirect to ?class_id=X&section_id=2
$request3 = \Illuminate\Http\Request::create('/students/view/list', 'GET', [
'class_id' => 0,
'section_id' => 0
]);

try {
$response = $controller->getStudentList($request3);

if ($response instanceof \Illuminate\Http\RedirectResponse) {
logResult("Scenario 3 (Section 2 Teacher -> No Section): REDIRECTED to " . $response->getTargetUrl());
// Verify parameters in target URL?
// Assuming target URL contains class_id=$class_id and section_id=2
} else {
logResult("Scenario 3: SUCCESS (No Redirect - Maybe Class Teacher logic triggered?)");
}
} catch (\Exception $e) {
logResult("Scenario 3: FAILED (" . $e->getMessage() . ")");
}

// Scenario 4: Section Teacher requesting WRONG section
$request4 = \Illuminate\Http\Request::create('/students/view/list', 'GET', [
'class_id' => $class_id,
'section_id' => 1 // Requesting Section 1 when assigned to 2
]);

try {
$response = $controller->getStudentList($request4);
// Should NOT redirect, should 403.
if ($response instanceof \Illuminate\Http\RedirectResponse) {
logResult("Scenario 4: UNEXPECTED REDIRECT");
} else {
logResult("Scenario 4 (Section 2 Teacher -> Section 1): UNEXPECTED SUCCESS");
}
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { // catch 403
logResult("Scenario 4 (Section 2 Teacher -> Section 1): EXPECTED FAIL (Status: " . $e->getStatusCode() . ")");
logResult("DEBUG MESSAGE CAUGHT: " . $e->getMessage());
} catch (\Exception $e) {
logResult("Scenario 4 (Section 2 Teacher -> Section 1): ERROR (" . $e->getMessage() . ")");
}

// Cleanup
$teacher->delete();
\App\Models\AssignedTeacher::where('teacher_id', $teacher->id)->delete();