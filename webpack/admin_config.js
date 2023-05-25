var path = require('path');
var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.EnableVersionning = function() {
    Encore.configureFilenames({
        js: '[name].[fullhash:8].js',
        css: '[name].[fullhash:8].css',
    })
    return Encore;
};

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/admin/')
    // public path used by the web server to access the output path
    .setPublicPath('/build/admin')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */

    // @important addEntry here

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .cleanupOutputBeforeBuild()
    .EnableVersionning()
    .enablePostCssLoader()

    .enableVueLoader(() => {}, {
        runtimeCompilerBuild: false,
        useJsx: true,
    })

    .enableSourceMaps(false)
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

;

var config = Encore.getWebpackConfig();

// https://stackoverflow.com/questions/43107233/configuration-resolve-has-an-unknown-property-root
config.resolve.modules = [path.resolve('./assets'), 'node_modules']

// Set a unique name for the config (needed later!)
config.name = 'admin';

// reset Encore to build the second config
Encore.reset();

// yarn encore dev --config-name admin
module.exports = config;
