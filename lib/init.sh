#!/bin/bash


# create folder for log files
mkdir /var/www/html/lib/logs;




#############################
######### CRON JOBS #########
#############################

# stop cron service
service cron stop

# echo new cron-jobs and environment variables into temp file

# first add the environment variables so that they will be available during cron jobs
echo "LDAP_SELFSERVICE_USER=${LDAP_SELFSERVICE_USER}" >> cronjobs.file
echo "LDAP_SELFSERVICE_PASSWORD=${LDAP_SELFSERVICE_PASSWORD}" >> cronjobs.file
echo "LDAP_SELFSERVICE_SERVER=${LDAP_SELFSERVICE_SERVER}" >> cronjobs.file
echo "TARGET_BRANCH=${TARGET_BRANCH}" >> cronjobs.file
echo "PUS_PATH=${PUS_PATH}" >> cronjobs.file

# !!! cd in file directory to prevent problems with relative paths
# run password reminder script everyday at 6 am.
echo "0 6 * * * cd /var/www/html/lib/password-reminder; /usr/local/bin/php /var/www/html/lib/password-reminder/password-reminder-cron.php >> /var/www/html/lib/logs/cron.log 2>&1" >> cronjobs.file
# run password cleanup script every 30 minutes
echo "*/30 * * * * cd /var/www/html/lib/pus; /bin/bash /var/www/html/lib/pus/passwordCleanupCron.sh >> /var/www/html/lib/logs/cron.log 2>&1" >> cronjobs.file

# install new cron file and remove tmp-file
crontab cronjobs.file
rm cronjobs.file

# start cron service again
service cron start


# chown all files to www-data in /var/www/html/* except for .htaccess and .htpasswd files
find /var/www/html ! -name ".htaccess" ! -name ".htpasswd" -exec chown www-data:www-data {} \;

# start other services (see https://hub.docker.com/_/php -> Dockerfile of Docker-Image)
apache2-foreground