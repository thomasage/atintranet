require('../css/app.scss');

let $ = require('jquery');
require('jquery-ui/themes/base/all.css');
require('jquery-ui/ui/widgets/autocomplete');

require('bootstrap');
require('font-awesome/scss/font-awesome.scss');

let Highcharts = require('highcharts/js/highcharts');
require('highcharts/js/highcharts-more');
require('highcharts/css/highcharts.scss');

$(function () {

    $('tr[data-url]').click(function () {
        window.location.href = $(this).data('url');
    });

    $(':input[data-autocomplete]').each(function () {
        let url = $(this).data('autocomplete');
        let cache = {};
        $(this).autocomplete({
            minLength: 1,
            source: function (request, response) {
                let term = request.term;
                if (term in cache) {
                    response(cache[term]);
                    return;
                }
                $.getJSON(url, request, function (data) {
                    cache[term] = data;
                    response(data);
                });
            }
        });
    });

});

global.scrollToElement = function (element, increment = 0) {

    $('html,body').animate(
        {
            scrollTop: element.offset().top + increment
        },
        1000
    );

};

global.toNumber = function (input) {

    return parseFloat(input.replace(',', '.'));

};
