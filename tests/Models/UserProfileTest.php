<?php
namespace Tests\Models;

use App\Models\UserProfile;
use Tests\BaseTestCase;

class UserProfileTest extends BaseTestCase
{
    private UserProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profile = new UserProfile($this->pdo);
    }

    public function testFindByUserReturnsProfile(): void
    {
        $expected = ['ID_USER' => 1, 'WEIGHT' => 70.5, 'HEIGHT' => 175.0, 'ALLERGIES' => 'None', 'GOAL' => 'Maintain'];
        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->profile->findByUser(1);
        $this->assertEquals($expected, $result);
    }

    public function testFindByUserReturnsNull(): void
    {
        $stmt = $this->mockStatement([]);
        $this->expectPrepare($stmt);

        $result = $this->profile->findByUser(999);
        $this->assertNull($result);
    }

    public function testHasProfileTrue(): void
    {
        $stmt = $this->mockStatement([['C' => 1]]);
        $this->expectPrepare($stmt);

        $this->assertTrue($this->profile->hasProfile(1));
    }

    public function testHasProfileFalse(): void
    {
        $stmt = $this->mockStatement([['C' => 0]]);
        $this->expectPrepare($stmt);

        $this->assertFalse($this->profile->hasProfile(99));
    }

    public function testSaveInsertsNewProfile(): void
    {
        // First call for findByUser (returns null), second for INSERT
        $findStmt = $this->createMock(\PDOStatement::class);
        $findStmt->method('execute')->willReturn(true);
        $findStmt->method('bindParam')->willReturn(true);
        $findStmt->method('fetch')->willReturn(false);

        $insertStmt = $this->createMock(\PDOStatement::class);
        $insertStmt->method('bindParam')->willReturn(true);
        $insertStmt->expects($this->once())->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($findStmt, $insertStmt);

        $this->profile->save(1, 70.5, 175.0, 'None', 'Lose weight');
    }

    public function testSaveUpdatesExistingProfile(): void
    {
        $existingProfile = ['ID_USER' => 1, 'WEIGHT' => 68.0, 'HEIGHT' => 170.0, 'ALLERGIES' => '', 'GOAL' => 'Gain'];

        $findStmt = $this->createMock(\PDOStatement::class);
        $findStmt->method('execute')->willReturn(true);
        $findStmt->method('bindParam')->willReturn(true);
        $findStmt->method('fetch')->willReturn($existingProfile);

        $updateStmt = $this->createMock(\PDOStatement::class);
        $updateStmt->method('bindParam')->willReturn(true);
        $updateStmt->expects($this->once())->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($findStmt, $updateStmt);

        $this->profile->save(1, 72.0, 175.0, 'Nuts', 'Maintain');
    }
}
