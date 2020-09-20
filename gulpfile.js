var gulp = require('gulp');
var wpPot = require('gulp-wp-pot');
var minify = require('gulp-minify');

gulp.task('makepot', function() {
    return gulp.src('*.php')
        .pipe(wpPot( {
            domain: 'manually-reduce-stock-for-woocommerce',
            package: 'manually-reduce-stock-for-woocommerce'
        } ))
        .pipe(gulp.dest('i18n/languages/manually-reduce-stock-for-woocommerce.pot'));
});

gulp.task('compress', function() {
  return gulp.src('assets/js/admin/*.js')
    .pipe(minify({
        ext:{
            min:'.min.js'
        },
        ignoreFiles: ['*.min.js']
    }))
    .pipe(gulp.dest('assets/js/admin'));
});
