# ใช้ PHP CLI 8.0
FROM php:8.0-cli

# ติดตั้ง dependencies สำหรับ PostgreSQL และ PHP extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# กำหนด working directory
WORKDIR /app

# คัดลอกโค้ดโปรเจคไป container
COPY . /app

# เปิด port ให้ PHP built-in server
EXPOSE 10000

# รัน PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
