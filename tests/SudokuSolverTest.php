<?php

declare(strict_types=1);

namespace Sudoku\Tests;

use PHPUnit\Framework\TestCase;
use Sudoku\SudokuBoard;
use Sudoku\SudokuSolver;

final class SudokuSolverTest extends TestCase
{
    public function testSolverCompletesPuzzle(): void
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
        $solver = new SudokuSolver();

        self::assertTrue($solver->solve($board));
        self::assertTrue($board->isComplete());
    }
}
