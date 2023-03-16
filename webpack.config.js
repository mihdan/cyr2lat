const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const webPackModule = (production = true) => {
	return {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				options: {
					presets: ['@babel/preset-env'],
				},
			},
			{
				test: /\.s?css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: path.join(__dirname, 'assets'),
						},
					},
					{
						loader: 'css-loader',
						options: {
							sourceMap: !production,
							url: false,
						},
					},
				],
			},
		],
	};
};

const tables = (env) => {
	/**
	 * @param  env.production
	 */
	const production = env.production ? env.production : false;

	return {
		devtool: production ? false : 'eval-source-map',
		entry: ['./src/js/tables/app.js'],
		module: webPackModule(production),
		output: {
			path: path.join(__dirname, 'assets', 'js'),
			filename: path.join('tables', 'app.js'),
		},
		plugins: [
			new MiniCssExtractPlugin({
				filename: 'css/[name].min.css',
			}),
		],
	};
};

const converter = (env) => {
	/**
	 * @param  env.production
	 */
	const production = env.production ? env.production : false;

	return {
		devtool: production ? false : 'eval-source-map',
		entry: ['./src/js/converter/app.js'],
		module: webPackModule(production),
		output: {
			path: path.join(__dirname, 'assets', 'js'),
			filename: path.join('converter', 'app.js'),
		},
		plugins: [
			new MiniCssExtractPlugin({
				filename: 'css/[name].min.css',
			}),
		],
	};
};

module.exports = [tables, converter];
