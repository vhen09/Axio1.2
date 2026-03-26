<?php
// Auto-initialize database fallback on every request
@require_once __DIR__ . '/../config/auto-setup.php';

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

function toBool($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
}

function clampScore($value) {
    $num = intval($value);
    if ($num < 0) return 0;
    if ($num > 100) return 100;
    return $num;
}

function parseStoredInput($raw) {
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'proof_text' => strval($raw ?? ''),
            'theorem_text' => '',
            'is_completed' => false,
            'is_verified' => false,
            'verification_status' => 'pending',
            'total_steps' => 0,
            'verified_steps' => 0,
            'steps' => [],
            'live_score' => 0,
            'started_at' => null,
            'last_edited_at' => null,
            'completed_at' => null,
            'incorrect_steps' => 0,
            'hints_used' => 0,
            'verification_attempts' => 0,
            'avg_verification_attempts_per_step' => 0,
            'completion_seconds' => 0
        ];
    }

    $steps = isset($decoded['steps']) && is_array($decoded['steps']) ? $decoded['steps'] : [];

    return [
        'proof_text' => strval($decoded['proof_text'] ?? ''),
        'theorem_text' => strval($decoded['theorem_text'] ?? ''),
        'is_completed' => toBool($decoded['is_completed'] ?? false),
        'is_verified' => toBool($decoded['is_verified'] ?? false),
        'verification_status' => strval($decoded['verification_status'] ?? 'pending'),
        'total_steps' => intval($decoded['total_steps'] ?? count($steps)),
        'verified_steps' => intval($decoded['verified_steps'] ?? 0),
        'steps' => $steps,
        'live_score' => intval($decoded['live_score'] ?? 0),
        'started_at' => $decoded['started_at'] ?? ($decoded['proof_meta']['started_at'] ?? null),
        'last_edited_at' => $decoded['last_edited_at'] ?? ($decoded['proof_meta']['last_edited_at'] ?? null),
        'completed_at' => $decoded['completed_at'] ?? ($decoded['proof_meta']['completed_at'] ?? null),
        'incorrect_steps' => intval($decoded['incorrect_steps'] ?? ($decoded['proof_meta']['incorrect_steps'] ?? 0)),
        'hints_used' => intval($decoded['hints_used'] ?? ($decoded['proof_meta']['hints_used'] ?? 0)),
        'verification_attempts' => intval($decoded['verification_attempts'] ?? ($decoded['proof_meta']['verification_attempts'] ?? 0)),
        'avg_verification_attempts_per_step' => floatval($decoded['avg_verification_attempts_per_step'] ?? ($decoded['proof_meta']['avg_verification_attempts_per_step'] ?? 0)),
        'completion_seconds' => intval($decoded['completion_seconds'] ?? ($decoded['proof_meta']['completion_seconds'] ?? 0))
    ];
}

function resolveUserId(PDO $db, $providedUserId = null) {
    if ($providedUserId && intval($providedUserId) > 0) {
        return intval($providedUserId);
    }

    $stmt = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    $existing = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    if ($existing && isset($existing['id'])) {
        return intval($existing['id']);
    }

    $username = 'demo_user';
    $passwordHash = password_hash('demo_password', PASSWORD_BCRYPT);
    $insert = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insert->execute([$username, $passwordHash]);
    return intval($db->lastInsertId());
}

function clearUserData(PDO $db, $userId) {
    $db->beginTransaction();
    try {
        $submissionIdsStmt = $db->prepare("SELECT id FROM submissions WHERE user_id = ?");
        $submissionIdsStmt->execute([$userId]);
        $submissionIds = array_map(function ($row) {
            return intval($row['id'] ?? 0);
        }, $submissionIdsStmt->fetchAll(PDO::FETCH_ASSOC));

        $deletedScores = 0;
        if (!empty($submissionIds)) {
            $placeholders = implode(',', array_fill(0, count($submissionIds), '?'));
            $deleteScoresStmt = $db->prepare("DELETE FROM scores WHERE submission_id IN ({$placeholders})");
            $deleteScoresStmt->execute($submissionIds);
            $deletedScores = $deleteScoresStmt->rowCount();
        }

        $deleteSubmissionsStmt = $db->prepare("DELETE FROM submissions WHERE user_id = ?");
        $deleteSubmissionsStmt->execute([$userId]);
        $deletedSubmissions = $deleteSubmissionsStmt->rowCount();

        $db->commit();
        return [
            'deleted_submissions' => $deletedSubmissions,
            'deleted_scores' => $deletedScores
        ];
    } catch (Exception $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

function deleteSingleSubmission(PDO $db, $userId, $submissionId) {
    $db->beginTransaction();
    try {
        $ownStmt = $db->prepare("SELECT id FROM submissions WHERE id = ? AND user_id = ? LIMIT 1");
        $ownStmt->execute([$submissionId, $userId]);
        $owned = $ownStmt->fetch(PDO::FETCH_ASSOC);

        if (!$owned) {
            throw new RuntimeException('Submission not found for current user');
        }

        $scoreDeleteStmt = $db->prepare("DELETE FROM scores WHERE submission_id = ?");
        $scoreDeleteStmt->execute([$submissionId]);
        $deletedScores = $scoreDeleteStmt->rowCount();

        $submissionDeleteStmt = $db->prepare("DELETE FROM submissions WHERE id = ? AND user_id = ?");
        $submissionDeleteStmt->execute([$submissionId, $userId]);
        $deletedSubmissions = $submissionDeleteStmt->rowCount();

        $db->commit();
        return [
            'deleted_submissions' => $deletedSubmissions,
            'deleted_scores' => $deletedScores
        ];
    } catch (Exception $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

function findOwnedSubmission(PDO $db, $userId, $submissionId) {
    if (!$submissionId || intval($submissionId) <= 0) return null;
    $stmt = $db->prepare("SELECT id FROM submissions WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([intval($submissionId), intval($userId)]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && isset($row['id']) ? intval($row['id']) : null;
}

function getTableColumns(PDO $db, $tableName) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return array_map(function ($row) {
            return strtolower(strval($row['Field'] ?? ''));
        }, $rows);
    } catch (Exception $error) {
        return [];
    }
}

function pickColumn(array $columns, array $candidates, $fallback = null) {
    foreach ($candidates as $candidate) {
        if (in_array(strtolower($candidate), $columns, true)) {
            return strtolower($candidate);
        }
    }
    return $fallback;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Database is unavailable. Please ensure your database is configured properly and accessible. Contact support if the issue persists.'
        ]);
        error_log('Submissions API: Database connection failed');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sessionUserId = $_SESSION['user_id'] ?? null;
        $queryUserId = $_GET['user_id'] ?? null;
        $userId = $queryUserId ? intval($queryUserId) : ($sessionUserId ? intval($sessionUserId) : null);

        $submissionCols = getTableColumns($db, 'submissions');
        $scoreCols = getTableColumns($db, 'scores');

        $submissionCreatedCol = pickColumn($submissionCols, ['created_at', 'submitted_at', 'updated_at', 'timestamp'], null);
        $scoreValueCol = pickColumn($scoreCols, ['score', 'points', 'value', 'grade'], null);
        $scoreSubmissionCol = pickColumn($scoreCols, ['submission_id', 'proof_id'], null);

        $submissionCreatedExpr = $submissionCreatedCol ? "s.`{$submissionCreatedCol}`" : "NULL";
        $scoreValueExpr = $scoreValueCol ? "MAX(sc.`{$scoreValueCol}`)" : "NULL";

        $scoreJoin = '';
        if ($scoreSubmissionCol) {
            $scoreJoin = " LEFT JOIN scores sc ON sc.`{$scoreSubmissionCol}` = s.id ";
        }

        $query = "SELECT s.id, s.user_id, s.input_text, {$submissionCreatedExpr} AS created_at, {$scoreValueExpr} AS score
                  FROM submissions s
                  {$scoreJoin}";

        $params = [];
        if ($userId) {
            $query .= " WHERE s.user_id = ?";
            $params[] = $userId;
        }

        $query .= " GROUP BY s.id, s.user_id, s.input_text, {$submissionCreatedExpr}
                ORDER BY {$submissionCreatedExpr} DESC, s.id DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $submissions = array_map(function($row) {
            $parsed = parseStoredInput($row['input_text'] ?? '');
            return [
                'id' => intval($row['id'] ?? 0),
                'user_id' => intval($row['user_id'] ?? 0),
                'proof_text' => $parsed['proof_text'],
                'theorem_text' => $parsed['theorem_text'],
                'input_text' => $parsed['proof_text'],
                'score' => isset($row['score']) ? intval($row['score']) : intval($parsed['live_score'] ?? 0),
                'is_completed' => $parsed['is_completed'],
                'is_verified' => $parsed['is_verified'],
                'verification_status' => $parsed['verification_status'],
                'total_steps' => $parsed['total_steps'],
                'verified_steps' => $parsed['verified_steps'],
                'steps' => $parsed['steps'],
                'live_score' => intval($parsed['live_score'] ?? 0),
                'started_at' => $parsed['started_at'],
                'last_edited_at' => $parsed['last_edited_at'],
                'completed_at' => $parsed['completed_at'],
                'incorrect_steps' => $parsed['incorrect_steps'],
                'hints_used' => $parsed['hints_used'],
                'verification_attempts' => $parsed['verification_attempts'],
                'avg_verification_attempts_per_step' => $parsed['avg_verification_attempts_per_step'],
                'completion_seconds' => $parsed['completion_seconds'],
                'created_at' => $row['created_at'] ?? null
            ];
        }, $rows);

        echo json_encode([
            'success' => true,
            'submissions' => $submissions
        ]);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit();
    }

    $action = $input['action'] ?? 'save_complete_proof';

    if ($action === 'clear_user_data') {
        $requestedUserId = $_SESSION['user_id'] ?? ($input['user_id'] ?? null);
        $userId = resolveUserId($db, $requestedUserId);
        $result = clearUserData($db, $userId);

        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'deleted_submissions' => intval($result['deleted_submissions'] ?? 0),
            'deleted_scores' => intval($result['deleted_scores'] ?? 0)
        ]);
        exit();
    }

    if ($action === 'delete_submission') {
        $requestedUserId = $_SESSION['user_id'] ?? ($input['user_id'] ?? null);
        $userId = resolveUserId($db, $requestedUserId);
        $submissionId = intval($input['submission_id'] ?? 0);

        if ($submissionId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid submission id']);
            exit();
        }

        $result = deleteSingleSubmission($db, $userId, $submissionId);
        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'submission_id' => $submissionId,
            'deleted_submissions' => intval($result['deleted_submissions'] ?? 0),
            'deleted_scores' => intval($result['deleted_scores'] ?? 0)
        ]);
        exit();
    }

    if ($action === 'save_draft_proof') {
        $requestedUserId = $_SESSION['user_id'] ?? ($input['user_id'] ?? null);
        $userId = resolveUserId($db, $requestedUserId);
        $theoremText = trim(strval($input['theorem'] ?? ''));
        $steps = isset($input['steps']) && is_array($input['steps']) ? $input['steps'] : [];
        $verificationSummary = trim(strval($input['verification_summary'] ?? ''));
        $proofMeta = isset($input['proof_meta']) && is_array($input['proof_meta']) ? $input['proof_meta'] : [];
        $requestedSubmissionId = intval($input['submission_id'] ?? 0);

        $proofLines = [];
        $verifiedSteps = 0;
        foreach ($steps as $idx => $step) {
            $text = trim(strval($step['text'] ?? ''));
            if ($text === '') continue;
            $verified = toBool($step['verified'] ?? false);
            if ($verified) $verifiedSteps++;
            $proofLines[] = 'Step ' . ($idx + 1) . ': ' . $text;
        }

        if ($theoremText === '' && empty($proofLines)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Draft requires theorem text or at least one step']);
            exit();
        }

        $storedPayload = [
            'theorem_text' => $theoremText,
            'proof_text' => implode("\n", $proofLines),
            'steps' => $steps,
            'total_steps' => count($steps),
            'verified_steps' => $verifiedSteps,
            'live_score' => clampScore($input['live_score'] ?? 0),
            'started_at' => strval($proofMeta['started_at'] ?? date('c')),
            'last_edited_at' => strval($proofMeta['last_edited_at'] ?? date('c')),
            'incorrect_steps' => intval($proofMeta['incorrect_steps'] ?? max(0, count($steps) - $verifiedSteps)),
            'hints_used' => intval($proofMeta['hints_used'] ?? 0),
            'verification_attempts' => intval($proofMeta['verification_attempts'] ?? 0),
            'avg_verification_attempts_per_step' => floatval($proofMeta['avg_verification_attempts_per_step'] ?? 0),
            'completion_seconds' => intval($proofMeta['completion_seconds'] ?? 0),
            'is_completed' => false,
            'is_verified' => false,
            'verification_status' => 'pending',
            'verification_summary' => mb_substr($verificationSummary, 0, 2000)
        ];

        $ownedSubmissionId = findOwnedSubmission($db, $userId, $requestedSubmissionId);
        if ($ownedSubmissionId) {
            $updateSubmission = $db->prepare("UPDATE submissions SET input_text = ? WHERE id = ? AND user_id = ?");
            $updateSubmission->execute([json_encode($storedPayload, JSON_UNESCAPED_UNICODE), $ownedSubmissionId, $userId]);
            $submissionId = $ownedSubmissionId;
        } else {
            $insertSubmission = $db->prepare("INSERT INTO submissions (user_id, input_text) VALUES (?, ?)");
            $insertSubmission->execute([$userId, json_encode($storedPayload, JSON_UNESCAPED_UNICODE)]);
            $submissionId = intval($db->lastInsertId());
        }

        echo json_encode([
            'success' => true,
            'submission_id' => intval($submissionId),
            'is_completed' => false,
            'is_verified' => false,
            'verification_status' => 'pending'
        ]);
        exit();
    }

    if ($action !== 'save_complete_proof') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit();
    }

    $requestedUserId = $_SESSION['user_id'] ?? ($input['user_id'] ?? null);
    $userId = resolveUserId($db, $requestedUserId);
    $theoremText = trim(strval($input['theorem'] ?? ''));
    $steps = isset($input['steps']) && is_array($input['steps']) ? $input['steps'] : [];
    $isCompleted = toBool($input['is_completed'] ?? false);
    $isVerified = toBool($input['is_verified'] ?? false);
    $verificationStatus = strtolower(trim(strval($input['verification_status'] ?? 'pending')));
    $verificationSummary = trim(strval($input['verification_summary'] ?? ''));
    $proofMeta = isset($input['proof_meta']) && is_array($input['proof_meta']) ? $input['proof_meta'] : [];
    $draftSubmissionId = intval($input['draft_submission_id'] ?? 0);

    if (strlen($theoremText) < 5) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Theorem statement is required']);
        exit();
    }

    if (count($steps) < 2) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Proof must contain at least 2 verified steps']);
        exit();
    }

    $verifiedSteps = 0;
    $proofLines = [];

    foreach ($steps as $idx => $step) {
        $text = trim(strval($step['text'] ?? ''));
        $verified = toBool($step['verified'] ?? false);

        if (strlen($text) < 10) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Each proof step must be meaningful and complete']);
            exit();
        }

        if (!$verified) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'All proof steps must be verified before completion']);
            exit();
        }

        $verifiedSteps++;
        $proofLines[] = 'Step ' . ($idx + 1) . ': ' . $text;
    }

    $finalStep = strtolower(trim(strval($steps[count($steps) - 1]['text'] ?? '')));
    $hasConclusion = preg_match('/(therefore|hence|thus|qed|proved|proven|conclude|so\s+we\s+have|∎|■)/i', $finalStep) === 1;
    if (!$hasConclusion) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Final step must explicitly state a proof conclusion']);
        exit();
    }

    if (!$isCompleted || !$isVerified || $verificationStatus !== 'success') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Only completed and verified proofs can be saved']);
        exit();
    }

    $proofText = implode("\n", $proofLines);
    $score = clampScore($input['score'] ?? 100);

    if ($score < 80) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Complete proof must pass verification with a strong score']);
        exit();
    }

    $storedPayload = [
        'theorem_text' => $theoremText,
        'proof_text' => $proofText,
        'steps' => $steps,
        'total_steps' => count($steps),
        'verified_steps' => $verifiedSteps,
        'live_score' => $score,
        'started_at' => strval($proofMeta['started_at'] ?? date('c')),
        'last_edited_at' => strval($proofMeta['last_edited_at'] ?? date('c')),
        'incorrect_steps' => intval($proofMeta['incorrect_steps'] ?? max(0, count($steps) - $verifiedSteps)),
        'hints_used' => intval($proofMeta['hints_used'] ?? 0),
        'verification_attempts' => intval($proofMeta['verification_attempts'] ?? 0),
        'avg_verification_attempts_per_step' => floatval($proofMeta['avg_verification_attempts_per_step'] ?? 0),
        'completion_seconds' => intval($proofMeta['completion_seconds'] ?? 0),
        'is_completed' => true,
        'is_verified' => true,
        'verification_status' => 'success',
        'verification_summary' => mb_substr($verificationSummary, 0, 2000),
        'completed_at' => date('c')
    ];

    $ownedDraftSubmissionId = findOwnedSubmission($db, $userId, $draftSubmissionId);
    if ($ownedDraftSubmissionId) {
        $updateSubmission = $db->prepare("UPDATE submissions SET input_text = ? WHERE id = ? AND user_id = ?");
        $updateSubmission->execute([json_encode($storedPayload, JSON_UNESCAPED_UNICODE), $ownedDraftSubmissionId, $userId]);
        $submissionId = $ownedDraftSubmissionId;
    } else {
        $insertSubmission = $db->prepare("INSERT INTO submissions (user_id, input_text) VALUES (?, ?)");
        $insertSubmission->execute([$userId, json_encode($storedPayload, JSON_UNESCAPED_UNICODE)]);
        $submissionId = intval($db->lastInsertId());
    }

    $scoreSaved = false;
    $scoreColsPost = getTableColumns($db, 'scores');
    $scoreSubmissionColPost = pickColumn($scoreColsPost, ['submission_id', 'proof_id'], null);
    $scoreValueColPost = pickColumn($scoreColsPost, ['score', 'points', 'value', 'grade'], null);

    if ($scoreSubmissionColPost && $scoreValueColPost) {
        $deleteScore = $db->prepare("DELETE FROM scores WHERE `{$scoreSubmissionColPost}` = ?");
        $deleteScore->execute([$submissionId]);
        $insertScore = $db->prepare("INSERT INTO scores (`{$scoreSubmissionColPost}`, `{$scoreValueColPost}`) VALUES (?, ?)");
        $insertScore->execute([$submissionId, $score]);
        $scoreSaved = true;
    }

    echo json_encode([
        'success' => true,
        'submission_id' => $submissionId,
        'score' => $score,
        'score_saved' => $scoreSaved,
        'is_completed' => true,
        'is_verified' => true,
        'verification_status' => 'success'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Submissions API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save proof. ' . $e->getMessage() . '. Please try again or contact support if the issue persists.'
    ]);
}
?>