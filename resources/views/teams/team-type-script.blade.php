{{-- Adapts the team form to the selected club's type.

     A club team is an age cohort: Birth Year identifies it, and Rank (A/B/C/D)
     optionally splits the several teams a large club fields at the same age.
     A high school team IS its tier, so the tier is required, Birth Year does not
     apply (the squad mixes ages), and only three tiers exist.

     Shared by the team create and edit forms, both of which let the club be
     changed. --}}
<script>
(function () {
    const clubSelect = document.getElementById('club_id');
    const birthField = document.getElementById('birth-year-field');
    const birthInput = document.getElementById('birth_year');
    const rank       = document.getElementById('rank');
    const rankLabel  = document.querySelector('label[for="rank"]');

    if (!clubSelect || !birthField || !birthInput || !rank) {
        return;
    }

    const labels = {
        club:   { A: 'A (best)', B: 'B',  C: 'C',        D: 'D' },
        school: { A: 'Varsity',  B: 'JV', C: 'Freshmen', D: null },
    };

    function apply() {
        const selected = clubSelect.options[clubSelect.selectedIndex];
        const isSchool = ((selected && selected.dataset.clubType) || 'club') === 'school';
        const set      = isSchool ? labels.school : labels.club;

        birthField.classList.toggle('d-none', isSchool);

        // A hidden input still posts its value, which would keep re-saving a
        // stale birth year onto a school team. Disabling it drops it from the
        // submission (the controller also enforces this server-side).
        birthInput.disabled = isSchool;

        // A school team has no meaning without a tier, so it is required there
        // and the empty choice is withdrawn.
        rank.required = isSchool;

        if (rankLabel) {
            rankLabel.textContent = isSchool ? 'Level' : 'Rank';
        }

        Array.from(rank.options).forEach(function (option) {
            if (!option.value) {
                option.hidden   = isSchool;
                option.disabled = isSchool;
                return;
            }

            const text = set[option.value];

            // A tier the current type does not have (school has no 'D')
            option.hidden   = text === null;
            option.disabled = text === null;
            option.textContent = text === null ? option.value : text;
        });

        // If switching to a school left an unavailable tier selected, clear it
        // rather than silently submitting something the server will reject.
        if (rank.selectedIndex >= 0 && rank.options[rank.selectedIndex].hidden) {
            rank.value = '';
        }
    }

    clubSelect.addEventListener('change', apply);

    apply();
})();
</script>
