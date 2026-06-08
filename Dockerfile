FROM php:8.0-apache
# Copy toàn bộ code từ máy bạn vào thư mục gốc của web
COPY . /var/www/html/
# Mở quyền truy cập cho thư mục
RUN chown -R www-data:www-data /var/www/html