module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
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
                    'js/resizefly-admin.min.js': 'js/src/*.js'
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
                    }
                },
                files: {
                    'js/resizefly-admin.min.js': 'js/src/*.js'
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
                    src: ['**/*.php', '!node_modules/**']
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

        watch: {
            js: {
                files: ['Gruntfile.js', 'js/src/**/*.js'],
                tasks: 'uglify:dev'
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

    grunt.registerTask('third-party', ['copy:php', 'string-replace:php']);
    grunt.registerTask('default', ['uglify:dev', 'sass', 'watch']);
    grunt.registerTask('build', ['pot', 'uglify:dist', 'sass']);
};
