/*
 * Abandoned Castle maze editor.
 *
 * The page renders the palette and the grid; this script lets them be used
 * with the mouse and the keyboard and keeps the layout. Room 0 is the
 * south-west corner, like in the game; the grid shows north at the top.
 */
(function () {
    'use strict';

    var config = window.abandonCastleEditor;
    if (!config) {
        return;
    }

    var size = config.width * config.height;
    var layout = new Array(size).fill('');
    var selected = '';

    var palette = Array.prototype.slice.call(document.querySelectorAll('.ac-palette-tile'));
    var cells = [];
    Array.prototype.forEach.call(document.querySelectorAll('.ac-grid-cell'), function (cell) {
        cells[Number(cell.getAttribute('data-index'))] = cell;
    });
    var status = document.getElementById('ac-status');

    function format(text) {
        var args = Array.prototype.slice.call(arguments, 1);
        return text.replace(/%(\d+)\$s|%s/g, function (match, position) {
            return String(position ? args[position - 1] : args.shift());
        });
    }

    function say(text) {
        // Clearing first makes a repeated message announced again.
        status.textContent = '';
        window.setTimeout(function () { status.textContent = text; }, 30);
    }

    function roomName(index) {
        return String.fromCharCode(65 + (index % config.width)) + (Math.floor(index / config.width) + 1);
    }

    function tileLabel(letter) {
        return letter && config.tiles[letter] ? config.tiles[letter].label : config.text.empty;
    }

    function allowedAt(index, letter) {
        return index !== config.entrance || config.tiles[letter].passage;
    }

    function draw(index) {
        var letter = layout[index];
        var cell = cells[index];
        cell.innerHTML = letter && config.tiles[letter] ? config.tiles[letter].svg : '';
        var label = roomName(index) + ': ' + tileLabel(letter);
        if (index === config.entrance) {
            label += ', ' + config.text.entrance;
        }
        cell.setAttribute('aria-label', label);
    }

    function place(index, letter) {
        if (!letter) {
            say(config.text.pickFirst);
            return;
        }
        if (!allowedAt(index, letter)) {
            say(config.text.entranceNeedsPassage);
            return;
        }
        layout[index] = letter;
        draw(index);
        say(format(config.text.placed, tileLabel(letter), roomName(index)));
    }

    function empty(index) {
        layout[index] = '';
        draw(index);
        say(format(config.text.emptied, roomName(index)));
    }

    function select(button) {
        palette.forEach(function (other) {
            other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
        });
        selected = button.getAttribute('data-letter');
    }

    function focusCell(index) {
        cells.forEach(function (cell, i) {
            cell.setAttribute('tabindex', i === index ? '0' : '-1');
        });
        cells[index].focus();
    }

    // Whether any exit can be walked to from the entrance.
    function exitReachable() {
        var seen = {};
        var queue = [config.entrance];
        seen[config.entrance] = true;
        var steps = { N: config.width, S: -config.width, W: -1, E: 1 };
        while (queue.length) {
            var index = queue.shift();
            var letter = layout[index];
            if (letter === config.exit) {
                return true;
            }
            var exits = config.exits[letter] || '';
            if (index === config.entrance) {
                exits = exits.replace('S', '');
            }
            for (var i = 0; i < exits.length; i++) {
                var direction = exits.charAt(i);
                var x = index % config.width;
                if ((direction === 'W' && x === 0) || (direction === 'E' && x === config.width - 1)) {
                    continue;
                }
                var next = index + steps[direction];
                if (next < 0 || next >= size || seen[next]) {
                    continue;
                }
                seen[next] = true;
                queue.push(next);
            }
        }
        return false;
    }

    palette.forEach(function (button) {
        button.addEventListener('click', function () { select(button); });
    });

    cells.forEach(function (cell, index) {
        cell.addEventListener('click', function () {
            focusCell(index);
            place(index, selected);
        });
        cell.addEventListener('keydown', function (event) {
            var x = index % config.width;
            var target = null;
            switch (event.key) {
                case 'ArrowUp':
                    target = index + config.width < size ? index + config.width : null;
                    break;
                case 'ArrowDown':
                    target = index - config.width >= 0 ? index - config.width : null;
                    break;
                case 'ArrowLeft':
                    target = x > 0 ? index - 1 : null;
                    break;
                case 'ArrowRight':
                    target = x < config.width - 1 ? index + 1 : null;
                    break;
                case 'Delete':
                case 'Backspace':
                    event.preventDefault();
                    empty(index);
                    return;
                default:
                    return;
            }
            event.preventDefault();
            if (target !== null) {
                focusCell(target);
            }
        });
    });

    document.getElementById('ac-fill').addEventListener('click', function () {
        if (!selected) {
            say(config.text.pickFirst);
            return;
        }
        var count = 0;
        for (var index = 0; index < size; index++) {
            if (layout[index] === '' && allowedAt(index, selected)) {
                layout[index] = selected;
                draw(index);
                count++;
            }
        }
        say(count ? format(config.text.filled, count, tileLabel(selected)) : config.text.nothingToFill);
    });

    document.getElementById('ac-clear').addEventListener('click', function () {
        layout.fill('');
        cells.forEach(function (cell, index) { draw(index); });
        say(config.text.cleared);
    });

    document.getElementById('ac-export').addEventListener('click', function () {
        if (layout.indexOf(config.exit) === -1) {
            say(config.text.noExit);
            return;
        }
        var letters = layout.map(function (letter) { return letter || config.solid; });
        document.getElementById('ac-export-text').value = 'array(' + letters.map(function (letter) {
            return "'" + letter + "'";
        }).join(',') + ');';
        document.getElementById('ac-export-box').hidden = false;
        say(exitReachable() ? config.text.exported : config.text.exported + ' ' + config.text.unreachable);
    });

    document.getElementById('ac-save').addEventListener('click', function () {
        var title = document.getElementById('ac-title').value.trim();
        if (!title) {
            say(config.text.needTitle);
            document.getElementById('ac-title').focus();
            return;
        }
        var body = new URLSearchParams();
        body.set('layout', document.getElementById('ac-export-text').value);
        body.set('title', title);
        body.set(config.tokenField, config.token);
        say(config.text.saving);
        fetch(config.saveUrl, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (response) {
                return response.text().then(function (text) {
                    say(response.ok ? text : config.text.saveFailed + ' ' + text);
                });
            })
            .catch(function () { say(config.text.saveFailed); });
    });

    cells.forEach(function (cell, index) { draw(index); });
}());
