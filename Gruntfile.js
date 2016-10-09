module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        uglify: {
            dev: {
                options: {
                    mangle: false,
                    sourceMap: true

                },
                files: [
                    {
                        expand: true,
                        cwd: 'js/src',
                        src: ['**/*.js'],
                        dest: 'js/',
                        ext: '.min.js'
                    }
                ]
            },
            dist: {
                options: {
                    mangle: true,
                    preserveComments: 'some',
                    compress: {
                        drop_console: true
                    }
                },
                files: [
                    {
                        expand: true,
                        cwd: 'js/src',
                        src: ['**/*.js'],
                        dest: 'js/',
                        ext: '.min.js'
                    }
                ]
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

        watch: {
            js: {
                files: ['Gruntfile.js', 'js/src/**/*.js'],
                tasks: ['uglify:dev']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-pot');

    grunt.registerTask('default', ['uglify:dev', 'watch']);
    grunt.registerTask('build', ['pot', 'uglify:dist']);
};
