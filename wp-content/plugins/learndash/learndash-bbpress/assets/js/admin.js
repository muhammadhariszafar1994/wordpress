jQuery( document ).ready( function( $ ) {
    $( '.select2' ).select2({
        allowClear: true,
        closeOnSelect: false,
        debug: true,
        dropdownCssClass: 'learndash-select2-dropdown',
        multiple: true,
        placeholder: Learndash_BBPress.string.placeholder,
        width: '100%',
    });
});