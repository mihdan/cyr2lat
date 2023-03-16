const glob = require( 'glob' );
const path = require( 'path' );
const CssMinimizerWebpackPlugin = require( 'css-minimizer-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const WebpackRemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

const webPackModule = ( production = true ) => {
	return {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				options: {
					presets: [ '@babel/preset-env' ],
				},
			},
			{
				test: /\.s?css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: path.join( __dirname, 'assets' ),
						},
					},
					{
						loader: 'css-loader',
						options: {
							sourceMap: ! production,
							url: false,
						},
					},
				],
			},
		],
	};
};

const lookup = ( lookupPath ) => {
	const ext = path.extname( lookupPath );
	const entries = {};

	glob.sync( lookupPath ).map( ( filePath ) => {
		if ( filePath.includes( '.min' + ext ) ) {
			return filePath;
		}

		let filename = path.basename( filePath, ext );

		if ( 'app' === filename ) {
			filename = 'apps/' + path.basename( path.dirname( filePath ) );
		}

		entries[ filename ] = path.resolve( filePath );

		return filePath;
	} );

	return entries;
};

const cyr2lat = ( env ) => {
	/**
	 * @param env.production
	 */
	const production = env.production ? env.production : false;
	const cssEntries = lookup( './assets/css/*.css' );
	const jsEntries = lookup( './assets/js/*.js' );
	const appEntries = lookup( './src/js/**/app.js' );

	const entries = {
		...cssEntries,
		...jsEntries,
		...appEntries,
	};

	return {
		devtool: production ? false : 'eval-source-map',
		entry: entries,
		module: webPackModule( production ),
		output: {
			path: path.join( __dirname, 'assets' ),
			filename: ( pathData ) => {
				return pathData.chunk.name.includes( 'apps/' )
					? 'js/[name].js'
					: 'js/[name].min.js';
			},
		},
		plugins: [
			new WebpackRemoveEmptyScriptsPlugin(),
			new MiniCssExtractPlugin( {
				filename: 'css/[name].min.css',
			} ),
		],
		optimization: {
			minimizer: [
				new TerserPlugin( {
					extractComments: false,
				} ),
				new CssMinimizerWebpackPlugin(),
			],
		},
	};
};

module.exports = [ cyr2lat ];
