/* Handle adding and removing list elements */
$( () => {
    const parentNode = $('[data-list=referrers]');
    setupListControls(parentNode);
});

/**
 * Listen for bubbling click events on the Add/Remove buttons of a list of inputs.
 * The first item is expected to have an Add button for adding new entries.
 * All subsequent items have a Remove button that removes themself.
 * The UI may be left with some blank inputs, so data must be sanitised in the backend
 * when posted.
 * @param {Node} parentNode The enclosing parent node, where we can add new elements.
 */
function setupListControls (parentNode) {
    $(parentNode).on('click', '.btn-success', () => {
        // NB This markup should match what's used in coupon_admin.php when building
        // the UI from existing content.
        $('<div class="col-sm-12" data-list-entry>' +
            '<div class="input-group"><input type="text" name="coupon_referrer[]" class="form-control">' +
            '<div class="input-group-btn">' +
            '    <button type="button" class="btn btn-danger">' +
            '    <i class="fa-solid fa-times"></i>' +
            '    </button>' +
            '</div>' +
            '</div>' +
            '</div>').appendTo(parentNode);
    });
    $(parentNode).on('click', '.btn-danger', (e) => {
        // Find the enclosing element with a data-list-entry attribute and remove it.
        const parents = $(e.currentTarget).parents('[data-list-entry]');
        parents.remove();
    });

}