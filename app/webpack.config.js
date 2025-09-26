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


// const TimestampWebpackPlugin = require('timestamp-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;


const commandLineArgs = require('command-line-args');
const options = commandLineArgs(
  optionDefinitions,
  {partial: true}
);

console.log("Bundle analyzer enabled:", !!options.analyze);
// const options = commandLineArgs(optionDefinitions, { partial: true });


const analyzerPlugins = options.analyze ? [
    new BundleAnalyzerPlugin({
        analyzerMode: "static",
        openAnalyzer: false,
        reportFilename: "bundle-report.html",
    })
] : [];



// const analyzerPlugins = options.analyze === "enabled" ? [
//     new BundleAnalyzerPlugin({
//         analyzerHost: "0.0.0.0",
//         analyzerPort: 8888,
//         analyzerMode: "server",
//         openAnalyzer: false,
//     })
// ] : [];


//

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
        publicPath: '/js/',     // <— tell Webpack that bundles are served from /js/
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
        new BundleAnalyzerPlugin({
            // analyzerHost: "0.0.0.0",
            // analyzerMode: options.analyze === "enabled" ? 'static': "server",
            // openAnalyzer: false


            analyzerMode: "static",
            openAnalyzer: false,
            generateStatsFile: true,
            compressionAlgorithm: "gzip",

            statsOptions: "verbose",

        }),

        new webpack.SourceMapDevToolPlugin({
            filename: '[name].bundle.map',
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

        ...analyzerPlugins
    ],
    resolve: {
        extensions: ['.js', '.jsx', '.json', '.ts', '.tsx'],
        "alias": {
            "react": "preact/compat",
            "react-dom/test-utils": "preact/test-utils",
            "react-dom": "preact/compat"
        }
    },


    stats: {
        all: false,      // start with nothing
        modules: true,   // show every module individually
        errors: true,
        warnings: true,
        builtAt: true,
        timings: true,
        entrypoints: true,
    },


};