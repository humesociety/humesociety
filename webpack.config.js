const Encore = require('@symfony/webpack-encore')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('site', './assets/js/site.js')
  .addEntry('admin', './assets/js/admin.js')
  .addEntry('conference', './assets/js/conference.js')
  .addEntry('journal', './assets/js/journal.js')
  .addEntry('tech', './assets/js/tech.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(() => {}, { useBuiltIns: 'usage', corejs: 3 })
  .enableSassLoader()

module.exports = Encore.getWebpackConfig()
