<?php
/**
 * sso_login.php
 * ─────────────────────────────────────────────────────────────────────────────
 * SSO Bridge – Receives authenticated user data from Spring Boot,
 * upserts into db_eval (student / teacher), creates a PHP session,
 * and redirects to the correct dashboard — bypassing the login page.
 *
 * Role Logic:
 *   ┌─────────────────┬───────────────┬──────────────────────────┐
 *   │ Role            │ Table         │ Redirect                 │
 *   ├─────────────────┼───────────────┼──────────────────────────┤
 *   │ Student         │ student       │ students/dash.php         │
 *   ├─────────────────┼───────────────┼──────────────────────────┤
 *   │ Faculty         │ teacher       │ teachers/dash.php         │
 *   │ Admin           │ teacher       │ teachers/dash.php         │
 *   │ Hackathon User  │ teacher       │ teachers/dash.php         │
 *   │ SaaS User       │ teacher       │ teachers/dash.php         │
 *   │ SaaS Admin      │ teacher       │ teachers/dash.php         │
 *   │ Lab User        │ teacher       │ teachers/dash.php         │
 *   ├─────────────────┼───────────────┼──────────────────────────┤
 *   │ Guest           │ BLOCKED       │ 403                      │
 *   └─────────────────┴───────────────┴──────────────────────────┘
 *
 * Expected JSON body from Vue:
 * {
 *   "roles":     ["Faculty"] | ["Student"] | ["Admin"] | ["Guest"] | ...
 *   "name":      "pranay",
 *   "sessionId": 4,
 *   "isActive":  "Y",
 *   "userId":    2,
 *   "email":     "pranay.bochkar@finvedic.com",
 *   "status":    "active",
 *   "username":  "pranay.bochkar@finvedic.com"
 * }
 *
 * DB Prerequisites (run once):
 *   ALTER TABLE student ADD UNIQUE (email);
 *   ALTER TABLE student ADD UNIQUE (uname);
 *   ALTER TABLE teacher ADD UNIQUE (email);
 *   ALTER TABLE teacher ADD UNIQUE (uname);
 * ─────────────────────────────────────────────────────────────────────────────
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ── CORS Headers ──────────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:9000');  // your Vue origin
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Only accept POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// ── Parse JSON body ───────────────────────────────────────────────────────────
$body = file_get_contents('php://input');

if (empty($body)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Empty request body'
    ]);
    exit;
}

$data = json_decode($body, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// ── Extract & Sanitise Fields ─────────────────────────────────────────────────
$roles     = isset($data['roles'])     && is_array($data['roles']) ? $data['roles'] : [];
$name      = isset($data['name'])      ? trim($data['name'])       : '';
$email     = isset($data['email'])     ? trim($data['email'])      : '';
$username  = isset($data['username'])  ? trim($data['username'])   : '';
$userId    = isset($data['userId'])    ? (int)$data['userId']      : 0;
$sessionId = isset($data['sessionId']) ? (int)$data['sessionId']  : 0;
$isActive  = isset($data['isActive'])  ? trim($data['isActive'])   : 'N';
$status    = isset($data['status'])    ? trim($data['status'])     : '';

// ── Field Validation ──────────────────────────────────────────────────────────
$errors = [];
if (empty($email))    $errors[] = 'email is required';
if (empty($username)) $errors[] = 'username is required';
if (empty($roles))    $errors[] = 'roles are required';
if (empty($name))     $errors[] = 'name is required';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed: ' . implode(', ', $errors)
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format: ' . $email
    ]);
    exit;
}

// ── Check Active Status ───────────────────────────────────────────────────────
if ($isActive !== 'Y') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'User account is not active (isActive=' . $isActive . ')'
    ]);
    exit;
}

if (strtolower($status) !== 'active') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'User account status is not active (status=' . $status . ')'
    ]);
    exit;
}

// ── DB Connection ─────────────────────────────────────────────────────────────
// Save incoming SSO username to avoid collision with DB config vars
$incoming_uname = $username;

require_once __DIR__ . '/config.php'; // provides $conn (mysqli)

// restore SSO username (config.php defines $username for DB user)
if (isset($incoming_uname)) {
    $username = $incoming_uname;
}

if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// ── Role Resolution ───────────────────────────────────────────────────────────
// Normalise all incoming roles to lowercase for comparison
$normalisedRoles = array_map('strtolower', $roles);

/**
 * STUDENT roles  → student table → students/dash.php
 * TEACHER roles  → teacher table → teachers/dash.php
 * GUEST          → BLOCKED (403)
 *
 * Roles that map to STUDENT table:
 *   saas user | saasuser
 *
 * Roles that map to TEACHER table:
 *   faculty | teacher | admin | hackathon user | saas admin | lab user
 */
$studentRoles = ['saasuser', 'saas user'];

$teacherRoles = [
    'faculty',
    'teacher',
    'admin',
    'hackathon user',
    'hackathonuser',
    'saas admin',
    'saasadmin',
    'lab user',
    'labuser',
];

$blockedRoles = ['guest'];

// Check role priority: Student first, then Teacher roles, then block
$resolvedRole = null;

foreach ($normalisedRoles as $role) {
    if (in_array($role, $studentRoles)) {
        $resolvedRole = 'student';
        break; // student found, stop checking
    }
}

if ($resolvedRole === null) {
    foreach ($normalisedRoles as $role) {
        if (in_array($role, $teacherRoles)) {
            $resolvedRole = 'teacher';
            $resolvedRoleLabel = $role; // keep original label for subject field
            break;
        }
    }
}

// Map role label to a readable subject default for teacher table
$roleToSubject = [
    'faculty'       => 'FACULTY',
    'teacher'       => 'TEACHER',
    'admin'         => 'ADMIN',
    'hackathon user'=> 'HACKATHON',
    'hackathonuser' => 'HACKATHON',
    'saas user'     => 'SAAS',
    'saasuser'      => 'SAAS',
    'saas admin'    => 'SAAS_ADMIN',
    'saasadmin'     => 'SAAS_ADMIN',
    'lab user'      => 'LAB',
    'labuser'       => 'LAB',
];

if ($resolvedRole === null) {
    // Check if all roles are guest/blocked
    $allBlocked = true;
    foreach ($normalisedRoles as $role) {
        if (!in_array($role, $blockedRoles)) {
            $allBlocked = false;
        }
    }

    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Role not permitted. Received: ' . implode(', ', $roles)
    ]);
    exit;
}

// ── Sentinel password (SSO users never log in manually with this) ─────────────
$sentinelPword = md5('SSO_MANAGED_' . $userId . '_' . $email);

// ── Truncate fname safely for char(100) ──────────────────────────────────────
$fname = substr($name, 0, 100);


// =============================================================================
// STUDENT PATH
// student role → insert/find in `student` table → students/dash.php
// =============================================================================
if ($resolvedRole === 'student') {

    // ── 1. Check if student already exists (by email OR uname) ───────────────
    $stmt = $conn->prepare("SELECT * FROM student WHERE email = ? OR uname = ? LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ── EXISTS → use existing record ──────────────────────────────────────
        $row    = $result->fetch_assoc();
        $action = 'existing';
        $stmt->close();

    } else {
        // ── NOT EXISTS → INSERT from Spring data ──────────────────────────────
        $stmt->close();

        $dob    = '2000-01-01'; // default — Spring has no DOB field
        $gender = 'M';          // default — Spring has no gender field

        $ins = $conn->prepare(
            "INSERT INTO student (uname, pword, fname, dob, gender, email)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE uname = uname"
        );
        if (!$ins) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB prepare error (insert): ' . $conn->error]);
            exit;
        }
        $ins->bind_param('ssssss', $username, $sentinelPword, $fname, $dob, $gender, $email);

        if (!$ins->execute()) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to insert student: ' . $ins->error
            ]);
            $ins->close();
            exit;
        }

        $newId = $conn->insert_id;
        $ins->close();

        // ── Fetch freshly inserted row ────────────────────────────────────────
        $stmt2 = $conn->prepare("SELECT * FROM student WHERE id = ? LIMIT 1");
        if (!$stmt2) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB prepare error (fetch): ' . $conn->error]);
            exit;
        }
        $stmt2->bind_param('i', $newId);
        $stmt2->execute();
        $row = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        $action = 'inserted';
    }

    if (!$row) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not retrieve student record']);
        exit;
    }

    // ── 2. Build $_SESSION — mirrors login_student.php exactly ───────────────
    $_SESSION['user_id']        = $row['id'];
    $_SESSION['id']             = $row['id'];
    $_SESSION['fname']          = $row['fname'];
    $_SESSION['email']          = $row['email'];
    $_SESSION['dob']            = $row['dob'];
    $_SESSION['gender']         = $row['gender'];
    $_SESSION['uname']          = $row['uname'];
    $_SESSION['img']            = ($row['gender'] === 'F') ? '../img/fp.png' : '../img/mp.png';
    // SSO Meta
    $_SESSION['sso_user_id']    = $userId;
    $_SESSION['sso_session_id'] = $sessionId;
    $_SESSION['sso_roles']      = $roles;

    // ── 3. Return success response ────────────────────────────────────────────
    echo json_encode([
        'success'  => true,
        'role'     => 'student',
        'action'   => $action,             // 'existing' or 'inserted'
        'redirect' => 'students/dash.php',
        'message'  => 'Session created for student: ' . $row['fname']
    ]);
    exit;
}


// =============================================================================
// TEACHER PATH
// Faculty | Admin | Hackathon User | SaaS User | SaaS Admin | Lab User
// → insert/find in `teacher` table → teachers/dash.php
// =============================================================================
if ($resolvedRole === 'teacher') {

    // Determine subject label from role
    $subject = isset($resolvedRoleLabel) && isset($roleToSubject[$resolvedRoleLabel])
        ? $roleToSubject[$resolvedRoleLabel]
        : strtoupper(implode('_', $roles)); // fallback: use role name as subject

    $subject = substr($subject, 0, 100); // safe for varchar(100)

    // ── 1. Check if teacher already exists (by email OR uname) ───────────────
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE email = ? OR uname = ? LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ── EXISTS → use existing record ──────────────────────────────────────
        $row    = $result->fetch_assoc();
        $action = 'existing';
        $stmt->close();

    } else {
        // ── NOT EXISTS → INSERT from Spring data ──────────────────────────────
        $stmt->close();

        $dob    = '1990-01-01'; // default — Spring has no DOB field
        $gender = 'M';          // default — Spring has no gender field

        $ins = $conn->prepare(
            "INSERT INTO teacher (uname, pword, fname, dob, gender, email, subject)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE uname = uname"
        );
        if (!$ins) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB prepare error (insert): ' . $conn->error]);
            exit;
        }
        $ins->bind_param('sssssss', $username, $sentinelPword, $fname, $dob, $gender, $email, $subject);

        if (!$ins->execute()) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to insert teacher: ' . $ins->error
            ]);
            $ins->close();
            exit;
        }

        $newId = $conn->insert_id;
        $ins->close();

        // ── Fetch freshly inserted row ────────────────────────────────────────
        $stmt2 = $conn->prepare("SELECT * FROM teacher WHERE id = ? LIMIT 1");
        if (!$stmt2) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB prepare error (fetch): ' . $conn->error]);
            exit;
        }
        $stmt2->bind_param('i', $newId);
        $stmt2->execute();
        $row = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        $action = 'inserted';
    }

    if (!$row) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not retrieve teacher record']);
        exit;
    }

    // ── 2. Build $_SESSION — mirrors login_teacher.php exactly ───────────────
    $_SESSION['user_id']        = $row['id'];
    $_SESSION['fname']          = $row['fname'];
    $_SESSION['email']          = $row['email'];
    $_SESSION['dob']            = $row['dob'];
    $_SESSION['gender']         = $row['gender'];
    $_SESSION['uname']          = $row['uname'];
    $_SESSION['subject']        = $row['subject'];
    $_SESSION['img']            = in_array($row['gender'], ['F', 'Female']) ? '../img/fp.png' : '../img/mp.png';
    // SSO Meta
    $_SESSION['sso_user_id']    = $userId;
    $_SESSION['sso_session_id'] = $sessionId;
    $_SESSION['sso_roles']      = $roles;

    // ── 3. Return success response ────────────────────────────────────────────
    echo json_encode([
        'success'  => true,
        'role'     => implode(', ', $roles),  // return original role name(s)
        'action'   => $action,                // 'existing' or 'inserted'
        'redirect' => 'teachers/dash.php',
        'message'  => 'Session created for ' . implode(', ', $roles) . ': ' . $row['fname']
    ]);
    exit;
}