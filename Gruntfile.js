module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		prompt: {

			install: {
				options: {
					questions: [
						{ config: 'dbHost', type: 'input', message: 'DB host', default: '127.0.0.1' },
						{ config: 'dbUser', type: 'input', message: 'DB username', default: 'root' },
						{ config: 'dbPass', type: 'password', message: 'DB password', default: '' },
						{ config: 'dbName', type: 'input', message: 'DB database', default: /\w*$/.exec(__dirname)  }
					]
				}
			},

		},

		exec: {

			createDB: {
				cmd: function() {
					return 'mysql' +
						' -h ' + this.config('dbHost') +
						' -u ' + this.config('dbUser') +
						(this.config('dbPass') ? ('-p' + this.config('dbPass')) : '') +
						' -e "CREATE DATABASE ' + this.config('dbName') + '"';
				}
			}

		}

	});

	// Load plugins
	grunt.loadNpmTasks('grunt-prompt');
	grunt.loadNpmTasks('grunt-exec');

	// Register tasks
	grunt.registerTask('default', ['']);
	grunt.registerTask('install', 'Project semi-automatic installation', ['prompt:install', 'exec:createDB']);

};