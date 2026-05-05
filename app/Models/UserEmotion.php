<?php
namespace App\Models;

use App\Core\Model;

class UserEmotion extends Model
{
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM USER_EMOTIONS WHERE ID_USER = :u");
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }

    public function getHistoryByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ue.EMOTION_DATE, e.EMOTION_NAME
             FROM USER_EMOTIONS ue
             JOIN EMOTIONS e ON e.ID_EMOTION = ue.ID_EMOTION
             WHERE ue.ID_USER = :u
             ORDER BY ue.EMOTION_DATE DESC"
        );
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function save(int $userId, int $emotionId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO USER_EMOTIONS (id_user, id_emotion, emotion_date) VALUES (:u, :e, NOW())"
        );
        $stmt->execute([':u' => $userId, ':e' => $emotionId]);
    }
}
