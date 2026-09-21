/* eslint-disable no-console -- this is a build script; console IS the output. */
/**
 * Verify the release archive produced by `npm run package:zip`.
 *
 * The archive contents are an allowlist (`files` in package.json, minus
 * `.npmignore`). That is easy to get wrong in a way no build output reveals:
 * a runtime asset left out of the list produces a zip that looks fine and
 * fatals on a real site. This script fails the build instead.
 *
 * Checks:
 *   1. Every plugin-relative asset path referenced by shipped PHP exists.
 *   2. A hardcoded list of must-ship files is present.
 *   3. No dev-only Composer package or build source leaked in.
 *
 * On failure the archive is deleted so a broken artifact cannot be uploaded.
 */

import { existsSync, unlinkSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire( import.meta.url );
const AdmZip = require( 'adm-zip' );

const root = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const zipPath = resolve( root, 'alphalisting.zip' );

if ( ! existsSync( zipPath ) ) {
	console.error( `\nVerification failed: ${ zipPath } does not exist.` );
	process.exit( 1 );
}

const zip = new AdmZip( zipPath );
const entries = zip.getEntries().filter( ( e ) => ! e.isDirectory );
// Strip the `alphalisting/` root folder so paths are plugin-relative.
const files = new Map(
	entries.map( ( e ) => [ e.entryName.replace( /^[^/]+\//, '' ), e ] )
);

const problems = [];

/** Files without which the plugin fatals or silently loses functionality. */
const MUST_SHIP = [
	'alphalisting.php',
	'readme.txt',
	'build/index.js',
	'build/index.asset.php',
	'vendor/autoload.php',
	'vendor/composer/ClassLoader.php',
	'templates/a-z-listing.php',
	'widgets/class-alphalisting-widget.php',
	'scripts/blocks/attributes.json',
	'scripts/alphalisting-widget-admin.js',
	'languages/alphalisting.pot',
];

/** Patterns that must never appear — dev deps, build source, tooling. */
const MUST_NOT_SHIP = [
	[ /^vendor\/(wp-cli|gettext|mck89|eftec)\//, 'dev Composer package' ],
	[
		/^vendor\/symfony\/(finder|deprecation-contracts|polyfill-php80)\//,
		'dev Composer package',
	],
	[ /^vendor\/bin\//, 'Composer binary' ],
	[ /^scripts\/components\//, 'webpack source' ],
	[ /^scripts\/blocks\/.*\.js$/, 'webpack source' ],
	[ /^node_modules\//, 'node_modules' ],
	[ /\.scss$/, 'Sass source' ],
	[ /^composer\.lock$/, 'lock file' ],
	[
		/^(Gruntfile\.js|AGENTS\.md|changelog\.md|package-lock\.json)$/,
		'repo tooling',
	],
	[ /^\.wordpress-org\//, 'WordPress.org assets' ],
];

// --- 1. runtime asset references in shipped PHP -----------------------------

// Quoted plugin-relative paths ending in a known asset extension.
const PATH_RE =
	/['"]\/?((?:[A-Za-z0-9_.-]+\/)+[A-Za-z0-9_.-]+\.(?:json|js|css|php))['"]/g;

const referenced = new Map();
for ( const [ name, entry ] of files ) {
	if ( ! name.endsWith( '.php' ) || name.startsWith( 'vendor/' ) ) {
		continue;
	}
	const code = entry.getData().toString( 'utf8' );
	for ( const match of code.matchAll( PATH_RE ) ) {
		const ref = match[ 1 ];
		// WordPress core paths are resolved against ABSPATH, not the plugin.
		if (
			ref.startsWith( 'wp-admin/' ) ||
			ref.startsWith( 'wp-includes/' )
		) {
			continue;
		}
		if ( ! referenced.has( ref ) ) {
			referenced.set( ref, new Set() );
		}
		referenced.get( ref ).add( name );
	}
}

for ( const [ ref, sources ] of [ ...referenced ].sort() ) {
	if ( ! files.has( ref ) ) {
		problems.push(
			`missing runtime asset: ${ ref }\n    referenced by ${ [
				...sources,
			].join( ', ' ) }`
		);
	}
}

// --- 2. must-ship files -----------------------------------------------------

for ( const name of MUST_SHIP ) {
	if ( ! files.has( name ) ) {
		problems.push( `missing required file: ${ name }` );
	}
}

// --- 3. must-not-ship patterns ----------------------------------------------

for ( const [ pattern, label ] of MUST_NOT_SHIP ) {
	const hits = [ ...files.keys() ].filter( ( n ) => pattern.test( n ) );
	if ( hits.length ) {
		problems.push(
			`${ label } leaked into the package (${ hits.length } file${
				hits.length === 1 ? '' : 's'
			}): ${ hits.slice( 0, 3 ).join( ', ' ) }${
				hits.length > 3 ? ', …' : ''
			}`
		);
	}
}

// --- report -----------------------------------------------------------------

if ( problems.length ) {
	console.error( '\nPackage verification FAILED:\n' );
	problems.forEach( ( p ) => console.error( `  ✗ ${ p }` ) );
	console.error( `\nDeleting ${ zipPath } so it cannot be shipped.` );
	unlinkSync( zipPath );
	console.error(
		'Fix the `files` allowlist in package.json (and check .npmignore, whose\n' +
			'rules override it), then run `npm run package` again.\n'
	);
	process.exit( 1 );
}

const bytes = entries.reduce( ( sum, e ) => sum + e.header.size, 0 );
console.log(
	`\nPackage verified: ${ files.size } files, ` +
		`${ ( bytes / 1024 ).toFixed( 0 ) } KB uncompressed.\n` +
		`  ${ referenced.size } runtime asset references resolved\n` +
		`  ${ MUST_SHIP.length } required files present\n` +
		`  no dev dependencies or build source leaked\n`
);
