$(document).ready(function() {
    // -----
    // For each 'password' type input on the page ...
    //
    $('input[type="password"]').each(function() {
        // -----
        // ... that has an 'id=' attribute ...
        //
        let password_id = $(this).attr('id');
        if (!password_id) {
            return true;
        }
        $(this).after('<span toggle="#'+password_id+'" class="fa-solid fa-eye toggle-pw"></span>');
    });

    $('.toggle-pw').click(function() {
        $(this).toggleClass('fa-eye fa-eye-slash');
        let input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
        } else {
            input.attr('type', 'password');
        }
    });
});
