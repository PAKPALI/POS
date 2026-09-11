<?php

namespace Tests\Unit;

use App\Services\PartnerPromotionService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PartnerPromotionServiceTest extends TestCase
{
    #[DataProvider('commissionRateBoundaries')]
    public function test_commission_grid_matches_the_architecture_at_every_boundary(int $rank, int $expectedRateBps): void
    {
        $this->assertSame($expectedRateBps, PartnerPromotionService::rateForRank($rank));
    }

    /** @return array<string, array{int, int}> */
    public static function commissionRateBoundaries(): array
    {
        return [
            'first client' => [1, 1000],
            'end of 10 percent block' => [5, 1000],
            'first 11 percent client' => [6, 1100],
            'end of 14 percent block' => [25, 1400],
            'first 15 percent client' => [26, 1500],
            'end of 15 percent block' => [35, 1500],
            'first 16 percent client' => [36, 1600],
            'end of 19 percent block' => [75, 1900],
            'first 20 percent client' => [76, 2000],
            'end of 20 percent block' => [95, 2000],
            'first 21 percent client' => [96, 2100],
            'end of 24 percent block' => [175, 2400],
            'maximum rate' => [176, 2500],
            'maximum rate remains capped' => [1000, 2500],
        ];
    }
}
