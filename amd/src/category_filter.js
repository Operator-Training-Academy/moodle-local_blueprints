/**
 * Category filter auto-submit behaviour.
 *
 * @module     local_blueprints/category_filter
 * @copyright  2026 Operator Training Academy <otancoic@operatortraining.academy>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        init: function() {
            const category = document.querySelector('#local-blueprints-category[data-autosubmit]');

            if (!category) {
                return;
            }

            category.addEventListener('change', function() {
                category.form.submit();
            });
        },
    };
});
