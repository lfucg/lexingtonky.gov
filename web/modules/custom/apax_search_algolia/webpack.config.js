const path = require('path');
const webpack = require('webpack');
const { CleanWebpackPlugin} = require('clean-webpack-plugin');
const CaseSensitivePathsPlugin = require('case-sensitive-paths-webpack-plugin');
const WatchMissingNodeModulesPlugin = require('react-dev-utils/WatchMissingNodeModulesPlugin');
const ModuleNotFoundPlugin = require('react-dev-utils/ModuleNotFoundPlugin');
const isDevMode = process.env.NODE_ENV !== 'production';

const commitHash = require('child_process')
  .execSync('git rev-parse --short HEAD')
  .toString()
  .trim();

const appPath = './react/src/index.js';
const autocompletePath = './react/src/autocomplete.js';
const polyfills = [
  'element-closest-polyfill',
  'core-js/stable',
  'regenerator-runtime/runtime',
];

const config = {
  entry: {
    index: [
      ...polyfills,
      appPath,
    ],
    autocomplete: [
      ...polyfills,
      autocompletePath,
    ]
  },
  optimization: {
    usedExports: 'global',
    splitChunks: {
      cacheGroups: {
        common: {
          name: 'common',
          chunks: 'initial',
          minChunks: 2,
        },
      }
    }
  },
  devtool: (isDevMode) ? 'source-map' : false,
  devServer: {
    host: '0.0.0.0',
    hot: 'only',
    liveReload: false,
    allowedHosts: 'all',
    static: false,
    client: {
      webSocketURL: 'auto://lexky-d8-hmr.lndo.site',
      logging: 'none',
      overlay: {
        errors: true,
        warnings: false,
      },
    },
    devMiddleware: {
      writeToDisk: true,
      stats: {
        colors: true,
        hash: false,
        version: false,
        timings: true,
        modules: false,
      },
    },
  },
  mode: (isDevMode) ? 'development' : 'production',
  output: {
    path: path.resolve(__dirname, "react/dist"),
    filename: '[name].min.js'
  },
  resolve: {
    extensions: ['.js', '.jsx'],
    fallback: { "url": require.resolve("url/") }
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        include: path.join(__dirname, 'react/src'),
      },
      {
        test: /\.(png|jp(e*)g|svg|gif)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: 'images/[hash]-[name].[ext]',
              publicPath: '/modules/custom/apax_algolia_search/react/dist/'
            },
          },
        ],
      },
    ],
  },
  plugins: [
    // This gives some necessary context to module not found errors, such as
    // the requesting resource.
    new ModuleNotFoundPlugin(appPath),
    new ModuleNotFoundPlugin(autocompletePath),
    // Needed for chalk.js
    // new webpack.ProvidePlugin({ process: 'process/browser' }),
    // Makes some environment variables available to the JS code
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development'),
      },
      __COMMIT_HASH__: JSON.stringify(commitHash)
    }),
    // Watcher doesn't work well if you mistype casing in a path so we use
    // a plugin that prints an error when you attempt to do this.
    // See https://github.com/facebook/create-react-app/issues/240
    isDevMode && new CaseSensitivePathsPlugin(),
    // If you require a missing module and then `npm install` it, you still have
    // to restart the development server for Webpack to discover it. This plugin
    // makes the discovery automatic so you don't have to restart.
    // See https://github.com/facebook/create-react-app/issues/186
    isDevMode && new WatchMissingNodeModulesPlugin('./node_modules'),
    // Delete old dist folder before rebuilding
    new CleanWebpackPlugin(),
  ].filter(Boolean),
  // Turn off performance processing because we utilize
  // our own hints via the FileSizeReporter
  performance: false,
};

module.exports = config;
