var gulp        = require('gulp'),
    sass        = require('gulp-sass')(require('sass')),
    prefix      = require('gulp-autoprefixer'),
    rename      = require('gulp-rename'),
    cleanCSS    = require('gulp-clean-css');
    // livereload = require('gulp-livereload'),
    // browserify  = require('browserify'),
    // source      = require('vinyl-source-stream'),
    // buffer      = require('vinyl-buffer'),
    // concat      = require('gulp-concat'),
    // sourcemaps  = require('gulp-sourcemaps')
    // cp          = require('child_process'),
    // browserSync = require('browser-sync').create(),

// gulp.task('imagemin', function () {
//     return gulp.src('./images/*')
//         .pipe(imagemin({
//             progressive: true,
//             svgoPlugins: [{removeViewBox: false}],
//             use: [pngquant()]
//         }))
//         .pipe(gulp.dest('./images'));
// });

/**
 * @task sass
 * Compile files from scss
 */
gulp.task('sass', function(done) {
return gulp.src('./scss/**/*.scss')
    .pipe(sass())
    .pipe(prefix(['last 3 versions', '> 1%', 'ie 10'], { cascade: true }))
    .pipe(cleanCSS({compatibility: 'ie10'}))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('dist'))
    // .pipe(livereload())
    .on('close', done);
});

/**
 * @task old_sass
 * Compile files from scss
 */
 gulp.task('old_sass', function(done) {
    return gulp.src('./old_scss/**/*.*')
        .pipe(sass())
        .pipe(prefix(['last 3 versions', '> 1%', 'ie 10'], { cascade: true }))
        .pipe(cleanCSS({compatibility: 'ie10'}))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('dist'))
        // .pipe(livereload())
        .on('close', done);
    });

// gulp.task('scripts', function(done) {
//   return gulp.src('./lib/*.js')
//     .pipe(uglify('main.js'))
//     .pipe(gulp.dest('dist'))
//     .on('close', done);
// });

/**
 * @task build
 * Compile sass / js
 */
 gulp.task('build', gulp.series('sass', 'old_sass'));


gulp.task('watch', function(){
    // livereload.listen();
    gulp.watch('./scss/**/*.scss', gulp.series('sass'));
    gulp.watch('./old_scss/**/*.s[ac]ss', gulp.series('old_sass'));
    // gulp.watch('./lib/*.js', gulp.series('scripts'));
});

/**
 * Default task, running just `gulp` will
 * Watch scss/js files for changes & recompile
 * Clear cache when Drupal related files are changed
 */
 gulp.task('default', gulp.series('build', 'watch'));