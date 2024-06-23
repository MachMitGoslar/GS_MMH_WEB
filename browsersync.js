const browserSync = require( 'browser-sync' );

const sync = browserSync.create();

sync.init( {
	watch: true,
	proxy: "localhost:8000",
	files: [
        "assets",
        "content",
        "site/snippets",
        "site/templates",
        "site/blueprints",
        "site/languages"
    ],
	ignore: [
        "**/.lock",
        "**/.DS_Store"
    ],
	open: 'local'
} );