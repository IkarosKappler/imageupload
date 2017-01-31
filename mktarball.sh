#!/bin/sh
#
# Do not include the 'public' directory as it is the upload (!) dir.

DATE=$(date +"%Y%m%d%H%M%S")
tar -czf imageupload_$DATE.tar.gz README.md ajax config.inc.php css database file.class.php img index.html install.txt tests js lib mktarball.sh

