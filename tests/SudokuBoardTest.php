<?php

declare(strict_types=1);

namespace Sudoku\Tests;

use PHPUnit\Framework\TestCase;
use Sudoku\SudokuBoard;

final class SudokuBoardTest extends TestCase
{
    public function testMoveValidation(): void
    {
        $grid = [
            [5, 3, 0, 0, 7, 0, 0, 0, 0],
            [6, 0, 0, 1, 9, 5, 0, 0, 0],
            [0, 9, 8, 0, 0, 0, 0, 6, 0],
            [8, 0, 0, 0, 6, 0, 0, 0, 3],
            [4, 0, 0, 8, 0, 3, 0, 0, 1],
            [7, 0, 0, 0, 2, 0, 0, 0, 6],
            [0, 6, 0, 0, 0, 0, 2, 8, 0],
            [0, 0, 0, 4, 1, 9, 0, 0, 5],
            [0, 0, 0, 0, 8, 0, 0, 7, 9],
        ];

        $board = new SudokuBoard($grid);
        self::assertFalse($board->isMoveValid(0, 2, 5));
        self::assertTrue($board->isMoveValid(0, 2, 4));
    }
}
