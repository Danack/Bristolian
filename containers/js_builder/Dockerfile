FROM node:20-bookworm-slim

WORKDIR /var/app/app

RUN apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    git \
    # Playwright Chromium system dependencies
    libnss3 \
    libnspr4 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libdbus-1-3 \
    libxkbcommon0 \
    libatspi2.0-0 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libpango-1.0-0 \
    libcairo2 \
    fonts-liberation \
    && rm -rf /var/lib/apt/lists/*

# Pre-install Playwright Chromium into the image cache (package version matched at runtime via node_modules).
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
RUN npx --yes playwright@1.49.1 install chromium

CMD ["sh", "/var/app/containers/js_builder/entrypoint.sh"]
