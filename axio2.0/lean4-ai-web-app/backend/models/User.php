<?php

class User {
    private $db;
    private $lastError = null;
    private $passwordColumn = 'password_hash';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create($username, $password) {
        $username = trim((string)$username);
        $password = (string)$password;
        $this->lastError = null;

        if ($username === '' || $password === '') {
            $this->lastError = 'Username and password are required.';
            return false;
        }

        if ($this->findByUsername($username)) {
            $this->lastError = 'Username already exists.';
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $email = $this->buildEmailFromUsername($username);
        $stmt = $this->db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');

        try {
            return $stmt->execute([$username, $email, $hash]);
        } catch (Exception $error) {
            $this->lastError = $error->getMessage();
            return false;
        }
    }

    public function findByUsername($username) {
        $username = trim((string)$username);
        if ($username === '') {
            return null;
        }

        try {
            $stmt = $this->db->prepare('SELECT id, username, password_hash AS password FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $error) {
            $this->lastError = $error->getMessage();
            return null;
        }
    }

    private function buildEmailFromUsername($username) {
        $clean = preg_replace('/[^a-zA-Z0-9._-]+/', '', strtolower($username));
        if ($clean === '') {
            $clean = 'user' . time();
        }
        return $clean . '@axio.local';
    }

    public function getLastError() {
        return $this->lastError;
    }
}