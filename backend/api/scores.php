<?php
require_once '../config/database.php';
require_once '../models/Score.php';

class ScoreAPI {
    private $db;
    private $scoreModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->scoreModel = new Score($this->db);
    }

    public function getScores($userId) {
        $scores = $this->scoreModel->getScoresByUserId($userId);
        echo json_encode($scores);
    }
}

header('Content-Type: application/json');

$scoreAPI = new ScoreAPI();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if ($userId > 0) {
        $scoreAPI->getScores($userId);
    } else {
        echo json_encode(['error' => 'Invalid user ID']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>