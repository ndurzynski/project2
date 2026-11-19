<?php
require 'common.php';
protect();
$error = '';
try { $scores = array_values(storage()['scores']); }
catch (Throwable $exception) { $scores = []; $error = 'Scores could not be loaded. Check the private file permissions.'; }
usort($scores, function ($a, $b) { return ($b['score'] <=> $a['score']) ?: strcmp($a['username'], $b['username']); });
$scores = array_slice($scores, 0, 10);
$_SESSION['leaderboard'] = $scores;
$scores = $_SESSION['leaderboard'];
page_start('Leaderboard');
?>
<p>The ten highest scores. Only your best game counts.</p>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if (!$scores): ?><p>No completed games yet.</p><?php else: ?>
<div class="table-wrap"><table><caption>Best scores</caption><thead><tr><th scope="col">Rank</th><th scope="col">Player</th><th scope="col">Score</th><th scope="col">Class</th><th scope="col">Ending</th></tr></thead><tbody>
<?php foreach ($scores as $rank=>$score): ?><tr><td><?= $rank+1 ?></td><td><?= e($score['username']) ?></td><td><?= (int)$score['score'] ?></td><td><?= e($score['class']) ?></td><td><?= e($score['ending']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; page_end(); ?>
