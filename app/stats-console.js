// stats-console.js
const webpack = require("webpack");
const config = require("./webpack.config.js");

const compiler = webpack(config);

compiler.run((err, stats) => {
    if (err) {
        console.error("Webpack fatal error:", err);
        process.exit(1);
    }

    if (stats.hasErrors()) {
        console.error("Webpack compilation errors:", stats.toJson().errors);
    }

    const info = stats.toJson({ all: false, modules: true, chunks: false });

    console.log("\nModule sizes:\n");

    info.modules.forEach((mod) => {
        const name = mod.name ? mod.name.replace(/^.*!/, "") : "<unknown>";
        const sizeKB = (mod.size / 1024).toFixed(2);
        console.log(`${sizeKB.padStart(7)} KiB  ${name}`);
    });

    console.log(`\nTotal modules: ${info.modules.length}`);
    compiler.close(() => {});
});
