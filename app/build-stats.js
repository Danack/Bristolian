const webpack = require("webpack");
const config = require("./webpack.config.js");

const compiler = webpack(config);

compiler.run((err, stats) => {
    if (err) {
        console.error(err);
        process.exit(1);
    }
    require("fs").writeFileSync("stats.json", JSON.stringify(stats.toJson({
        all: true, // include everything
    }), null, 2));
    console.log("stats.json written");
    compiler.close(() => {});
});