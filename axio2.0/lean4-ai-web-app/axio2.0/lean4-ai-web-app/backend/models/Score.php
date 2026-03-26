class Score {
    private $id;
    private $userId;
    private $submissionId;
    private $score;

    public function __construct($userId, $submissionId, $score) {
        $this->userId = $userId;
        $this->submissionId = $submissionId;
        $this->score = $score;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getSubmissionId() {
        return $this->submissionId;
    }

    public function getScore() {
        return $this->score;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setScore($score) {
        $this->score = $score;
    }
}