
var gulp = require('gulp');
var stylus = require('gulp-stylus');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var pump = require('pump');

function minifyCss(src, dest) {
	return gulp.src(src)
		.pipe(stylus())
		.on('error', function(err){
			console.log(err);
			this.emit('end');
		})
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest(dest));
}

gulp.task('frontStyle', function () {
	return minifyCss('resources/assets/css/style.styl', 'public/css');
});

gulp.task('adminStyle', function () {
	return minifyCss('resources/assets/gtcms/css/style.styl', 'public/gtcms/css');
});

gulp.task('adminVendorsCss', function (cb) {
	pump([
		gulp.src([
			'public/components/bootstrap/dist/css/bootstrap.min.css',
			'public/components/selectize/dist/css/selectize.default.css',
			'public/components/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.css',
			'public/components/blueimp-file-upload/css/jquery.fileupload.css',

			'resources/assets/gtcms/css/vendors/metis-menu.min.css',
			'resources/assets/gtcms/css/vendors/theme.css',
			'resources/assets/gtcms/css/vendors/gtcms-datepicker.css'
		]),
		concat('vendors.css'),
		cssmin(),
		rename({suffix: '.min'}),
		gulp.dest('public/gtcms/css')
	],
	cb);
});

gulp.task('minifyAdminVendorsJs', function(cb) {
	pump([
		gulp.src([
			'public/components/jquery/dist/jquery.min.js',
			'public/components/history.js/scripts/bundled/html4+html5/jquery.history.js',
			'public/components/jquery-ui/ui/minified/core.min.js',
			'public/components/jquery-ui/ui/minified/widget.min.js',
			'public/components/jquery-ui/ui/minified/mouse.min.js',
			'public/components/jquery-ui/ui/minified/sortable.min.js',
			'public/components/jquery-ui/ui/minified/draggable.min.js',
			'public/components/jquery-ui/ui/minified/slider.min.js',
			'public/components/jquery-ui/ui/minified/datepicker.min.js',
			'public/components/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js',
			'public/components/spin.js/spin.min.js',
			'public/components/spin.js/jquery.spin.js',
			'public/components/autosize/dist/autosize.min.js',
			'public/components/blueimp-load-image/js/load-image.js',
			'public/components/blueimp-load-image/js/load-image-scale.js',
			'public/components/blueimp-load-image/js/load-image-meta.js',
			'public/components/blueimp-load-image/js/load-image-fetch.js',
			'public/components/blueimp-load-image/js/load-image-exif.js',
			'public/components/blueimp-load-image/js/load-image-exif-map.js',
			'public/components/blueimp-load-image/js/load-image-orientation.js',
			'public/components/blueimp-canvas-to-blob/js/canvas-to-blob.js',
			'public/components/blueimp-file-upload/js/jquery.iframe-transport.js',
			'public/components/blueimp-file-upload/js/jquery.fileupload.js',
			'public/components/blueimp-file-upload/js/jquery.fileupload-process.js',
			'public/components/blueimp-file-upload/js/jquery.fileupload-image.js',
			'public/components/bootstrap/dist/js/bootstrap.min.js',

			'resources/assets/gtcms/js/vendors/metis-menu.min.js',
			'resources/assets/gtcms/js/vendors/selectize.js',
			'resources/assets/gtcms/js/vendors/template.js',
			'resources/assets/gtcms/js/vendors/jquery.ui.touch-punch.min.js',
			'resources/assets/gtcms/js/vendors/jquery.numeric.min.js'
		]),
		concat('vendors.min.js', {newLine: ';'}),
		uglify(),
		gulp.dest('public/gtcms/js')
	],
	cb);
});

gulp.task('minifyAdminJs', function(cb) {
	pump([
		gulp.src([
			'resources/assets/gtcms/js/gtcmspremium.js',
			'resources/assets/gtcms/js/admin.js'
		]),
		concat('admin.min.js', {newLine: ';'}),
		uglify(),
		gulp.dest('public/gtcms/js')
	],
	cb);
});

gulp.task('watch', function() {
	gulp.watch('public/css/style.styl', ['frontStyle']);
	gulp.watch('resources/assets/gtcms/css/style.styl', ['adminStyle']);
	gulp.watch([
		'resources/assets/gtcms/js/admin.js',
		'resources/assets/gtcms/js/gtcmspremium.js'
	], ['minifyAdminJs']);
});

gulp.task('default', [
	'frontStyle',
	'adminStyle',
	'adminVendorsCss',
	'minifyAdminVendorsJs',
	'minifyAdminJs'
]);