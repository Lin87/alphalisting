module.exports = function( grunt ) {

	'use strict';

	const sass = require( 'sass' );

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'alphalisting',
			},
			update_all_domains: {
				options: {
					updateDomains: true
				},
				src: [ '*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!test/**/*', '!tests/**/*', '!build/**/*', '!vendor/**/*' ]
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				},
				options: {
					screenshot_url: '.wordpress-org/{screenshot}.png',
					pre_convert: function( readme ) {
						readme = readme.replace( new RegExp('^`$[\n\r]+([^`]*)[\n\r]+^`$','gm'), function( codeblock, codeblockContents ) {
							const blockStartEnd = '```';
							let lines = codeblockContents.split('\n');
							if ( String( lines[0] ).startsWith('<?php') ) {
								return `${blockStartEnd}php\n${lines.join('\n')}\n${blockStartEnd}`;
							}
							// Without this, non-PHP code blocks return undefined and
							// String.replace() substitutes the literal text "undefined".
							return codeblock;
						});
						return readme;
					},
					post_convert: function( readme ) {
						readme = readme.replace( /^\*\*([^*\s][^*]*)\*\*$/gm, function( a, b ) {
							return `#### ${b} ####`;
						});
						readme = readme.replace( /^\*([^*\s][^*]*)\*$/gm, function( a, b ) {
							return `##### ${b} #####`;
						});
						return readme;
					}
				}
			},
		},

		sass: {
			options: {
				sourceMap: false,
				implementation: sass
			},
			dist: {
				files: {
					'css/alphalisting-default.css': 'css/alphalisting-default.scss',
					'css/alphalisting-customize.css': 'css/alphalisting-customize.scss'
				}
			}
		},
		
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.registerTask( 'default', ['build'] );
	grunt.registerTask( 'build', [ 'i18n','readme','sass' ] );
	// Note: makepot is deliberately NOT part of this task. grunt-wp-i18n scans PHP
	// only and would silently drop every string from the block editor JS. Regenerate
	// the POT with `composer makepot` (WP-CLI, scans PHP + JS) instead.
	grunt.registerTask( 'i18n', ['addtextdomain'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );

	grunt.util.linefeed = '\n';

};
