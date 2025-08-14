# Use official PHP image
FROM php:8.2-cli

# Copy all project files
COPY . /var/www/html

WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start PHP's built-in server
CMD ["php", "-S", "0.0.0.0:80"]
