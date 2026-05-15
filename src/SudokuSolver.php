<?php

declare(strict_types=1);

namespace Sudoku;

final class SudokuSolver
{
    public function solve(SudokuBoard $board): bool
    {
        $grid = $board->getGrid();
        $isSolved = $this->solveGrid($grid);

        if ($isSolved) {
            for ($row = 0; $row < 9; $row++) {
                for ($col = 0; $col < 9; $col++) {
                    $board->setValue($row, $col, $grid[$row][$col]);
                }
            }
        }

        return $isSolved;
    }

    /** @param int[][] $grid */
    private function solveGrid(array &$grid): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($grid[$row][$col] === 0) {
                    for ($candidate = 1; $candidate <= 9; $candidate++) {
                        if ($this->isValidMove($grid, $row, $col, $candidate)) {
                            $grid[$row][$col] = $candidate;

                            if ($this->solveGrid($grid)) {
                                return true;
                            }

                            $grid[$row][$col] = 0;
                        }
                    }

                    return false;
                }
            }
        }

        return true;
    }

    /** @param int[][] $grid */
    private function isValidMove(array $grid, int $row, int $col, int $value): bool
    {
        for ($i = 0; $i < 9; $i++) {
            if ($grid[$row][$i] === $value || $grid[$i][$col] === $value) {
                return false;
            }
        }

        $startRow = intdiv($row, 3) * 3;
        $startCol = intdiv($col, 3) * 3;

        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($grid[$r][$c] === $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
