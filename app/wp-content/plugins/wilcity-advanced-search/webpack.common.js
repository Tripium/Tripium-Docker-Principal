// webpack.config.js
const path = require("path");
const VueLoaderPlugin = require("vue-loader/lib/plugin");
require("dotenv").config({ path: "./.env" });
const ASSET_URL = process.env.ASSET_URL;
// console.log(ASSET_URL);
const chunkFilename =
  process.env.MODE === "dev" ? "[name].[id].js" : "[name].[id].js";

module.exports = {
  watch: true,
  // node: {
  //     fs: 'empty'
  // },
  entry: {
    "listing-settings": "./admin/source/dev/listing-settings.js"
  },
  output: {
    filename: "[name].js",
    chunkFilename: chunkFilename,
    path: path.resolve(__dirname, "admin/source/js"),
    publicPath: ASSET_URL
  },
  resolve: {
    alias: {
      vue$: "vue/dist/vue.esm.js"
    },
    extensions: ["*", ".js", ".vue", ".json"]
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: "vue-loader"
      },
      // this will apply to both plain `.js` files
      // AND `<script>` blocks in `.vue` files
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: "babel-loader"
      },
      // this will apply to both plain `.css` files
      // AND `<style>` blocks in `.vue` files
      {
        test: /\.css$/,
        use: ["vue-style-loader", "css-loader"]
      }
    ]
  },
  plugins: [
    // make sure to include the plugin for the magic
    new VueLoaderPlugin()
  ],
  optimization: {
    splitChunks: {
      chunks: "async",
      cacheGroups: {
        // Cache Group
        vendors: {
          test: /[\/]node_modules[\/]/,
          priority: -10
        },
        default: {
          minChunks: 2,
          priority: -20,
          reuseExistingChunk: true
        }
      }
    }
  }
};
