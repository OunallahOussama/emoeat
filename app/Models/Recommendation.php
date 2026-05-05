<?php
namespace App\Models;

use App\Core\Model;

class Recommendation extends Model
{
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }

    public function getRecentByUser(int $userId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.FOOD_NAME, e.EMOTION_NAME, r.RECOMMENDATION_DATE
             FROM RECOMMENDATIONS r
             JOIN FOODS f ON f.ID_FOOD = r.ID_FOOD
             JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
             WHERE r.ID_USER = :u
             ORDER BY r.RECOMMENDATION_DATE DESC"
        );
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        $count = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($count >= $limit) break;
            $results[] = $row;
            $count++;
        }
        return $results;
    }

    public function getHistoryByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.FOOD_NAME, f.CALORIES, f.CATEGORY,
                    r.BENEFIT, r.RECOMMENDATION_DATE, e.EMOTION_NAME
             FROM RECOMMENDATIONS r
             JOIN FOODS f ON f.ID_FOOD = r.ID_FOOD
             JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
             WHERE r.ID_USER = :u
             ORDER BY r.RECOMMENDATION_DATE DESC"
        );
        $stmt->bindParam(':u', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function save(int $emotionId, int $foodId, string $benefit, int $userId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO RECOMMENDATIONS (id_emotion, id_food, benefit, id_user, recommendation_date)
             VALUES (:e, :f, :b, :u, NOW())"
        );
        $stmt->execute([':e' => $emotionId, ':f' => $foodId, ':b' => $benefit, ':u' => $userId]);
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM RECOMMENDATIONS");
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }
}
