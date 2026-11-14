CREATE USER 'deployUser'@'localhost' IDENTIFIED BY 'admin';

GRANT ALL PRIVILEGES ON deploy.* TO 'deployUser'@'localhost';
