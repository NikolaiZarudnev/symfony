import toastr from 'toastr';

$(document).ready(() => {
    $('#flash-messages .alert').each((index, el) => {
        toastr.success(el.dataset.message, el.dataset.label);
    });
});
