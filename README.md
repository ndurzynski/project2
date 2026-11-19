# Last light

Project 02, Web Programming. PHP, HTML and CSS.

Run locally with PHP 8 or newer:

```
LASTLIGHT_DATA_DIR="$HOME/lastlight-data" php -S localhost:8000
```

Open http://localhost:8000 and register. The server stores password hashes in a private JSON file, not in the public folder. CODD uses PHP's temporary directory by default. Set `LASTLIGHT_DATA_DIR` for permanent storage elsewhere.

The story has 17 scenes and three endings. PHP sessions hold the current character, inventory, score and choice history. The bookmark cookie must match the session. The leaderboard ranks completed games and keeps each player's best score.

The consequence rule reads each choice's requirements and changes. It shows costs and missing stats or items before the choice. It does not predict later scenes. Ending notes use the character class and collected items.

Three rule tests passed: strength 3 against a requirement of 6 reports 3 missing; a choice needing oil reports missing oil when the inventory is empty; a choice with health -2, score +10 and a tool shows all three changes. All three endings were also tested through the live site.