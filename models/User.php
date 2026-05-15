<?php
require_once __DIR__ . '/../config/db.php';

class User {

    private $db;

    public function __construct() {
        $this->db = DB::connect();
    }

    // REGISTER
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password_hash, delivery_address, role)
                VALUES (?, ?, ?, ?, 'customer')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // FIND BY EMAIL
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // FIND BY ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // UPDATE PROFILE
    public function updateProfile($id, $name, $email, $address) {
        $sql = "UPDATE users SET name=?, email=?, delivery_address=? WHERE id=?";
        return $this->db->prepare($sql)->execute([$name, $email, $address, $id]);
    }

    // UPDATE PASSWORD
    public function updatePassword($id, $hash) {
        return $this->db
            ->prepare("UPDATE users SET password_hash=? WHERE id=?")
            ->execute([$hash, $id]);
    }

    // SAVE REMEMBER TOKEN
    public function saveToken($id, $tokenHash) {
        return $this->db
            ->prepare("UPDATE users SET remember_token=? WHERE id=?")
            ->execute([$tokenHash, $id]);
    }

    // FIND TOKEN
    public function findByToken($tokenHash) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE remember_token=?");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch();
    }
}