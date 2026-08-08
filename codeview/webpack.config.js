const path = require('path');
const webpack = require('webpack');

module.exports = {
  devtool: false,
  entry: {
    codeview: ['./public/tsx/bootstrap.tsx'],
  },
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        loader: 'ts-loader',
        options: { allowTsInNodeModules: true },
      },
      {
        enforce: 'pre',
        test: /\.js$/,
        loader: 'source-map-loader',
      },
    ],
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        default: false,
        vendors: false,
      },
    },
  },
  output: {
    path: path.resolve(__dirname, 'public/js'),
    publicPath: '',
    filename: '[name].bundle.js',
  },
  performance: {
    hints: false,
  },
  plugins: [
    new webpack.optimize.LimitChunkCountPlugin({
      maxChunks: 1,
    }),
  ],
  resolve: {
    extensions: ['.js', '.jsx', '.json', '.ts', '.tsx'],
    alias: {
      react: 'preact/compat',
      'react-dom/test-utils': 'preact/test-utils',
      'react-dom': 'preact/compat',
    },
  },
  stats: {
    all: false,
    modules: true,
    errors: true,
    warnings: true,
    builtAt: true,
    timings: true,
    entrypoints: true,
  },
};
