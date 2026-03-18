<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

session_start();

function toBool($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
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
            'live_score' => 0,
            'started_at' => null,
            'last_edited_at' => null,
            'completed_at' => null,
            'incorrect_steps' => 0,
            'hints_used' => 0,
            'verification_attempts' => 0,
            'avg_verification_attempts_per_step' => 0,
            'completion_seconds' => 0,
            'steps' => []
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
        'live_score' => intval($decoded['live_score'] ?? 0),
        'started_at' => $decoded['started_at'] ?? ($decoded['proof_meta']['started_at'] ?? null),
        'last_edited_at' => $decoded['last_edited_at'] ?? ($decoded['proof_meta']['last_edited_at'] ?? null),
        'completed_at' => $decoded['completed_at'] ?? ($decoded['proof_meta']['completed_at'] ?? null),
        'incorrect_steps' => intval($decoded['incorrect_steps'] ?? ($decoded['proof_meta']['incorrect_steps'] ?? 0)),
        'hints_used' => intval($decoded['hints_used'] ?? ($decoded['proof_meta']['hints_used'] ?? 0)),
        'verification_attempts' => intval($decoded['verification_attempts'] ?? ($decoded['proof_meta']['verification_attempts'] ?? 0)),
        'avg_verification_attempts_per_step' => floatval($decoded['avg_verification_attempts_per_step'] ?? ($decoded['proof_meta']['avg_verification_attempts_per_step'] ?? 0)),
        'completion_seconds' => intval($decoded['completion_seconds'] ?? ($decoded['proof_meta']['completion_seconds'] ?? 0)),
        'steps' => $steps
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
        error_log('Analytics API: Database connection failed');
        echo json_encode(['success' => false, 'error' => 'Database is unavailable. Please try again later or contact support.']);
        exit();
    }

    $queryUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $sessionUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    $userId = resolveUserId($db, $queryUserId ?: $sessionUserId);

    $scoreCols = getTableColumns($db, 'scores');
    $submissionCols = getTableColumns($db, 'submissions');

    $scoreValueCol = pickColumn($scoreCols, ['score', 'points', 'value', 'grade'], null);
    $scoreSubmissionCol = pickColumn($scoreCols, ['submission_id', 'proof_id'], null);
    $submissionCreatedCol = pickColumn($submissionCols, ['created_at', 'submitted_at', 'updated_at', 'timestamp'], null);

    $scoreExpr = $scoreValueCol ? "MAX(sc.`{$scoreValueCol}`)" : "NULL";
    $createdExpr = $submissionCreatedCol ? "s.`{$submissionCreatedCol}`" : "NULL";

    $scoreJoin = '';
    if ($scoreSubmissionCol) {
        $scoreJoin = "LEFT JOIN scores sc ON sc.`{$scoreSubmissionCol}` = s.id";
    }

    $query = "SELECT s.id, s.input_text, {$createdExpr} AS created_at, {$scoreExpr} AS score
              FROM submissions s
              {$scoreJoin}
              WHERE s.user_id = ?
              GROUP BY s.id, s.input_text, {$createdExpr}
              ORDER BY {$createdExpr} DESC, s.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $submissions = [];
    foreach ($rows as $row) {
        $parsed = parseStoredInput($row['input_text'] ?? '');
        $resolvedScore = isset($row['score']) ? intval($row['score']) : intval($parsed['live_score'] ?? 0);
        if ($resolvedScore <= 0 && intval($parsed['total_steps'] ?? 0) > 0) {
            $resolvedScore = intval(round((intval($parsed['verified_steps'] ?? 0) / max(1, intval($parsed['total_steps'] ?? 0))) * 100));
        }
        $submissions[] = [
            'id' => intval($row['id'] ?? 0),
            'score' => $resolvedScore,
            'theorem_text' => $parsed['theorem_text'],
            'proof_text' => $parsed['proof_text'],
            'is_completed' => $parsed['is_completed'],
            'is_verified' => $parsed['is_verified'] || strtolower($parsed['verification_status']) === 'success',
            'total_steps' => intval($parsed['total_steps'] ?? 0),
            'verified_steps' => intval($parsed['verified_steps'] ?? 0),
            'started_at' => $parsed['started_at'] ?? null,
            'last_edited_at' => $parsed['last_edited_at'] ?? null,
            'completed_at' => $parsed['completed_at'] ?? null,
            'incorrect_steps' => intval($parsed['incorrect_steps'] ?? 0),
            'hints_used' => intval($parsed['hints_used'] ?? 0),
            'verification_attempts' => intval($parsed['verification_attempts'] ?? 0),
            'avg_verification_attempts_per_step' => floatval($parsed['avg_verification_attempts_per_step'] ?? 0),
            'completion_seconds' => intval($parsed['completion_seconds'] ?? 0),
            'steps' => $parsed['steps'] ?? [],
            'created_at' => $row['created_at'] ?? null
        ];
    }

    $totalProofs = count($submissions);
    $verifiedCount = count(array_filter($submissions, function ($item) {
        return !empty($item['is_verified']);
    }));
    $pendingCount = max(0, $totalProofs - $verifiedCount);

    $scores = array_values(array_filter(array_map(function ($item) {
        return intval($item['score'] ?? 0);
    }, $submissions), function ($value) {
        return intval($value) > 0;
    }));

    $avgScore = count($scores) > 0 ? intval(round(array_sum($scores) / count($scores))) : 0;
    $highestScore = count($scores) > 0 ? max($scores) : 0;
    $totalScored = count($scores);
    $successRate = $totalScored > 0
        ? intval(round((count(array_filter($scores, function ($value) { return intval($value) >= 70; })) / $totalScored) * 100))
        : 0;
    $proofsCompleted = count(array_filter($submissions, function ($item) {
        return !empty($item['is_completed']);
    }));
    $totalStepsWritten = array_sum(array_map(function ($item) {
        return intval($item['total_steps'] ?? 0);
    }, $submissions));

    $proofsAttempted = $totalProofs;
    $incorrectSteps = array_sum(array_map(function ($item) {
        $storedIncorrect = intval($item['incorrect_steps'] ?? 0);
        if ($storedIncorrect > 0) return $storedIncorrect;
        $total = intval($item['total_steps'] ?? 0);
        $verified = intval($item['verified_steps'] ?? 0);
        return max(0, $total - $verified);
    }, $submissions));
    $hintsUsed = array_sum(array_map(function ($item) {
        return intval($item['hints_used'] ?? 0);
    }, $submissions));
    $verificationAttempts = array_sum(array_map(function ($item) {
        return intval($item['verification_attempts'] ?? 0);
    }, $submissions));
    $avgVerificationAttemptsPerStep = $totalStepsWritten > 0
        ? round($verificationAttempts / $totalStepsWritten, 2)
        : 0;

    $completedWithTime = array_values(array_filter($submissions, function ($item) {
        return !empty($item['is_completed']) && intval($item['completion_seconds'] ?? 0) > 0;
    }));
    $avgCompletionSeconds = count($completedWithTime) > 0
        ? intval(round(array_sum(array_map(function ($item) { return intval($item['completion_seconds'] ?? 0); }, $completedWithTime)) / count($completedWithTime)))
        : 0;

    $accuracyRate = $avgScore;
    $avgStepsPerProof = $proofsAttempted > 0 ? round($totalStepsWritten / $proofsAttempted, 2) : 0;

    $recentActivity = array_map(function ($item, $index) {
        return [
            'id' => intval($item['id'] ?? 0),
            'score' => intval($item['score'] ?? 0),
            'created_at' => $item['created_at'] ?? null,
            'label' => !empty($item['theorem_text'])
                ? mb_substr(trim($item['theorem_text']), 0, 42)
                : ('Entry ' . ($index + 1))
        ];
    }, array_slice($submissions, 0, 7), array_keys(array_slice($submissions, 0, 7)));

    $orderedByDate = $submissions;
    usort($orderedByDate, function ($a, $b) {
        $da = strtotime(strval($a['completed_at'] ?: $a['created_at'] ?: '')) ?: 0;
        $db = strtotime(strval($b['completed_at'] ?: $b['created_at'] ?: '')) ?: 0;
        return $da <=> $db;
    });

    $progressOverTime = [];
    $accuracyOverTime = [];
    $hintUsageTrend = [];
    $runningCompleted = 0;

    foreach ($orderedByDate as $item) {
        $labelDate = $item['completed_at'] ?: $item['created_at'] ?: null;
        $label = $labelDate ? date('M d', strtotime($labelDate)) : ('Entry ' . strval($item['id']));

        if (!empty($item['is_completed'])) {
            $runningCompleted++;
        }

        $progressOverTime[] = [
            'label' => $label,
            'value' => $runningCompleted,
            'submission_id' => intval($item['id'] ?? 0)
        ];

        $accuracyOverTime[] = [
            'label' => $label,
            'value' => intval($item['score'] ?? 0),
            'submission_id' => intval($item['id'] ?? 0)
        ];

        $hintUsageTrend[] = [
            'label' => $label,
            'value' => intval($item['hints_used'] ?? 0),
            'submission_id' => intval($item['id'] ?? 0)
        ];
    }

    $proofHistory = array_map(function ($item) {
        return [
            'submission_id' => intval($item['id'] ?? 0),
            'theorem_name' => trim(strval($item['theorem_text'] ?? '')) ?: ('Proof #' . intval($item['id'] ?? 0)),
            'date_completed' => $item['completed_at'] ?? $item['created_at'] ?? null,
            'steps' => intval($item['total_steps'] ?? 0),
            'accuracy' => intval($item['score'] ?? 0),
            'completion_seconds' => intval($item['completion_seconds'] ?? 0),
            'status' => !empty($item['is_completed']) ? 'Completed' : 'In Progress'
        ];
    }, $submissions);

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_proofs' => $totalProofs,
            'proofs_attempted' => $proofsAttempted,
            'proof_accuracy_rate' => $accuracyRate,
            'verified_count' => $verifiedCount,
            'pending_count' => $pendingCount,
            'avg_score' => $avgScore,
            'highest_score' => $highestScore,
            'total_scored' => $totalScored,
            'success_rate' => $successRate,
            'proofs_completed' => $proofsCompleted,
            'total_steps_written' => $totalStepsWritten,
            'incorrect_steps' => $incorrectSteps,
            'hints_used' => $hintsUsed,
            'verification_attempts' => $verificationAttempts,
            'avg_verification_attempts_per_step' => $avgVerificationAttemptsPerStep,
            'avg_completion_seconds' => $avgCompletionSeconds,
            'avg_steps_per_proof' => $avgStepsPerProof
        ],
        'recent_activity' => $recentActivity,
        'charts' => [
            'completion_progress_over_time' => $progressOverTime,
            'accuracy_improvement_over_time' => $accuracyOverTime,
            'hint_usage_trend' => $hintUsageTrend
        ],
        'proof_history' => $proofHistory
    ]);
} catch (Exception $error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $error->getMessage()
    ]);
}
?>
