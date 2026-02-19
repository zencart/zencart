#
# Copy (select) /laravel/vendor/ files to /vendor
# for use with the test suite
#
# This allows us to override composer's PHP-version and package-compatibility limitations
#
cd laravel
FILE=app/Models/Coupon.php
if [ ! -f "$FILE" ]; then
    echo "ERROR: Cannot find the Laravel directory. Please run from root of the project directory tree."
    exit 2
fi
cd vendor
cp -Rf illuminate ../../vendor
cp -Rf symfony ../../vendor
if [ -d "nunomaduro" ]; then
    cp -Rf nunomaduro ../../vendor
fi
