<?php

declare(strict_types=1);

use Sudoku\SudokuBoard;
use Sudoku\SudokuSolver;

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
} else {
    require dirname(__DIR__) . '/src/SudokuBoard.php';
    require dirname(__DIR__) . '/src/SudokuSolver.php';
}

/** @return int[][] */
function emptyGrid(): array
{
  $grid = [];
  for ($row = 0; $row < 9; $row++) {
    $grid[$row] = array_fill(0, 9, 0);
  }

  return $grid;
}

/** @return string[][] */
function emptyStates(): array
{
  $states = [];
  for ($row = 0; $row < 9; $row++) {
    $states[$row] = array_fill(0, 9, '');
  }

  return $states;
}

/** @param int[][] $grid */
function isValidForGrid(array $grid, int $row, int $col, int $value): bool
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

/** @param int[] $numbers */
function shuffleNumbers(array $numbers): array
{
  shuffle($numbers);
  return $numbers;
}

/** @param int[][] $grid */
function fillSolvedGrid(array &$grid): bool
{
  for ($row = 0; $row < 9; $row++) {
    for ($col = 0; $col < 9; $col++) {
      if ($grid[$row][$col] !== 0) {
        continue;
      }

      $numbers = shuffleNumbers([1, 2, 3, 4, 5, 6, 7, 8, 9]);
      foreach ($numbers as $candidate) {
        if (isValidForGrid($grid, $row, $col, $candidate)) {
          $grid[$row][$col] = $candidate;
          if (fillSolvedGrid($grid)) {
            return true;
          }
          $grid[$row][$col] = 0;
        }
      }

      return false;
    }
  }

  return true;
}

/** @return int[][] */
function generateRandomSolution(): array
{
  $grid = emptyGrid();
  fillSolvedGrid($grid);
  return $grid;
}

/** @param int[][] $solution
 *  @return int[][]
 */
function buildPuzzleFromSolution(array $solution, string $difficulty): array
{
  $holesByDifficulty = [
    'facil' => 36,
    'medio' => 46,
    'dificil' => 54,
  ];

  $holes = $holesByDifficulty[$difficulty] ?? $holesByDifficulty['medio'];
  $puzzle = $solution;

  $positions = range(0, 80);
  shuffle($positions);

  for ($i = 0; $i < $holes; $i++) {
    $index = $positions[$i];
    $row = intdiv($index, 9);
    $col = $index % 9;
    $puzzle[$row][$col] = 0;
  }

  return $puzzle;
}

/** @param int[][] $grid */
function encodeGrid(array $grid): string
{
  return base64_encode(json_encode($grid, JSON_THROW_ON_ERROR));
}

/** @return int[][] */
function decodeGrid(string $encoded): array
{
  if ($encoded === '') {
    return emptyGrid();
  }

  try {
    /** @var mixed $decoded */
    $decoded = json_decode((string) base64_decode($encoded, true), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($decoded) || count($decoded) !== 9) {
      return emptyGrid();
    }

    $grid = [];
    for ($row = 0; $row < 9; $row++) {
      if (!isset($decoded[$row]) || !is_array($decoded[$row]) || count($decoded[$row]) !== 9) {
        return emptyGrid();
      }

      $grid[$row] = [];
      for ($col = 0; $col < 9; $col++) {
        $value = (int) $decoded[$row][$col];
        $grid[$row][$col] = ($value >= 0 && $value <= 9) ? $value : 0;
      }
    }

    return $grid;
  } catch (Throwable) {
    return emptyGrid();
  }
}

/** @return int[][] */
function readInputGrid(array $basePuzzle): array
{
  $grid = [];
  for ($row = 0; $row < 9; $row++) {
    $grid[$row] = [];
    for ($col = 0; $col < 9; $col++) {
      $typed = (int) ($_POST["cell_{$row}_{$col}"] ?? 0);
      $typed = ($typed >= 1 && $typed <= 9) ? $typed : 0;
      $grid[$row][$col] = $basePuzzle[$row][$col] !== 0 ? $basePuzzle[$row][$col] : $typed;
    }
  }

  return $grid;
}

$difficulty = (string) ($_POST['difficulty'] ?? 'medio');
if (!in_array($difficulty, ['facil', 'medio', 'dificil'], true)) {
  $difficulty = 'medio';
}

$cellStates = emptyStates();
$message = 'Juego cargado. Elige dificultad y valida tus jugadas.';

$basePuzzle = decodeGrid((string) ($_POST['base_puzzle'] ?? ''));
$solution = decodeGrid((string) ($_POST['solution_grid'] ?? ''));

$isEmptyBase = true;
for ($row = 0; $row < 9; $row++) {
  for ($col = 0; $col < 9; $col++) {
    if ($basePuzzle[$row][$col] !== 0) {
      $isEmptyBase = false;
      break 2;
    }
  }
}

if ($isEmptyBase) {
  $solution = generateRandomSolution();
  $basePuzzle = buildPuzzleFromSolution($solution, $difficulty);
}

$grid = $basePuzzle;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string) ($_POST['action'] ?? 'nuevo');

  if ($action === 'nuevo') {
    $solution = generateRandomSolution();
    $basePuzzle = buildPuzzleFromSolution($solution, $difficulty);
    $grid = $basePuzzle;
    $message = 'Se genero un Sudoku aleatorio nuevo.';
  }

  if ($action === 'validar') {
    $grid = readInputGrid($basePuzzle);
    $errors = 0;

    for ($row = 0; $row < 9; $row++) {
      for ($col = 0; $col < 9; $col++) {
        if ($basePuzzle[$row][$col] !== 0) {
          $cellStates[$row][$col] = 'fija';
          continue;
        }

        if ($grid[$row][$col] === 0) {
          $cellStates[$row][$col] = '';
          continue;
        }

        if ($grid[$row][$col] === $solution[$row][$col]) {
          $cellStates[$row][$col] = 'correcta';
        } else {
          $cellStates[$row][$col] = 'incorrecta';
          $errors++;
        }
      }
    }

    if ($errors === 0) {
      $message = 'Excelente. Los numeros ingresados hasta ahora son correctos.';
    } else {
      $message = "Tienes {$errors} celda(s) incorrecta(s).";
    }
  }

  if ($action === 'resolver') {
    $grid = $solution;
    $message = 'Sudoku resuelto.';
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sudoku DevOps Demo</title>
  <style>
    body { font-family: Trebuchet MS, sans-serif; margin: 0; background: linear-gradient(150deg, #eef2ff, #f8fafc); }
    .wrap { max-width: 760px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 10px 24px rgba(15, 23, 42, .12); }
    .topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
    .grid { display: grid; grid-template-columns: repeat(9, 40px); gap: 4px; justify-content: center; }
    input, select { height: 40px; text-align: center; font-size: 16px; border: 1px solid #cbd5e1; border-radius: 6px; }
    input { width: 40px; font-size: 18px; }
    input.btop { border-top: 3px solid #334155; }
    input.bleft { border-left: 3px solid #334155; }
    input.bright { border-right: 3px solid #334155; }
    input.bbottom { border-bottom: 3px solid #334155; }
  .fija { background: #e2e8f0; font-weight: 700; }
  .correcta { background: #dcfce7; border-color: #16a34a; }
  .incorrecta { background: #fee2e2; border-color: #dc2626; }
    .actions { margin-top: 14px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    button { padding: 10px 14px; border: 0; background: #0284c7; color: #fff; border-radius: 8px; font-weight: 700; cursor: pointer; }
    button.secondary { background: #475569; }
    button.ghost { background: #0f766e; }
    .msg { margin-top: 10px; font-weight: 700; color: #0f172a; }
    @media (max-width: 520px) {
      .wrap { margin: 0; border-radius: 0; min-height: 100vh; }
      .grid { grid-template-columns: repeat(9, 32px); }
      input, select { height: 32px; }
      input { width: 32px; font-size: 14px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Sudoku DevOps Demo</h1>
    <p>Generacion aleatoria real por dificultad y validacion inmediata de jugadas.</p>
    <form method="post">
      <div class="topbar">
        <label>
          Dificultad
          <select name="difficulty">
            <option value="facil" <?= $difficulty === 'facil' ? 'selected' : '' ?>>Facil</option>
            <option value="medio" <?= $difficulty === 'medio' ? 'selected' : '' ?>>Medio</option>
            <option value="dificil" <?= $difficulty === 'dificil' ? 'selected' : '' ?>>Dificil</option>
          </select>
        </label>
      </div>
      <input type="hidden" name="base_puzzle" value="<?= htmlspecialchars(encodeGrid($basePuzzle), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="solution_grid" value="<?= htmlspecialchars(encodeGrid($solution), ENT_QUOTES, 'UTF-8') ?>">
      <div class="grid">
        <?php for ($row = 0; $row < 9; $row++): ?>
          <?php for ($col = 0; $col < 9; $col++): ?>
            <?php $isFixed = $basePuzzle[$row][$col] !== 0; ?>
            <?php $class = $isFixed ? 'fija' : $cellStates[$row][$col]; ?>
            <?php
              $blockBorders = [];
              if ($row % 3 === 0) {
                  $blockBorders[] = 'btop';
              }
              if ($col % 3 === 0) {
                  $blockBorders[] = 'bleft';
              }
              if ($row === 8) {
                  $blockBorders[] = 'bbottom';
              }
              if ($col === 8) {
                  $blockBorders[] = 'bright';
              }
              $class = trim($class . ' ' . implode(' ', $blockBorders));
            ?>
            <input
              class="<?= $class ?>"
              type="number"
              min="1"
              max="9"
              name="cell_<?= $row ?>_<?= $col ?>"
              value="<?= $grid[$row][$col] === 0 ? '' : $grid[$row][$col] ?>"
              <?= $isFixed ? 'readonly' : '' ?>
            >
          <?php endfor; ?>
        <?php endfor; ?>
      </div>
      <div class="actions">
        <button type="submit" name="action" value="nuevo" class="secondary">Nuevo Aleatorio</button>
        <button type="submit" name="action" value="validar" class="ghost">Validar Numeros</button>
        <button type="submit" name="action" value="resolver">Resolver Sudoku</button>
      </div>
      <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    </form>
  </div>
</body>
</html>
