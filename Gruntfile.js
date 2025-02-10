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
				src: [ '*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!tests/**/*', '!build/**/*', '!vendor/**/*' ]
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

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'tests/*', 'vendor/*' ],
					mainFile: 'alphalisting.php',
					potFilename: 'alphalisting.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},

		// babel: {
		// 	options: {
		// 		sourceMap: true,
		// 		plugins: [['transform-react-jsx', {"pragma": "wp.element.createElement"}]],
		// 		presets: ['env', 'react']
		// 	},
		// 	jsx: {
		// 		files: [{
		// 			expand: true,
		// 			src: ['**/*.jsx'],
		// 			ext: '.js'
		// 		}]
		// 	}
		// },

		sass: {
			options: {
				sourceMap: true,
				implementation: sass
			},
			dist: {
				files: {
					'css/alphalisting-default.css': 'css/alphalisting-default.scss',
					'css/alphalisting-customize.css': 'css/alphalisting-customize.scss'
				}
			}
		},

		copy: {
			main: {
				files: [

                    // Copy the autoload.php file
                    {src: 'vendor/autoload.php', dest: 'build/vendor/autoload.php'},

                    // Copy the composer directory
                    {expand: true, cwd: 'vendor/composer', src: ['**'], dest: 'build/vendor/composer'},

                    // Copy the symfony directory
                    {expand: true, cwd: 'vendor/symfony', src: ['**'], dest: 'build/vendor/symfony'}
				],
			}
		},
		
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.registerTask( 'default', ['build'] );
	grunt.registerTask( 'build', [ 'i18n','readme','sass' ] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );
	grunt.registerTask( 'default', ['copy'] );

	grunt.util.linefeed = '\n';

};