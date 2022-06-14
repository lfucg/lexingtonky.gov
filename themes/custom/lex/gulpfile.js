var gulp = require('gulp');
var livereload = require('gulp-livereload')
var uglify = require('gulp-uglify');
var sass = require('gulp-sass');
var autoprefixer = require('autoprefixer');
var postcss = require('gulp-postcss');
var sourcemaps = require('gulp-sourcemaps');
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
sass.compiler = require('node-sass');

var autoprefixerOptions = {
  overrideBrowserslist: ['> 0%', 'IE 9'],
  cascade: false,
};

gulp.task('imagemin', function () {
    return gulp.src('./images/*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()]
        }))
        .pipe(gulp.dest('./images'));
});
gulp.task('sass', function () {
    return gulp.src('./scss/**/*.scss')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(postcss([autoprefixer(autoprefixerOptions)]))
        .pipe(gulp.dest('./css'));
});
gulp.task('old_sass', function () {
    return gulp.src('./old_scss/**/*.*')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(postcss([autoprefixer(autoprefixerOptions)]))
        .pipe(gulp.dest('./css'));
});

gulp.task('uglify', function() {
  gulp.src('./lib/*.js')
    .pipe(uglify('main.js'))
    .pipe(gulp.dest('./js'))
});

/**
* @task refresh
* Refresh browser
*/
gulp.task('refresh', function(done) {
    livereload.reload();
   done();
 });

/**
 * @task build
 * Compile sass / js
 */
 gulp.task('build', gulp.series('imagemin', 'sass', 'old_sass', 'refresh'));


gulp.task('watch', function(){
    livereload.listen();
    gulp.watch('./scss/**/*.scss', gulp.series('sass'));
    gulp.watch('./old_scss/**/*.sass', gulp.series('old_sass'));
    gulp.watch('./old_scss/**/*.scss', gulp.series('old_sass'));
    gulp.watch('./lib/*.js', gulp.series('uglify'));
    gulp.watch(['/css/style.css', './**/*.twig', './js/*.js'], function (files){
        livereload.changed(files)
    });
});
