<?php
namespace Tests\Models;

use App\Models\PasswordResetToken;
use Tests\BaseTestCase;

class PasswordResetTokenTest extends BaseTestCase
{
    private PasswordResetToken $tokenModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenModel = new PasswordResetToken($this->pdo);
    }

    public function testCreateInvalidatesOldAndInserts(): void
    {
        $delStmt = $this->createMock(\PDOStatement::class);
        $delStmt->expects($this->once())->method('execute')
            ->with([':u' => 1])
            ->willReturn(true);

        $insStmt = $this->createMock(\PDOStatement::class);
        $insStmt->expects($this->once())->method('execute')
            ->with([':u' => 1, ':t' => 'abc123', ':e' => '2024-12-31 23:59:59'])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($delStmt, $insStmt);

        $this->tokenModel->create(1, 'abc123', '2024-12-31 23:59:59');
    }

    public function testValidateReturnsTokenData(): void
    {
        $expected = ['ID_TOKEN' => 1, 'ID_USER' => 5, 'EMAIL' => 'user@example.com', 'NAME' => 'User'];
        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->tokenModel->validate('valid_token');
        $this->assertEquals($expected, $result);
    }

    public function testValidateReturnsNullForInvalidToken(): void
    {
        $stmt = $this->mockStatement([]);
        $this->expectPrepare($stmt);

        $result = $this->tokenModel->validate('invalid_token');
        $this->assertNull($result);
    }

    public function testMarkUsedExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->expects($this->once())->method('execute')
            ->with([':id' => 7])
            ->willReturn(true);
        $this->expectPrepare($stmt);

        $this->tokenModel->markUsed(7);
    }
}
