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
                    type: 'column'
                },
                title: false,
                xAxis: {
                    categories: $element.data('categories')
                },
                yAxis: {
                    min: 0,
                    title: false,
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                        }
                    }
                },
                tooltip: {
                    headerFormat: '<b>{point.x}</b><br/>',
                    pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
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
                series: $element.data('series')
            }
        );

    });

});
