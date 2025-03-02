module.exports = function (grunt) {
	grunt.initConfig({
		sass: {
			dist: {
				options: {
					style: "compressed",
				},
				files: [
					{
						expand: true,
						cwd: "scss",
						src: ["**/*.scss"],
						dest: "public/css",
						ext: ".css",
					},
				],
			},
		},
		uglify: {
			options: {
				compress: true,
				mangle: true,
			},
			dist: {
				files: [
					{
						expand: true,
						cwd: "js",
						src: ["**/*.js"],
						dest: "public/js",
						ext: ".min.js",
					},
				],
			},
		},
		copy: {
      fonts: {
        expand: true,
        cwd: 'node_modules/bootstrap-icons/font/fonts/',
        src: '*',
        dest: 'public/css/fonts/'
      }
    },
		watch: {
			scss: {
				files: ["scss/**/*.scss"],
				tasks: ["sass"],
			},
			js: {
				files: ["js/**/*.js"],
				tasks: ["uglify"],
			},
		},
	});

	grunt.loadNpmTasks("grunt-contrib-sass");
	grunt.loadNpmTasks("grunt-contrib-uglify");
  grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-contrib-watch");

	grunt.registerTask("default", ["sass", "uglify", "copy", "watch"]);
};
