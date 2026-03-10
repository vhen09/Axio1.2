<?php

class User {
    private $id;
    private $username;
    private $password;
    private $email;

    public function __construct($username, $password, $email) {
        $this->username = $username;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->email = $email;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function validatePassword($password) {
        return password_verify($password, $this->password);
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
}