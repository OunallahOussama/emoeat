<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT ID_USER, NAME, EMAIL, PASSWORD, ROLE, CREATED_AT FROM USERS WHERE EMAIL = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS WHERE ID_USER = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$row['CNT'] > 0;
    }

    public function create(string $name, string $email, string $hashedPassword): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO USERS (name, email, password, role, created_at) VALUES (:name, :email, :password, 'CLIENT', NOW())"
        );
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        $newId = (int)$this->db->lastInsertId();

        $stmtC = $this->db->prepare("INSERT INTO CLIENT (id_user) VALUES (:id_user)");
        $stmtC->bindParam(':id_user', $newId, \PDO::PARAM_INT);
        $stmtC->execute();

        return $newId;
    }

    public function updatePassword(int $userId, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("UPDATE USERS SET PASSWORD = :pwd WHERE ID_USER = :u");
        $stmt->execute([':pwd' => $hashedPassword, ':u' => $userId]);
    }

    public function updateRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare("UPDATE USERS SET ROLE = :r WHERE ID_USER = :id");
        $stmt->bindParam(':r', $role, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($role === 'ADMIN') {
            $chk = $this->db->prepare("SELECT COUNT(*) AS C FROM ADMIN WHERE ID_USER = :id");
            $chk->bindParam(':id', $userId, \PDO::PARAM_INT);
            $chk->execute();
            $row = $chk->fetch(\PDO::FETCH_ASSOC);
            if ((int)$row['C'] === 0) {
                $ins = $this->db->prepare("INSERT INTO ADMIN (ID_USER) VALUES (:id)");
                $ins->bindParam(':id', $userId, \PDO::PARAM_INT);
                $ins->execute();
            }
        } else {
            $del = $this->db->prepare("DELETE FROM ADMIN WHERE ID_USER = :id");
            $del->bindParam(':id', $userId, \PDO::PARAM_INT);
            $del->execute();
        }
    }

    public function delete(int $userId): void
    {
        $tables = [
            "DELETE FROM RECOMMENDATIONS WHERE ID_USER = :id",
            "DELETE FROM USER_EMOTIONS WHERE ID_USER = :id",
            "DELETE FROM USER_PROFILE WHERE ID_USER = :id",
            "DELETE FROM ACTIVITY_LOG WHERE ID_USER = :id",
            "DELETE FROM CLIENT WHERE ID_USER = :id",
            "DELETE FROM ADMIN WHERE ID_USER = :id",
            "DELETE FROM USERS WHERE ID_USER = :id",
        ];
        foreach ($tables as $sql) {
            $st = $this->db->prepare($sql);
            $st->bindParam(':id', $userId, \PDO::PARAM_INT);
            $st->execute();
        }
    }

    public function search(string $query = ''): array
    {
        if ($query !== '') {
            $stmt = $this->db->prepare(
                "SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS
                 WHERE LOWER(NAME) LIKE :q OR LOWER(EMAIL) LIKE :q ORDER BY CREATED_AT DESC"
            );
            $like = '%' . strtolower($query) . '%';
            $stmt->bindParam(':q', $like);
        } else {
            $stmt = $this->db->prepare("SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC");
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentUsers(int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC");
        $stmt->execute();
        $users = [];
        $count = 0;
        while ($u = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($count >= $limit) break;
            $users[] = $u;
            $count++;
        }
        return $users;
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS C FROM USERS");
        $stmt->execute();
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$r['C'];
    }
}
