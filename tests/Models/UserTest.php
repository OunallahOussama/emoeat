<?php
namespace Tests\Models;

use App\Models\User;
use Tests\BaseTestCase;

class UserTest extends BaseTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User($this->pdo);
    }

    public function testFindByEmailReturnsUser(): void
    {
        $expected = [
            'ID_USER' => 1,
            'NAME' => 'John',
            'EMAIL' => 'john@example.com',
            'PASSWORD' => 'hashed',
            'ROLE' => 'CLIENT',
            'CREATED_AT' => '2024-01-01',
        ];

        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->user->findByEmail('john@example.com');
        $this->assertEquals($expected, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        $stmt = $this->mockStatement([]);
        $this->expectPrepare($stmt);

        $result = $this->user->findByEmail('nobody@example.com');
        $this->assertNull($result);
    }

    public function testFindByIdReturnsUser(): void
    {
        $expected = [
            'ID_USER' => 1,
            'NAME' => 'John',
            'EMAIL' => 'john@example.com',
            'ROLE' => 'CLIENT',
            'CREATED_AT' => '2024-01-01',
        ];

        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->user->findById(1);
        $this->assertEquals($expected, $result);
    }

    public function testEmailExistsTrue(): void
    {
        $stmt = $this->mockStatement([['CNT' => 1]]);
        $this->expectPrepare($stmt);

        $this->assertTrue($this->user->emailExists('john@example.com'));
    }

    public function testEmailExistsFalse(): void
    {
        $stmt = $this->mockStatement([['CNT' => 0]]);
        $this->expectPrepare($stmt);

        $this->assertFalse($this->user->emailExists('nobody@example.com'));
    }

    public function testCreateReturnsId(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);
        $this->pdo->method('lastInsertId')->willReturn('5');

        $result = $this->user->create('Jane', 'jane@example.com', 'hashedpw');
        $this->assertEquals(5, $result);
    }

    public function testUpdatePasswordExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->expects($this->once())->method('execute')
            ->with([':pwd' => 'newhash', ':u' => 1])
            ->willReturn(true);

        $this->expectPrepare($stmt);

        $this->user->updatePassword(1, 'newhash');
    }

    public function testDeleteCascadesAllTables(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->exactly(7))->method('execute')->willReturn(true);

        $this->pdo->expects($this->exactly(7))->method('prepare')->willReturn($stmt);

        $this->user->delete(1);
    }
}
