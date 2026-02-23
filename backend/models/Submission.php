<?php

class Submission {
    private $id;
    private $userId;
    private $input;
    private $leanScore;
    private $createdAt;

    public function __construct($userId, $input) {
        $this->userId = $userId;
        $this->input = $input;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getInput() {
        return $this->input;
    }

    public function getLeanScore() {
        return $this->leanScore;
    }

    public function setLeanScore($leanScore) {
        $this->leanScore = $leanScore;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function saveToDatabase($dbConnection) {
        $stmt = $dbConnection->prepare("INSERT INTO submissions (user_id, input, lean_score, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $this->userId, $this->input, $this->leanScore, $this->createdAt);
        $stmt->execute();
        $this->id = $dbConnection->insert_id;
        $stmt->close();
    }
}