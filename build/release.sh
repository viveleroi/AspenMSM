# THIS SCRIPT WAS DESIGNED TO PREPARE AspenMSM FOR A PRODUCTION RELEASE

# remove any existing exports
rm -rf trunk

# checkout the latest code from trunk
svn co http://svn.trellisdevelopment.com/aspenmsm/trunk
cd trunk

# get the svn revision number and create a RELEASE file
svnvers=`svnversion .`

# add in revision to app.default.config.php
sed -e "s/application_build'] = ''/application_build'] = '$svnvers'/g" app.default.config.php > adc-new.php
mv adc-new.php app.default.config.php

#remove tests dir
rm -rf tests

#remove build dir
rm -rf build

# remove all .svn directories
find . -name .svn -exec rm -rf {} \;

# make tarball
cd ..
mv trunk AspenMSM
tar czvf AspenMSM-$svnvers.tgz AspenMSM

echo "RELEASE BUILD COMPLETE"