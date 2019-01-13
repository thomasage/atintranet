require('../css/app.scss');

let $ = require('jquery');

require('bootstrap');
require('font-awesome/scss/font-awesome.scss');

let Highcharts = require('highcharts/js/highcharts');
require('highcharts/js/highcharts-more');
require('highcharts/css/highcharts.scss');

global.scrollToElement = function (element, increment = 0) {

    $('html,body').animate(
        {
            scrollTop: element.offset().top + increment
        },
        1000
    );

};
