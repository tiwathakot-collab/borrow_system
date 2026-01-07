# ใช้ PHP 8.0 CLI
FROM php:8.0-cli

# กำหนด working directory
WORKDIR /app

# คัดลอกไฟล์ทั้งหมดจาก repo ไปยัง container
COPY . /app

# เปิด port 10000 สำหรับ PHP built-in server
EXPOSE 10000

# รัน PHP server
CMD ["php", "-S", "0.0.0.0:10000"]
