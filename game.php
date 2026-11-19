<?php
require 'common.php';
protect();
require 'story.php';
$error = '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = field('action');
    if ($action === 'start' && isset($classes[field('class')])) {
        $class = field('class');
        $_SESSION['game'] = array_merge($classes[$class], ['class'=>$class, 'max_health'=>$classes[$class]['health'], 'node'=>'shore', 'score'=>0, 'inventory'=>[], 'history'=>[], 'saved'=>false]);
    } elseif ($action === 'choose' && isset($_SESSION['game'])) {
        $game = &$_SESSION['game'];
        $number = field('choice');
        // The hidden node rejects old forms. The client never supplies a destination or a score.
        if (field('node') !== $game['node'] || !ctype_digit($number) || !isset($story[$game['node']]['choices'][(int)$number])) {
            $error = 'That choice is no longer available. Use the current page.';
        } else {
            $choice = $story[$game['node']]['choices'][(int)$number];
            if (deficits($choice, $game)) { $error = 'That choice is locked: ' . implode('; ', deficits($choice, $game)) . '.'; }
            else {
                $game['history'][] = $story[$game['node']]['title'] . ': ' . $choice['text'];
                $game['health'] = max(0, min($game['max_health'], $game['health'] + ($choice['health'] ?? 0)));
                $game['score'] += $choice['score'] ?? 0;
                if (isset($choice['get']) && !in_array($choice['get'], $game['inventory'], true)) { $game['inventory'][] = $choice['get']; }
                $game['node'] = $game['health'] === 0 ? 'failure' : $choice['to'];
                if ($game['health'] === 0) { $game['history'][] = 'Your injuries forced you to stop before the light was ready.'; }
            }
        }
    } elseif ($action === 'resume' && isset($_SESSION['game'])) {
        // The cookie is a bookmark only. The session remains the sole source of game state.
        $bookmark = $_COOKIE['lastlight_node'] ?? '';
        if (!is_string($bookmark) || !isset($story[$bookmark]) || $bookmark !== $_SESSION['game']['node']) {
            $error = 'The bookmark does not match this session. Your current game has not changed.';
        } else { $_SESSION['message'] = 'Resumed your saved position.'; }
    } else { $error = 'Choose a valid class or action.'; }
    if ($error === '') { redirect('game.php'); }
}
if (isset($_SESSION['game'])) {
    $game = &$_SESSION['game'];
    setcookie('lastlight_node', $game['node'], ['expires'=>time()+604800, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    $node = $story[$game['node']];
    if (isset($node['ending']) && !$game['saved']) {
        try {
            storage(function ($data) use ($game, $node) {
                $user = $_SESSION['username'];
                if (!isset($data['scores'][$user]) || $game['score'] > $data['scores'][$user]['score']) {
                    $data['scores'][$user] = ['username'=>$data['users'][$user]['name'], 'score'=>$game['score'], 'ending'=>$node['ending'], 'class'=>$game['class']];
                }
                return $data;
            });
            $game['saved'] = true;
        } catch (Throwable $exception) { $error = 'Your ending is complete, but the score could not be saved. Check the private file permissions and reload.'; }
    }
}
page_start(isset($node) ? $node['title'] : 'Choose your character');
if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif;
if ($message): ?><p role="status"><?= e($message) ?></p><?php endif;
if (!isset($game)): ?>
<p>Pick a character. Some choices need a certain strength, wisdom or item. If your health reaches zero, the game ends.</p>
<form method="post"><?php token(); ?><input type="hidden" name="action" value="start">
<?php foreach ($classes as $name=>$stats): ?>
<label class="class-option"><input type="radio" name="class" value="<?= e($name) ?>" required> <?= e(ucfirst($name)) ?>: health <?= $stats['health'] ?>, strength <?= $stats['strength'] ?>, wisdom <?= $stats['wisdom'] ?></label>
<?php endforeach; ?><p><button>Begin</button></p></form>
<?php else: ?>
<div class="stats" aria-label="Character stats"><span><?= e(ucfirst($game['class'])) ?></span><span>Health <?= $game['health'] ?>/<?= $game['max_health'] ?></span><span>Strength <?= $game['strength'] ?></span><span>Wisdom <?= $game['wisdom'] ?></span><span>Score <?= $game['score'] ?></span></div>
<p class="inventory">Inventory: <?= e($game['inventory'] ? implode(', ', $game['inventory']) : 'empty') ?></p>
<div class="game-layout"><section class="<?= isset($node['ending']) ? 'ending ending-' . e(strtolower($node['ending'])) : 'scene' ?>" aria-label="Story">
<p class="story-text"><?= e($node['text']) ?></p>
<?php if (isset($node['ending'])): ?>
<h2><?= e($node['ending']) ?> ending</h2>
<p>You finished as a <?= e($game['class']) ?> with <?= $game['score'] ?> points and <?= $game['health'] ?> health.</p>
<h3>Your choices</h3>
<p><?= e(match($game['class']) { 'warrior'=>'You chose warrior, with high strength and low wisdom.', 'mage'=>'You chose mage, with high wisdom and low strength.', default=>'You chose rogue, with equal strength and wisdom.' }) ?></p>
<p><?= e(in_array('repaired gear', $game['inventory'], true) ? 'You repaired the drive gear.' : 'You left the drive gear unrepaired.') ?> <?= e(in_array('bell echo', $game['inventory'], true) ? 'You rang the old bell, opening the secret light route.' : 'You did not ring the old bell.') ?> <?= e(in_array('oil', $game['inventory'], true) ? 'You brought lamp oil.' : 'You reached the end without oil.') ?></p>
<p><a href="leaderboard.php">View scores</a></p>
<form method="post"><?php token(); ?><input type="hidden" name="action" value="start"><input type="hidden" name="class" value="<?= e($game['class']) ?>"><button>Play again with this class</button></form>

<?php else: ?>
<h2>What do you do?</h2>
<?php foreach ($node['choices'] as $i=>$choice): $locked = deficits($choice, $game); ?>
<form method="post" class="choice"><?php token(); ?><input type="hidden" name="action" value="choose"><input type="hidden" name="node" value="<?= e($game['node']) ?>"><input type="hidden" name="choice" value="<?= $i ?>">
<button <?= $locked ? 'disabled' : '' ?>><?= e($choice['text']) ?></button>
<p class="notes"><?= $locked ? 'Locked. ' : '' ?><?= e(implode('; ', choice_notes($choice, $game))) ?></p></form>
<?php endforeach; ?>
<form method="post"><?php token(); ?><input type="hidden" name="action" value="resume"><button>Resume saved position</button></form>

<?php endif; ?>
</section><aside><h2>Previous choices</h2>
<?php if (!$game['history']): ?><p>No choices yet.</p><?php else: ?><ol><?php foreach ($game['history'] as $entry): ?><li><?= e($entry) ?></li><?php endforeach; ?></ol><?php endif; ?>
</aside></div>
<details><summary>Start over with another class</summary><p>This replaces the current run.</p><form method="post"><?php token(); ?><input type="hidden" name="action" value="start"><label for="class">Class</label><select name="class" id="class"><?php foreach ($classes as $name=>$stats): ?><option value="<?= e($name) ?>"><?= e(ucfirst($name)) ?></option><?php endforeach; ?></select> <button>Start over</button></form></details>
<?php endif; page_end(); ?>
