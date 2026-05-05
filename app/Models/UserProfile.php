<?php
namespace App\Models;

use App\Core\Model;

class UserProfile extends Model
{
    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function hasProfile(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'] > 0;
    }

    public function save(int $userId, float $weight, float $height, string $allergies, string $goal): void
    {
        $existing = $this->findByUser($userId);

        if ($existing) {
            $sql = "UPDATE USER_PROFILE SET WEIGHT = :weight, HEIGHT = :height,
                    ALLERGIES = :allergies, GOAL = :goal WHERE ID_USER = :u";
        } else {
            $sql = "INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT, ALLERGIES, GOAL)
                    VALUES (:u, :weight, :height, :allergies, :goal)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':height', $height);
        $stmt->bindParam(':allergies', $allergies);
        $stmt->bindParam(':goal', $goal);
        $stmt->execute();
    }
}
