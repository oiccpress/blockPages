// Weirdness inside of PKP-lib needs this :(
// (especially around using non-standard Function.caller code)
// import { Webpack5RemoveUseStrictPlugin } from "webpack5-remove-use-strict-plugin";
const USE_STRICT_REGEX = /(\'|\")use\s+strict(\'|\")\;?/gm;

class Webpack5RemoveUseStrictPlugin {
    constructor() {
        this.pluginName = "Webpack5RemoveUseStrictPlugin";
    }

    apply(compiler) {
        compiler.hooks.make.tap(this.pluginName, (compilation) => {
            const hooks =
                compiler.webpack.javascript.JavascriptModulesPlugin.getCompilationHooks(
                    compilation
                );

            const processSource = (source, renderContext) => {
                source._children.forEach((child, idx) => {
                    if (typeof child === "string" && child.match(USE_STRICT_REGEX)) {
                        source._children[idx] = child.replace(USE_STRICT_REGEX, "");
                    } else if(child._source && child._source._children) {
                      processSource(child._source, renderContext);
                    }
                });

                return source;
            };

            hooks.renderMain.tap(this.pluginName, processSource);
        });
    }
}

const path = require('path');

module.exports = {
  entry: './src/index.js',
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'js'),
  },
  plugins: [
      new Webpack5RemoveUseStrictPlugin()
  ],
  mode: "development"
};
