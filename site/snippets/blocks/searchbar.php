<form method="get" action="<?= url('project-archive') ?>" class="searchbar">
    <input
        type="search"
        name="q"
        placeholder="Projekte durchsuchen..."
        value="<?= esc(get('q') ?? '') ?>"
        aria-label="Projekte durchsuchen"
    >
    <?php snippet('utilities/button', ['label' => 'Suchen', 'variant' => 'secondary', 'type' => 'submit']) ?>
</form>

