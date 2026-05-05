<?php
namespace App\Models;

use App\Core\Model;

class Food extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM FOODS WHERE ID_FOOD = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $name, string $category, int $calories, float $protein, float $carbs, float $fat, string $description): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO FOODS (FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION)
             VALUES (:name, :cat, :cal, :prot, :carbs, :fat, :desc)"
        );
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':cat', $category);
        $stmt->bindParam(':cal', $calories, \PDO::PARAM_INT);
        $stmt->bindParam(':prot', $protein);
        $stmt->bindParam(':carbs', $carbs);
        $stmt->bindParam(':fat', $fat);
        $stmt->bindParam(':desc', $description);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        foreach ([
            "DELETE FROM EMOTION_FOOD WHERE ID_FOOD = :id",
            "DELETE FROM RECOMMENDATIONS WHERE ID_FOOD = :id",
            "DELETE FROM FOODS WHERE ID_FOOD = :id",
        ] as $sql) {
            $st = $this->db->prepare($sql);
            $st->bindParam(':id', $id, \PDO::PARAM_INT);
            $st->execute();
        }
    }

    public function search(string $query = ''): array
    {
        if ($query !== '') {
            $stmt = $this->db->prepare(
                "SELECT ID_FOOD, FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION
                 FROM FOODS WHERE LOWER(FOOD_NAME) LIKE :q OR LOWER(CATEGORY) LIKE :q ORDER BY FOOD_NAME"
            );
            $like = '%' . strtolower($query) . '%';
            $stmt->bindParam(':q', $like);
        } else {
            $stmt = $this->db->prepare(
                "SELECT ID_FOOD, FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION FROM FOODS ORDER BY FOOD_NAME"
            );
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT ID_FOOD, FOOD_NAME, CATEGORY FROM FOODS ORDER BY FOOD_NAME");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM FOODS");
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }
}
