FROM php:7.3-apache-buster

RUN apt-get update && \
    apt-get install libldap2-dev libssl-dev openssl git cron curl unzip -y && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-install ldap

# install YAML module for perl
RUN cpan install YAML

COPY . /var/www/html/


#############################
####### COMPOSER SETUP ######
#############################
RUN cd ~ && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php && \
    cd /var/www/html && \
    composer install


# make all sh-files executable
RUN find /var/www/html -type f -iname "*.sh" -exec chmod +x {} \;

# environment variables
ARG LDAP_SELFSERVICE_USER
ENV LDAP_SELFSERVICE_USER=$LDAP_SELFSERVICE_USER
ARG LDAP_SELFSERVICE_PASSWORD
ENV LDAP_SELFSERVICE_PASSWORD=$LDAP_SELFSERVICE_PASSWORD
ARG LDAP_SELFSERVICE_SERVER
ENV LDAP_SELFSERVICE_SERVER=$LDAP_SELFSERVICE_SERVER
ARG SAMBA_SELFSERVICE_USER
ENV SAMBA_SELFSERVICE_USER=$SAMBA_SELFSERVICE_USER
ARG SAMBA_SELFSERVICE_PASSWORD
ENV SAMBA_SELFSERVICE_PASSWORD=$SAMBA_SELFSERVICE_PASSWORD
ARG SAMBA_SELFSERVICE_SERVER
ENV SAMBA_SELFSERVICE_SERVER=$SAMBA_SELFSERVICE_SERVER
ARG TARGET_BRANCH
ENV TARGET_BRANCH=$TARGET_BRANCH
ENV PUS_PATH="/var/www/html/lib/pus"

# Timezone settings for System and PHP
ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "' > /usr/local/etc/php/conf.d/tzone.ini && \
    printf $TZ >> /usr/local/etc/php/conf.d/tzone.ini && \
    printf '"\n' >> /usr/local/etc/php/conf.d/tzone.ini

# run init script when container starts (will install cron jobs and start apache-foreground)
ENTRYPOINT /bin/bash /var/www/html/lib/init.sh
