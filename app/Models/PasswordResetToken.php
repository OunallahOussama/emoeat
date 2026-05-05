<?php
namespace App\Models;

use App\Core\Model;

class PasswordResetToken extends Model
{
    public function create(int $userId, string $token, string $expiresAt): void
    {
        // Invalidate old tokens
        $stDel = $this->db->prepare("UPDATE PASSWORD_RESET_TOKENS SET USED = 1 WHERE ID_USER = :u AND USED = 0");
        $stDel->execute([':u' => $userId]);

        // Create new token
        $stIns = $this->db->prepare("INSERT INTO PASSWORD_RESET_TOKENS (ID_USER, TOKEN, EXPIRES_AT) VALUES (:u, :t, :e)");
        $stIns->execute([':u' => $userId, ':t' => $token, ':e' => $expiresAt]);
    }

    public function validate(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.ID_TOKEN, t.ID_USER, u.EMAIL, u.NAME
             FROM PASSWORD_RESET_TOKENS t
             JOIN USERS u ON u.ID_USER = t.ID_USER
             WHERE t.TOKEN = :token AND t.USED = 0 AND t.EXPIRES_AT > NOW()"
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markUsed(int $tokenId): void
    {
        $stmt = $this->db->prepare("UPDATE PASSWORD_RESET_TOKENS SET USED = 1 WHERE ID_TOKEN = :id");
        $stmt->execute([':id' => $tokenId]);
    }
}
