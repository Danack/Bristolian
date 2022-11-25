
FROM node:19.1.0-bullseye-slim

WORKDIR /var/app/app

RUN apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y git

# This is done in the installer
RUN npm update

# CMD tail -f /var/app/containers/js_builder/README_npm.md

CMD npm run js:build:dev:watch