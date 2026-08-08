
FROM node:19.1.0-bullseye-slim

WORKDIR /var/app/codeview

RUN apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y git


# CMD tail -f /var/app/containers/js_builder/README_npm.md

CMD ["sh", "/var/app/containers/codeview_js_builder/entrypoint.sh"]


# CMD npm run js:build:dev:watch