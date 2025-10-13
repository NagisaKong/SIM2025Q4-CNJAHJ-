<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Repositories\MatchRepository;

final class MatchRepositoryTest extends TestCase
{
    private PDO $pdo;
    private MatchRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE matches (id INTEGER PRIMARY KEY AUTOINCREMENT, csr_id INTEGER, request_id INTEGER, status TEXT, matched_at TEXT, completed_at TEXT)');
        $this->repository = new MatchRepository($this->pdo);
    }

    public function testAggregateByPeriodGroupsDailyTotals(): void
    {
        $this->pdo->prepare('INSERT INTO matches (csr_id, request_id, status, matched_at, completed_at) VALUES (?,?,?,?,?)')->execute([1, 1, 'completed', '2024-01-01 10:00:00', '2024-01-02 10:00:00']);
        $this->pdo->prepare('INSERT INTO matches (csr_id, request_id, status, matched_at, completed_at) VALUES (?,?,?,?,?)')->execute([1, 2, 'in_progress', '2024-01-01 12:00:00', null]);
        $this->pdo->prepare('INSERT INTO matches (csr_id, request_id, status, matched_at, completed_at) VALUES (?,?,?,?,?)')->execute([2, 3, 'completed', '2024-01-03 09:00:00', '2024-01-04 09:00:00']);

        $result = $this->repository->aggregateByPeriod('daily');

        $this->assertCount(2, $result);
        $this->assertSame('2024-01-03', $result[0]['period']);
        $this->assertSame(1, $result[0]['matches_created']);
        $this->assertSame(1, $result[0]['matches_completed']);
        $this->assertSame('2024-01-01', $result[1]['period']);
        $this->assertSame(2, $result[1]['matches_created']);
        $this->assertSame(1, $result[1]['matches_completed']);
    }
}
