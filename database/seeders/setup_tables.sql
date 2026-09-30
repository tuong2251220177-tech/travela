-- Create tbl_users table
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255),
  `activation_token` varchar(255),
  `isActive` char(1) DEFAULT 'n',
  `google_id` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_admin table
CREATE TABLE IF NOT EXISTS `tbl_admin` (
  `adminId` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`adminId`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_tours table
CREATE TABLE IF NOT EXISTS `tbl_tours` (
  `tourId` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext,
  `priceAdult` decimal(10,2),
  `priceChild` decimal(10,2),
  `time` int,
  `destination` varchar(255),
  `quantity` int,
  `availability` tinyint DEFAULT '1',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tourId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_images table
CREATE TABLE IF NOT EXISTS `tbl_images` (
  `imageId` int NOT NULL AUTO_INCREMENT,
  `tourId` int NOT NULL,
  `imagePath` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`imageId`),
  KEY `tourId` (`tourId`),
  CONSTRAINT `tbl_images_ibfk_1` FOREIGN KEY (`tourId`) REFERENCES `tbl_tours` (`tourId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_temp_images table
CREATE TABLE IF NOT EXISTS `tbl_temp_images` (
  `tempImageId` int NOT NULL AUTO_INCREMENT,
  `imagePath` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tempImageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_timeline table
CREATE TABLE IF NOT EXISTS `tbl_timeline` (
  `timelineId` int NOT NULL AUTO_INCREMENT,
  `tourId` int NOT NULL,
  `day` int,
  `title` varchar(255),
  `description` longtext,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`timelineId`),
  KEY `tourId` (`tourId`),
  CONSTRAINT `tbl_timeline_ibfk_1` FOREIGN KEY (`tourId`) REFERENCES `tbl_tours` (`tourId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_booking table
CREATE TABLE IF NOT EXISTS `tbl_booking` (
  `bookingId` int NOT NULL AUTO_INCREMENT,
  `tourId` int NOT NULL,
  `userId` int NOT NULL,
  `numAdults` int DEFAULT '0',
  `numChildren` int DEFAULT '0',
  `bookingStatus` char(1) DEFAULT 'p',
  `bookingDate` timestamp DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bookingId`),
  KEY `tourId` (`tourId`),
  KEY `userId` (`userId`),
  CONSTRAINT `tbl_booking_ibfk_1` FOREIGN KEY (`tourId`) REFERENCES `tbl_tours` (`tourId`),
  CONSTRAINT `tbl_booking_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `tbl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_checkout table
CREATE TABLE IF NOT EXISTS `tbl_checkout` (
  `checkoutId` int NOT NULL AUTO_INCREMENT,
  `bookingId` int NOT NULL,
  `totalPrice` decimal(10,2),
  `paymentMethod` varchar(50),
  `paymentStatus` varchar(50) DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`checkoutId`),
  KEY `bookingId` (`bookingId`),
  CONSTRAINT `tbl_checkout_ibfk_1` FOREIGN KEY (`bookingId`) REFERENCES `tbl_booking` (`bookingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_reviews table
CREATE TABLE IF NOT EXISTS `tbl_reviews` (
  `reviewId` int NOT NULL AUTO_INCREMENT,
  `tourId` int NOT NULL,
  `userId` int NOT NULL,
  `rating` int DEFAULT '0',
  `comment` longtext,
  `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`reviewId`),
  KEY `tourId` (`tourId`),
  KEY `userId` (`userId`),
  CONSTRAINT `tbl_reviews_ibfk_1` FOREIGN KEY (`tourId`) REFERENCES `tbl_tours` (`tourId`),
  CONSTRAINT `tbl_reviews_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `tbl_users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tbl_contact table
CREATE TABLE IF NOT EXISTS `tbl_contact` (
  `contactId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255),
  `subject` varchar(255),
  `message` longtext,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
