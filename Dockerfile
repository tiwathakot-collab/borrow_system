# ใช้ PHP 8.0 CLI
FROM php:8.0-cli

# ติดตั้ง mysqli และ dependencies
RUN docker-php-ext-install mysqli

# กำหนด working directory
WORKDIR /app

# คัดลอกโค้ดไปยัง container
COPY . /app

# เปิด port 10000
EXPOSE 10000

# รัน PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
