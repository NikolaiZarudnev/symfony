FROM node:18.17.0
LABEL authors="nikolay"

ENTRYPOINT ["top", "-b"]

COPY init.sh /var/www/ng-mysymfony/docker/nodejs/

WORKDIR /var/www/ng-mysymfony

CMD ["chmod", "755", "./docker/nodejs/init.sh"]