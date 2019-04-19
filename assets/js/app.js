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
