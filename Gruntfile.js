const sass = require('sass');

module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
                implementation: sass,
                outputStyle: 'compressed'
            },
            dist: {
                files: {
                    'css/resizefly-admin.css': 'css/src/inc.scss'
                }
            }
        },

        uglify: {
            dev: {
                options: {
                    mangle: false,
                    sourceMap: true,
                    banner: '(function ($) {',
                    footer: '\n})(jQuery);',
                    preserveComments: 'some'
                },
                files: {
                    'js/resizefly-admin.min.js': 'js/src/settings/*.js',
                    'js/resizefly-purge-single.min.js': 'js/src/purge-single.js'
                }
            },
            dist: {
                options: {
                    mangle: true,
                    preserveComments: 'some',
                    banner: '(function ($) {',
                    footer: '\n})(jQuery);',
                    compress: {
                        drop_console: true
                    },
                    sourceMap: false
                },
                files: {
                    'js/resizefly-admin.min.js': 'js/src/settings/*.js',
                    'js/resizefly-purge-single.min.js': 'js/src/purge-single.js'
                }
            },
            debug: {
                options: {
                    mangle: false,
                    sourceMap: false,
                    compress: {
                        booleans: false,
                        comparisons: false,
                        evaluate: false,
                        inline: false,
                        keep_fnames: true,
                        loops: false,
                        sequences: false,
                        typeofs: false,
                        drop_console: true,
                        hoist_vars: true,
                        hoist_props: true,
                        hoist_funs: true,
                        passes: 2,
                        warnings: true,
                        reduce_vars: false,
                        toplevel: true
                    },
                    beautify: {
                        braces: true,
                        indent_start: 4
                    },
                    banner: '(function ($) {',
                    footer: '\n})(jQuery);',
                    preserveComments: 'all'
                },
                files: {
                    'js/resizefly-admin.js': 'js/src/settings/*.js',
                    'js/resizefly-purge-single.js': 'js/src/purge-single.js'
                }
            }
        },

        pot: {
            admin: {
                options: {
                    text_domain: 'resizefly',
                    dest: 'languages/',
                    language: 'PHP',
                    encoding: 'utf-8',
                    keywords: [ //WordPress localisation functions
                        '__:1',
                        '_e:1',
                        '_x:1,2c',
                        'esc_html__:1',
                        'esc_html_e:1',
                        'esc_html_x:1,2c',
                        'esc_attr__:1',
                        'esc_attr_e:1',
                        'esc_attr_x:1,2c',
                        '_ex:1,2c',
                        '_n:1,2',
                        '_nx:1,2,4c',
                        '_n_noop:1,2',
                        '_nx_noop:1,2,3c'
                    ],
                    msgid_bugs_address: 'hi@resizefly.com'
                },
                files: [{
                    expand: true,
                    src: ['**/*.php', '!node_modules/**', '!vendor/**']
                }]
            }
        },

        'string-replace': {
            php: {
                files: [{
                    expand: true,
                    cwd: 'src/Common',
                    src: ['**/*.php'],
                    dest: 'src/Common',
                    filter: 'isFile'
                }],
                options: {
                    replacements: [
                        {
                            pattern: /namespace\s+?([^;]+);/,
                            replacement: 'namespace Alpipego\\Resizefly\\Common\\$1;'
                        },
                        {
                            pattern: /use\s+?([^;]+)(?=\\)([^;]+);/g,
                            replacement: 'use Alpipego\\Resizefly\\Common\\$1$2;'
                        }
                    ]
                }
            }
        },

        copy: {
            php: {
                files: [
                    {
                        expand: true,
                        cwd: 'vendor/pimple/pimple/src/Pimple',
                        src: ['**', '!**/Tests/**'],
                        dest: 'src/Common/Pimple'
                    },
                    {
                        expand: true,
                        cwd: 'vendor/psr/container/src',
                        src: '**',
                        dest: 'src/Common/Psr/Container'
                    },
                    {
                        src: 'vendor/composer/ClassLoader.php',
                        dest: 'src/Common/Composer/Autoload/ClassLoader.php'
                    }
                ]
            }
        },

        wp_readme_to_markdown: {
            dist: {
                files: {
                    'README.md': 'readme.txt'
                }
            }
        },

        watch: {
            js: {
                files: ['Gruntfile.js', 'js/src/**/*.js'],
                tasks: ['uglify:dev', 'uglify:debug']
            },
            sass: {
                files: ['Gruntfile.js', 'css/src/**/*.scss'],
                tasks: 'sass'
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-pot');
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-wp-readme-to-markdown');

    grunt.registerTask('third-party', ['copy:php', 'string-replace:php']);
    grunt.registerTask('default', ['uglify:debug', 'uglify:dev', 'sass', 'watch']);
    grunt.registerTask('build', ['pot', 'wp_readme_to_markdown', 'uglify:dist', 'uglify:debug', 'sass']);
};
