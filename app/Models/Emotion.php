<?php
namespace App\Models;

use App\Core\Model;

class Emotion extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM EMOTIONS WHERE ID_EMOTION = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT ID_EMOTION, EMOTION_NAME, DESCRIPTION FROM EMOTIONS ORDER BY EMOTION_NAME");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGrouped(): array
    {
        $stmt = $this->db->prepare("SELECT MIN(ID_EMOTION) AS ID_EMOTION, EMOTION_NAME FROM EMOTIONS GROUP BY EMOTION_NAME ORDER BY EMOTION_NAME");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(string $name, string $description): void
    {
        $stmt = $this->db->prepare("INSERT INTO EMOTIONS (EMOTION_NAME, DESCRIPTION) VALUES (:name, :desc)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':desc', $description);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        foreach ([
            "DELETE FROM EMOTION_FOOD WHERE ID_EMOTION = :id",
            "DELETE FROM USER_EMOTIONS WHERE ID_EMOTION = :id",
            "DELETE FROM RECOMMENDATIONS WHERE ID_EMOTION = :id",
            "DELETE FROM EMOTIONS WHERE ID_EMOTION = :id",
        ] as $sql) {
            $st = $this->db->prepare($sql);
            $st->bindParam(':id', $id, \PDO::PARAM_INT);
            $st->execute();
        }
    }

    public function addRule(int $emotionId, int $foodId, int $intensity): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES (:e, :f, :i)"
        );
        $stmt->bindParam(':e', $emotionId, \PDO::PARAM_INT);
        $stmt->bindParam(':f', $foodId, \PDO::PARAM_INT);
        $stmt->bindParam(':i', $intensity, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getRules(): array
    {
        $stmt = $this->db->prepare(
            "SELECT ef.ID_RULE, ef.INTENSITY, e.EMOTION_NAME, f.FOOD_NAME
             FROM EMOTION_FOOD ef
             JOIN EMOTIONS e ON e.ID_EMOTION = ef.ID_EMOTION
             JOIN FOODS f ON f.ID_FOOD = ef.ID_FOOD
             ORDER BY ef.INTENSITY DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getFoodsForEmotion(int $emotionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.id_food, f.food_name, f.calories, f.category,
                    f.protein, f.carbs, f.fat, f.description AS benefit,
                    ef.intensity AS score
             FROM EMOTION_FOOD ef
             JOIN FOODS f ON f.id_food = ef.id_food
             WHERE ef.id_emotion = :emo
             ORDER BY ef.intensity DESC"
        );
        $stmt->bindParam(':emo', $emotionId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM EMOTIONS");
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }
}
