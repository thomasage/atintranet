let $ = require('jquery');

let Highcharts = require('highcharts/js/highcharts');
require('highcharts/js/highcharts-more');
require('highcharts/css/highcharts.scss');

$(function () {

    $('.chart').each(function () {

        let $element = $(this);

        Highcharts.chart(
            $element.prop('id'),
            {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: false,
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: $element.data('series')
            });

    });

});
