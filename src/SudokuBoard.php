<?php

declare(strict_types=1);

namespace Sudoku;

final class SudokuBoard
{
    /** @var int[][] */
    private array $grid;

    /** @param int[][] $grid */
    public function __construct(array $grid)
    {
        $this->assertGridShape($grid);
        $this->grid = $grid;
    }

    /** @return int[][] */
    public function getGrid(): array
    {
        return $this->grid;
    }

    public function setValue(int $row, int $col, int $value): void
    {
        if ($value < 0 || $value > 9) {
            throw new \InvalidArgumentException('Cell value must be between 0 and 9.');
        }

        $this->grid[$row][$col] = $value;
    }

    public function isMoveValid(int $row, int $col, int $value): bool
    {
        if ($value < 1 || $value > 9) {
            return false;
        }

        for ($i = 0; $i < 9; $i++) {
            if ($i !== $col && $this->grid[$row][$i] === $value) {
                return false;
            }
            if ($i !== $row && $this->grid[$i][$col] === $value) {
                return false;
            }
        }

        $startRow = intdiv($row, 3) * 3;
        $startCol = intdiv($col, 3) * 3;

        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if (($r !== $row || $c !== $col) && $this->grid[$r][$c] === $value) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isComplete(): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $value = $this->grid[$row][$col];
                if ($value === 0 || !$this->isMoveValid($row, $col, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @param int[][] $grid */
    private function assertGridShape(array $grid): void
    {
        if (count($grid) !== 9) {
            throw new \InvalidArgumentException('Grid must have exactly 9 rows.');
        }

        foreach ($grid as $row) {
            if (!is_array($row) || count($row) !== 9) {
                throw new \InvalidArgumentException('Each row must have exactly 9 columns.');
            }

            foreach ($row as $cell) {
                if (!is_int($cell) || $cell < 0 || $cell > 9) {
                    throw new \InvalidArgumentException('Each cell must be an integer between 0 and 9.');
                }
            }
        }
    }
}
