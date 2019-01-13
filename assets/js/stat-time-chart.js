let $ = require('jquery');

let Highcharts = require('highcharts/js/highcharts');
require('highcharts/js/highcharts-more');
require('highcharts/css/highcharts.scss');

$(function () {

    $('.chart').each(function () {

        let $element = $(this);

        let title = false;
        if (undefined !== $element.data('title')) {
            title = {
                text: $element.data('title')
            };
        }

        Highcharts.chart(
            $element.prop('id'),
            {
                chart: {
                    height: 500,
                    type: 'column'
                },
                xAxis: {
                    categories: $element.data('categories')
                },
                yAxis: {
                    min: 0,
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                        }
                    },
                    title: false
                },
                tooltip: {
                    headerFormat: '<b>{point.x}</b><br/>',
                    pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}<br/>Ratio: {point.percentage:.1f}%'
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                        }
                    }
                },
                title: title,
                series: $element.data('series')
            }
        );

    });

});
