<?php
namespace App\Models;

use App\Core\Model;

class ActivityLog extends Model
{
    public function log(int $userId, string $action): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES (:u, :a, NOW())"
            );
            $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':a', $action, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            // Silent catch to not block the application flow
        }
    }

    public function search(string $query = ''): array
    {
        if ($query !== '') {
            $stmt = $this->db->prepare(
                "SELECT al.ID_LOG, al.ACTION, al.LOG_DATE, u.NAME, u.EMAIL
                 FROM ACTIVITY_LOG al JOIN USERS u ON u.ID_USER = al.ID_USER
                 WHERE LOWER(u.NAME) LIKE :q1 OR LOWER(u.EMAIL) LIKE :q2 OR LOWER(al.ACTION) LIKE :q3
                 ORDER BY al.LOG_DATE DESC"
            );
            $like = '%' . strtolower($query) . '%';
            $stmt->bindParam(':q1', $like);
            $stmt->bindParam(':q2', $like);
            $stmt->bindParam(':q3', $like);
        } else {
            $stmt = $this->db->prepare(
                "SELECT al.ID_LOG, al.ACTION, al.LOG_DATE, u.NAME, u.EMAIL
                 FROM ACTIVITY_LOG al JOIN USERS u ON u.ID_USER = al.ID_USER
                 ORDER BY al.LOG_DATE DESC"
            );
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
