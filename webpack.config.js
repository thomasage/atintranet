var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/js/app.js')
    .addEntry('client', './assets/js/client.js')
    .addEntry('invoice', './assets/js/invoice.js')
    .addEntry('stat-time-chart', './assets/js/stat-time-chart.js')
    .addEntry('stat-time-data', './assets/js/stat-time-data.js')
    .addEntry('stat-turnover-client', './assets/js/stat-turnover-client.js')
    .addEntry('stat-turnover-period', './assets/js/stat-turnover-period.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .splitEntryChunks()
;

module.exports = Encore.getWebpackConfig();
