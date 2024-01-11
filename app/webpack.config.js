const path = require('path');
const webpack = require('webpack');
const CompressionPlugin = require('compression-webpack-plugin');

const optionDefinitions = [
    { name: 'verbose', alias: 'v', type: Boolean },
    { name: 'src', type: String, multiple: true, defaultOption: true },
    { name: 'timeout', alias: 't', type: Number },
    { name: 'mode', type: String, defaultOption: 'unknown' },
    { name: 'analyze', type: Boolean, defaultOption: false },
    { name: 'watch', type: Boolean},
];


const commandLineArgs = require('command-line-args');
const options = commandLineArgs(
  optionDefinitions,
  {partial: false}
);

// const TimestampWebpackPlugin = require('timestamp-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
    devtool: false,
    entry: {
        app: [
            './public/tsx/bootstrap.tsx',
        ]
    },
    module: {
        rules: [
            {
                test: /\.(ts|tsx)$/,
                loader: 'ts-loader',
                options: { allowTsInNodeModules: true }
            },
            // {
            //     test: /\.css$/i,
            //     use: ['style-loader', 'css-loader'],
            // },
            {
                enforce: "pre",
                test: /\.js$/,
                loader: "source-map-loader"
            }
        ]
    },
    optimization: {
        splitChunks: {
            cacheGroups: {
                default: false,
                vendors: false,
            }
        }
    },
    output: {
        path: path.resolve(__dirname, 'public/js'),
        filename: '[name].bundle.js'
    },
    performance: {
        hints: false,
        // hints: process.env.NODE_ENV === 'production' ? "warning" : false
    },
    plugins: [
        // new TimestampWebpackPlugin({
        //     path: path.join(__dirname, 'public/dist'),
        //     // default output is timestamp.json
        //     filename: 'timestamp.json'
        // }),
        // new BundleAnalyzerPlugin({
        //     analyzerHost: "0.0.0.0",
        //     analyzerMode: options.analyze === "enabled" ? 'static': "server",
        //     openAnalyzer: false
        // })

        new webpack.SourceMapDevToolPlugin({
            filename: '[name].bundle.map',
            // publicPath: "http://local.phpopendocs.com/js/app.bundle.js.map"
            sourceRoot: "/var/app/app/public"
        }),

        new CompressionPlugin(),

        new webpack.EnvironmentPlugin({
            // We don't define NODE_ENV. That is for server side node
            // applications.
            // NODE_ENV: 'development',
            BRISTOLIAN_API_BASE_URL: undefined,
        }),

        new webpack.optimize.LimitChunkCountPlugin({
            maxChunks: 1,
        }),
    ],
    resolve: {
        extensions: ['.js', '.jsx', '.json', '.ts', '.tsx'],
        "alias": {
            "react": "preact/compat",
            "react-dom/test-utils": "preact/test-utils",
            "react-dom": "preact/compat"
        }
    },
};