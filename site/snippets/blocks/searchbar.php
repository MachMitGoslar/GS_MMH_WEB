<form method="get" action="<?= url('project-archive') ?>" class="searchbar">
    <input
        type="search"
        name="q"
        placeholder="Projekte durchsuchen..."
        value="<?= esc(get('q') ?? '') ?>"
        aria-label="Projekte durchsuchen"
    >
    <button class="gs-c-btn" data-type="secondary" data-size="regular" type="submit">Suchen</button>
</form>

