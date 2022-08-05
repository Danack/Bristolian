
FROM node:14.4.0-stretch-slim

WORKDIR /var/app/app

RUN apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y git

RUN npm update

CMD tail -f /var/app/containers/js_dev_builder/README_npm.md

# CMD npm run js:build:dev:watch