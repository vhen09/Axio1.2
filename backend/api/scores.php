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
            'live_score' => 0
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
        'live_score' => intval($decoded['live_score'] ?? 0)
    ];
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
        echo json_encode([
            'success' => true,
            'scores' => [],
            'summary' => [
                'avg_score' => 0,
                'highest_score' => 0,
                'total_scored' => 0,
                'success_rate' => 0,
                'proofs_completed' => 0,
                'total_steps_written' => 0
            ]
        ]);
        exit();
    }

    $queryUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $sessionUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    $userId = $queryUserId ?: $sessionUserId;

    $scoreCols = getTableColumns($db, 'scores');
    $submissionCols = getTableColumns($db, 'submissions');

    $scoreIdCol = pickColumn($scoreCols, ['id'], 'id');
    $scoreValueCol = pickColumn($scoreCols, ['score', 'points', 'value', 'grade'], null);
    $scoreCreatedCol = pickColumn($scoreCols, ['created_at', 'updated_at', 'timestamp'], null);
    $scoreSubmissionCol = pickColumn($scoreCols, ['submission_id', 'proof_id'], null);
    $submissionCreatedCol = pickColumn($submissionCols, ['created_at', 'submitted_at', 'updated_at', 'timestamp'], null);

    if (!in_array('id', $submissionCols, true)) {
        echo json_encode([
            'success' => true,
            'scores' => [],
            'summary' => [
                'avg_score' => 0,
                'highest_score' => 0,
                'total_scored' => 0,
                'success_rate' => 0,
                'proofs_completed' => 0,
                'total_steps_written' => 0
            ]
        ]);
        exit();
    }

    $scoreExpr = $scoreValueCol ? "MAX(sc.`{$scoreValueCol}`)" : "NULL";
    $scoreCreatedExpr = $scoreCreatedCol ? "MAX(sc.`{$scoreCreatedCol}`)" : "NULL";
    $submissionCreatedExpr = $submissionCreatedCol ? "s.`{$submissionCreatedCol}`" : "NULL";
    $orderExpr = $submissionCreatedCol ? "s.`{$submissionCreatedCol}`" : "s.id";

    $scoreJoin = '';
    if ($scoreSubmissionCol) {
        $scoreJoin = "LEFT JOIN scores sc ON sc.`{$scoreSubmissionCol}` = s.id";
    }

    $query = "SELECT
                s.id AS submission_id,
                {$scoreExpr} AS score_value,
                {$scoreCreatedExpr} AS score_created_at,
                s.user_id,
                s.input_text,
                {$submissionCreatedExpr} AS submission_created_at
            FROM submissions s
            {$scoreJoin}";

    $params = [];
    if ($userId) {
        $query .= " WHERE s.user_id = ?";
        $params[] = $userId;
    }

    $query .= " GROUP BY s.id, s.user_id, s.input_text, {$submissionCreatedExpr}
                ORDER BY {$orderExpr} DESC, s.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $scores = [];
    foreach ($rows as $row) {
        $parsed = parseStoredInput($row['input_text'] ?? '');
        $resolvedScore = isset($row['score_value']) && $row['score_value'] !== null
            ? intval($row['score_value'])
            : intval($parsed['live_score'] ?? 0);
        if ($resolvedScore <= 0 && intval($parsed['total_steps'] ?? 0) > 0) {
            $resolvedScore = intval(round((intval($parsed['verified_steps'] ?? 0) / max(1, intval($parsed['total_steps'] ?? 0))) * 100));
        }

        $scores[] = [
            'id' => intval($row['submission_id'] ?? 0),
            'submission_id' => intval($row['submission_id'] ?? 0),
            'user_id' => intval($row['user_id'] ?? 0),
            'score' => $resolvedScore,
            'proof_text' => $parsed['proof_text'],
            'theorem_text' => $parsed['theorem_text'],
            'is_completed' => $parsed['is_completed'],
            'is_verified' => $parsed['is_verified'],
            'verification_status' => $parsed['verification_status'],
            'total_steps' => $parsed['total_steps'],
            'verified_steps' => $parsed['verified_steps'],
            'created_at' => $row['score_created_at'] ?? $row['submission_created_at'] ?? null,
            'submitted_at' => $row['submission_created_at'] ?? null
        ];
    }

    $scoreValues = array_map(function ($item) {
        return intval($item['score'] ?? 0);
    }, $scores);

    $totalScored = count(array_filter($scoreValues, function ($value) { return intval($value) > 0; }));
    $avgScore = $totalScored > 0 ? intval(round(array_sum($scoreValues) / $totalScored)) : 0;
    $highestScore = $totalScored > 0 ? max($scoreValues) : 0;
    $successRate = $totalScored > 0
        ? intval(round((count(array_filter($scoreValues, function ($value) { return $value >= 70; })) / $totalScored) * 100))
        : 0;
    $proofsCompleted = count(array_filter($scores, function ($item) {
        return !empty($item['is_completed']);
    }));
    $totalStepsWritten = array_sum(array_map(function ($item) {
        return intval($item['total_steps'] ?? 0);
    }, $scores));

    echo json_encode([
        'success' => true,
        'scores' => $scores,
        'summary' => [
            'avg_score' => $avgScore,
            'highest_score' => $highestScore,
            'total_scored' => $totalScored,
            'success_rate' => $successRate,
            'proofs_completed' => $proofsCompleted,
            'total_steps_written' => $totalStepsWritten
        ]
    ]);
} catch (Exception $error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $error->getMessage(),
        'scores' => []
    ]);
}
?>