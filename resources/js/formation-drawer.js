export default class FormationDrawer
{
    constructor(players, playersByPosition)
    {
        this.players           = players;
        this.playersByPosition = playersByPosition;
    }

    drawFormation(formationData, selector = '#field')
    {
        // reverse the formation (4231 becomes 1324)
        let formation = formationData.name.split("").reverse().join("");

        let formationPositions = JSON.parse(formationData.formation);

        let output = document.createDocumentFragment();

        // Figure out how many rows/cols this formation needs
        let rows = formation.length;
        let cols = 0;

        for (let r = 0; r < rows; r++)
        {
            if (formation[r] > cols)
            {
                cols = formation[r];
            }
        }

        $(selector).addClass('formation-rows-' + rows);
        $(selector).addClass('formation-cols-' + cols);

        // Add in the goalie
        formation += '1';

        // Start drawing the formation now
        $(selector).addClass('formation');

        // Loop through each row in the formation
        for (let r = 0; r < formation.length; r++)
        {
            let rowDiv = document.createElement('div');
            rowDiv.className = 'row g-0'

            let columnCount = formation[r];

            // Loop through each column in this row
            for (let c = 0; c < columnCount; c++)
            {
                let colDiv = document.createElement('div');
                colDiv.className = 'col';

                let positionDiv = document.createElement('div');
                positionDiv.className = 'position empty';

                colDiv.appendChild(positionDiv);

                let cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn-close';
                cancelBtn.type = 'button';

                positionDiv.appendChild(cancelBtn);

                // Create the player picker dropdown
                let playerDropDiv = document.createElement('div');
                playerDropDiv.className = 'dropdown';

                let addSpan = document.createElement('span');
                addSpan.className = 'player-picker fs-3 bi bi-person-plus-fill';
                addSpan.setAttribute('data-bs-toggle', 'dropdown');
                addSpan.setAttribute('id', 'drop-player-' + r + '-' + c);

                playerDropDiv.appendChild(addSpan);

                let playerDropMenuDiv = document.createElement('div');
                playerDropMenuDiv.className = 'dropdown-menu player';

                // add the players
                let a = '';
                let shown = [];

                let position = formationPositions[r][c];

                // show players for this position first
                $.each(this.playersByPosition[position], (i, player) => {
                    shown.push(player.id);

                    let prepend = position + ':';
                    a = this.createDropdownItemLink(player, prepend);

                    playerDropMenuDiv.appendChild(a);
                });

                let divider = document.createElement('hr');
                divider.className = 'dropdown-divider';

                playerDropMenuDiv.appendChild(divider);

                // show all the other players
                $.each(this.players, (i, player) => {
                    if (shown.indexOf(player.id) !== -1)
                    {
                        return; // skip this player, he was already shown above
                    }

                    a = this.createDropdownItemLink(player);

                    playerDropMenuDiv.appendChild(a);
                });

                playerDropDiv.appendChild(playerDropMenuDiv);
                positionDiv.appendChild(playerDropDiv);

                // Create the position name
                let posName = document.createElement('h4');
                posName.textContent = position;

                positionDiv.setAttribute('data-player-position', position);

                positionDiv.appendChild(posName);

                // Create the event picker
                let eventDropDiv = document.createElement('div');
                eventDropDiv.className = 'event-picker';
                eventDropDiv.setAttribute('id', 'drop-event-' + r + '-' + c);

                positionDiv.appendChild(eventDropDiv);

                rowDiv.appendChild(colDiv);
            }

            output.appendChild(rowDiv);
        }

        $(selector).append(output);
    }

    createDropdownItemLink(player, prependString = '')
    {
        let display = player.name;

        if (player.nickname !== '' && player.nickname !== null)
        {
            display = player.nickname;
        }
        if (player.number !== '' && player.number !== null)
        {
            display = '(' + player.number + ') ' + display;
        }

        let link = document.createElement('a');

        link.textContent = prependString + ' ' + display;
        link.className = 'dropdown-item';
        link.setAttribute('data-player-id', player.id);
        link.setAttribute('href', '#');

        return link;
    }

    addPlayer($positionDiv, playerId)
    {
        let playerData   = this.players[playerId];
        let $eventPicker = $positionDiv.find('.event-picker');

        let img = document.createElement('img');
        img.className = 'img-fluid rounded-circle';
        img.setAttribute('data-player-id', playerId);
        img.setAttribute('title', playerData.name);
        img.src = '/' + playerData.photo;

        let nameSpan = document.createElement('span');
        nameSpan.className = 'name badge text-bg-dark opacity-75 fw-normal overflow-hidden';
        nameSpan.textContent = playerData.name;

        $positionDiv.removeClass('empty');
        $eventPicker.append(img);
        $eventPicker.append(nameSpan);
    }

    addPlayerStarters(starters, selector = '#field')
    {
        // Loop through the already drawn positions on the field
        $(selector + ' .row .col .position.empty').each((index, positionDiv) => {
            let $positionDiv = $(positionDiv);
            let thisPosition = $positionDiv.attr('data-player-position');

            for (let playerId in starters)
            {
                // this starter, starts in this position
                if (starters[playerId] == thisPosition)
                {
                    this.addPlayer($positionDiv, playerId);
                    delete starters[playerId];
                    break;
                }
            }
        });
    }
}
