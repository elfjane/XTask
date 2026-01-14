FROM node:22-alpine

WORKDIR /app

# Install dependencies first (for better caching)
COPY package*.json ./
RUN npm install

# Copy project files
COPY . .

# Build the application
# RUN npm run build

EXPOSE 3111

# For development
CMD ["npm", "run", "dev", "--", "-o"]
