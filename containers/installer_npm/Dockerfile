
FROM node:19.1.0-bullseye-slim

WORKDIR /var/app/app

RUN apt-get update -qq




CMD sh /var/app/containers/installer_npm/entrypoint.sh


# CMD npm run js:build:dev:watch