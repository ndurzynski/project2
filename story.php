<?php
// Each choice describes its destination, requirements and consequences.
$classes = [
    'warrior'=>['health'=>14, 'strength'=>8, 'wisdom'=>3],
    'mage'=>['health'=>10, 'strength'=>3, 'wisdom'=>8],
    'rogue'=>['health'=>12, 'strength'=>5, 'wisdom'=>5]
];
$story = [
 'shore'=>['title'=>'The dark shore', 'text'=>'You reach the beach below the lighthouse. The light is off and a fishing boat is still out in the storm. An old wreck is nearby. A path leads to the cottage.', 'choices'=>[
  ['text'=>'Search the wreck on the beach', 'to'=>'wreck', 'score'=>5],
  ['text'=>'Head toward the keeper\'s cottage', 'to'=>'cottage']]],
 'wreck'=>['title'=>'The wreck', 'text'=>'There is a tin of lamp oil under a plank. You can lift the plank or reach through the gap, but the edges are sharp.', 'choices'=>[
  ['text'=>'Lift the plank', 'to'=>'cottage', 'requires'=>['strength'=>5], 'get'=>'oil', 'score'=>10],
  ['text'=>'Pull the tin through the gap', 'to'=>'cottage', 'health'=>-2, 'get'=>'oil', 'score'=>5],
  ['text'=>'Leave it and take the shore path', 'to'=>'cliff']]],
 'cottage'=>['title'=>'The keeper\'s cottage', 'text'=>'The cottage roof is starting to collapse. You spot a tool and a journal on the desk. You can grab one before you have to leave.', 'choices'=>[
  ['text'=>'Take the repair tool', 'to'=>'gate', 'get'=>'tool', 'score'=>5],
  ['text'=>'Read the journal', 'to'=>'journal', 'score'=>5]]],
 'journal'=>['title'=>'A page in the journal', 'text'=>'The journal mentions a tunnel under the tower and a lens that works without oil. Ringing a bell activates it. A key is tucked inside the cover.', 'choices'=>[
  ['text'=>'Take the brass key and follow the sea wall', 'to'=>'tide', 'get'=>'brass key', 'score'=>10],
  ['text'=>'Leave the key and go to the gate', 'to'=>'gate']]],
 'cliff'=>['title'=>'The cliff path', 'text'=>'Part of the path has fallen into the sea. You could get across on the ledge, or go back through the cottage.', 'choices'=>[
  ['text'=>'Cross the ledge carefully', 'to'=>'gate', 'health'=>-3, 'score'=>5],
  ['text'=>'Turn back to the cottage', 'to'=>'cottage']]],
 'tide'=>['title'=>'The tide door', 'text'=>'You find a locked door under the sea wall. The key from the journal fits. Water is coming up the steps.', 'choices'=>[
  ['text'=>'Unlock the tide door', 'to'=>'tunnel', 'items'=>['brass key'], 'score'=>10, 'note'=>'Search for another way to light the coast.'],
  ['text'=>'Return to the tower gate', 'to'=>'gate']]],
 'tunnel'=>['title'=>'Under the tower', 'text'=>'There is a glass lens beside a bell. The lens is already glowing a little. Instructions are scratched into its stand.', 'choices'=>[
  ['text'=>'Read the markings and carry the lens', 'to'=>'bell', 'requires'=>['wisdom'=>5], 'get'=>'moon lens', 'score'=>15],
  ['text'=>'Carry the lens without reading', 'to'=>'bell', 'health'=>-4, 'get'=>'moon lens', 'score'=>5],
  ['text'=>'Retreat to the gate', 'to'=>'gate']]],
 'bell'=>['title'=>'The drowned bell', 'text'=>'You have the lens. The journal said to ring this bell before using it. The rope is still attached.', 'choices'=>[
  ['text'=>'Ring the bell to wake the old light', 'to'=>'gate', 'get'=>'bell echo', 'score'=>10, 'note'=>'The lens may answer this sound later.'],
  ['text'=>'Leave quietly', 'to'=>'gate']]],
 'gate'=>['title'=>'The iron gate', 'text'=>'A beam blocks the door. A counterweight is attached to one end. There is also enough space to crawl under it, but broken wood is in the way.', 'choices'=>[
  ['text'=>'Force the beam aside', 'to'=>'hall', 'requires'=>['strength'=>7], 'score'=>10],
  ['text'=>'Find the counterweight', 'to'=>'hall', 'requires'=>['wisdom'=>5], 'score'=>10],
  ['text'=>'Crawl underneath the beam', 'to'=>'hall', 'health'=>-3]]],
 'hall'=>['title'=>'The entrance hall', 'text'=>'There are supplies near the stairs. Take something for the lamp or stop to bandage your cuts.', 'choices'=>[
  ['text'=>'Take the spare oil', 'to'=>'stairs', 'get'=>'oil', 'score'=>5],
  ['text'=>'Take the repair tool', 'to'=>'stairs', 'get'=>'tool', 'score'=>5],
  ['text'=>'Bind your cuts with clean cloth', 'to'=>'stairs', 'health'=>3]]],
 'stairs'=>['title'=>'The broken stairs', 'text'=>'The stairs are missing a few steps. You could repair them with a tool. The service ladder still reaches the next floor, though it is hard to climb.', 'choices'=>[
  ['text'=>'Climb the service ladder', 'to'=>'workshop', 'health'=>-2, 'score'=>5],
  ['text'=>'Brace the steps with the tool', 'to'=>'workshop', 'items'=>['tool'], 'score'=>10]]],
 'workshop'=>['title'=>'The workshop', 'text'=>'The gear that turns the lamp is stuck. There is a manual on the bench. Fix it with your tool or use the manual to work out what is wrong.', 'choices'=>[
  ['text'=>'Repair the drive gear', 'to'=>'gallery', 'items'=>['tool'], 'get'=>'repaired gear', 'score'=>15],
  ['text'=>'Study the manual and release the jam', 'to'=>'gallery', 'requires'=>['wisdom'=>7], 'get'=>'repaired gear', 'score'=>15],
  ['text'=>'Leave the mechanism and climb', 'to'=>'gallery', 'note'=>'The ordinary lamp needs a working gear.']]],
 'gallery'=>['title'=>'The outside gallery', 'text'=>'A loose shutter keeps swinging across the walkway. You need to get past it to reach the lamp.', 'choices'=>[
  ['text'=>'Secure the shutter', 'to'=>'lantern', 'requires'=>['strength'=>5], 'score'=>10],
  ['text'=>'Work out a safe moment to cross', 'to'=>'lantern', 'requires'=>['wisdom'=>5], 'score'=>10],
  ['text'=>'Run past the shutter', 'to'=>'lantern', 'health'=>-4]]],
 'lantern'=>['title'=>'The lantern room', 'text'=>'The boat is getting close to the rocks. The lamp needs oil and a working gear. Beside it is a socket that could hold the lens from the tunnel.', 'choices'=>[
  ['text'=>'Fill the lamp and start the repaired gear', 'to'=>'heroic', 'items'=>['oil','repaired gear'], 'score'=>40, 'note'=>'The lamp can guide the boat home.'],
  ['text'=>'Place the moon lens in the stone socket', 'to'=>'secret', 'items'=>['moon lens','bell echo'], 'score'=>60, 'note'=>'The old light answers the bell.'],
  ['text'=>'Try the broken lamp anyway', 'to'=>'failure', 'note'=>'Without the repairs, the flame will not hold.'],
  ['text'=>'Signal from the window with a match', 'to'=>'failure', 'score'=>5, 'note'=>'The match cannot be seen through the rain.']]],
 'heroic'=>['title'=>'A light for the harbor', 'text'=>'The lamp starts turning. The boat changes course and gets past the rocks. You keep the light running until it reaches the harbor.', 'ending'=>'Heroic'],
 'failure'=>['title'=>'Too little light', 'text'=>'The lamp will not stay on. The boat hits the rocks before help arrives. The crew gets rescued in the morning, but the boat is lost.', 'ending'=>'Failure'],
 'secret'=>['title'=>'The light below the sea', 'text'=>'The lens lights up when you put it in the socket. Smaller lights switch on under the water, marking a channel around the rocks. The boat follows them back to shore.', 'ending'=>'Secret']
];
