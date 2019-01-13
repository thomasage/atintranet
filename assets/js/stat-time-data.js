let $ = require('jquery');

$(function () {

    $('tr[data-project][data-total=1]').on('click', function () {
        let project = $(this).data('project');
        $(this).closest('tbody').find('tr[data-project=' + project + '][data-detail=1]').toggleClass('d-none');
    });

});
