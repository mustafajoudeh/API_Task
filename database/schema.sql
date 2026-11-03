-- Back up your existing database before running this script.
CREATE DATABASE IF NOT EXISTS `login system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `login system`;

CREATE TABLE IF NOT EXISTS categories (
  CategorieID INT UNSIGNED NOT NULL AUTO_INCREMENT,
  CategorieName VARCHAR(120) NOT NULL,
  PRIMARY KEY (CategorieID),
  UNIQUE KEY UQ_categories_CategorieName (CategorieName)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fullName VARCHAR(150) NOT NULL,
  username VARCHAR(80) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(254) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UQ_user_username (username),
  UNIQUE KEY UQ_user_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  CategorieID INT UNSIGNED NOT NULL,
  PhotoURL VARCHAR(2048) NULL,
  `Product name` VARCHAR(180) NOT NULL,
  Description TEXT NULL,
  Price DECIMAL(10,2) NOT NULL DEFAULT 0,
  Quantity INT NOT NULL DEFAULT 0,
  `Created date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY IX_product_CategorieID (CategorieID),
  CONSTRAINT FK_product_categories FOREIGN KEY (CategorieID) REFERENCES categories(CategorieID)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT CK_product_Price CHECK (Price >= 0),
  CONSTRAINT CK_product_Quantity CHECK (Quantity >= 0)
) ENGINE=InnoDB;
