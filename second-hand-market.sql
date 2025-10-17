-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 09:59 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `second-hand-market`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `DeliveryID` int(11) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `DeliveryAddress` text NOT NULL,
  `TrackingNumber` varchar(100) DEFAULT NULL,
  `ScheduledDate` date DEFAULT NULL,
  `Status` enum('Scheduled','In Transit','Completed') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`DeliveryID`, `OrderID`, `DeliveryAddress`, `TrackingNumber`, `ScheduledDate`, `Status`) VALUES
(1, 2, 'To be provided by user', NULL, NULL, ''),
(2, 3, '', NULL, NULL, ''),
(3, 4, '', NULL, NULL, ''),
(4, 5, '', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `offer`
--

CREATE TABLE `offer` (
  `OfferID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `OfferAmount` decimal(10,2) NOT NULL,
  `Status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `OfferDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `offer`
--

INSERT INTO `offer` (`OfferID`, `ProductID`, `UserID`, `OfferAmount`, `Status`, `OfferDate`) VALUES
(1, 4, 1, '500.00', 'Pending', '2025-08-18 06:39:13'),
(2, 5, 2, '500.00', 'Pending', '2025-08-18 06:50:00'),
(3, 4, 1, '500.00', 'Pending', '2025-08-18 06:51:42'),
(4, 4, 1, '500.00', 'Pending', '2025-08-18 09:04:46'),
(5, 5, 4, '100.00', 'Pending', '2025-08-18 09:28:01'),
(6, 4, 4, '100.00', 'Pending', '2025-08-18 09:28:14'),
(7, 5, 4, '100.00', 'Pending', '2025-08-18 09:37:00'),
(8, 7, 2, '100.00', 'Pending', '2025-08-18 10:01:12'),
(9, 8, 2, '100.00', 'Accepted', '2025-08-18 10:20:42'),
(10, 10, 1, '1.00', 'Accepted', '2025-08-18 11:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `BuyerID` int(11) DEFAULT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `ProductID`, `BuyerID`, `TotalAmount`, `OrderDate`, `Status`) VALUES
(1, 5, 2, '1000.00', '2025-08-18 09:40:54', 'Processing'),
(2, 4, 1, '1000.00', '2025-08-18 09:49:52', 'Processing'),
(3, 8, 2, '100.00', '2025-08-18 10:20:52', 'Processing'),
(4, 7, 6, '5000.00', '2025-08-18 11:18:09', 'Processing'),
(5, 10, 1, '1.00', '2025-08-18 11:24:01', 'Processing');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `SellerID` int(11) DEFAULT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Condition` enum('New','Used - Like New','Used - Good','Used - Fair') NOT NULL,
  `Status` enum('Available','Sold','Hidden') DEFAULT 'Available',
  `ListDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `IsFlagged` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `SellerID`, `CategoryID`, `Title`, `Description`, `Price`, `Condition`, `Status`, `ListDate`, `IsFlagged`) VALUES
(4, 2, NULL, 'Toyota Celica', 'All White - New Condition', '1000.00', 'New', 'Sold', '2025-08-18 05:35:22', 0),
(5, 1, NULL, 'Honda Accord', '1990 Model', '1000.00', 'New', 'Sold', '2025-08-18 05:40:32', 0),
(7, 4, NULL, 'Mitsubishi Lancer Evo IX', '0-60  @4 seconds', '5000.00', 'New', 'Sold', '2025-08-18 09:27:42', 0),
(8, 5, NULL, 'Honda Civic LX', '2000 Model', '500.00', 'New', 'Sold', '2025-08-18 10:20:07', 0),
(10, 6, NULL, 'ggdg', 'gdgdgdg', '21212.00', 'New', 'Sold', '2025-08-18 11:22:54', 0);

-- --------------------------------------------------------

--
-- Table structure for table `productimage`
--

CREATE TABLE `productimage` (
  `ImageID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `ImageURL` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `productimage`
--

INSERT INTO `productimage` (`ImageID`, `ProductID`, `ImageURL`) VALUES
(1, 4, 'images/product_68a2bb9a115544.69831671.jpg'),
(2, 5, 'images/product_68a2bcd0320368.85189706.jpg'),
(4, 7, 'images/product_68a2f20e9bd567.92391629.jpg'),
(5, 8, 'images/product_68a2fe57c8d061.55717571.jpg'),
(7, 10, 'images/product_68a30d0e4aa231.03528848.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `ReviewID` int(11) NOT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `ReviewerID` int(11) DEFAULT NULL,
  `SellerRating` tinyint(4) DEFAULT NULL,
  `ProductRating` tinyint(4) DEFAULT NULL,
  `Comment` text DEFAULT NULL,
  `ReviewDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `AvgRating` decimal(3,2) DEFAULT 0.00,
  `Street` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `PostalCode` varchar(20) DEFAULT NULL,
  `JoinDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `IsAdmin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Name`, `Email`, `PasswordHash`, `AvgRating`, `Street`, `City`, `PostalCode`, `JoinDate`, `IsAdmin`) VALUES
(1, 'Noman Oman', 'noman.oman@probashi.com', '$2y$10$VDM8XCVKEDjhK/RF9TsiYONcFocHr3r2AEa6XaS95BJI9PuM.hoR.', '0.00', NULL, NULL, NULL, '2025-08-18 04:55:06', 0),
(2, 'Noman Beiman', 'noman.beiman@probashi.com', '$2y$10$ILk73Gu66MXtNYWA4a6NnumX6wirzY4YQC.Af9oGzNI8ku0yic.C6', '0.00', NULL, NULL, NULL, '2025-08-18 05:24:13', 0),
(3, 'Nesar Ahmed', 'nesar.ahmed@admin.com', '$2y$10$5.xeBl3L38wGZY6plZafgOsKsy5N2b4eIO8j79SWmAZZgmdjFZ4Ui', '0.00', NULL, NULL, NULL, '2025-08-18 05:44:23', 1),
(4, 'Ragib Shahriar Noman', 'noman.noman@probashi.com', '$2y$10$4kvCMdyi2irNGuEui7DuBudglY0xRIgpFX42mhGHV2Ixn2oDUwVli', '0.00', NULL, NULL, NULL, '2025-08-18 09:10:55', 0),
(5, 'Test', 'test@test.com', '$2y$10$ik8BnL4Pqe09ByoQWVioQun8EEHjat5Mji0v.XIplg9Cy7MBXtZA2', '0.00', NULL, NULL, NULL, '2025-08-18 10:17:55', 0),
(6, 'ABC', 'abc@gmail.com', '$2y$10$jbyCtUf7Uq6nmzWlYDDMxeGEbEnL.q/Yk/1YBycp9wLVCEaIDdw82', '0.00', NULL, NULL, NULL, '2025-08-18 11:16:47', 0);

-- --------------------------------------------------------

--
-- Table structure for table `userphone`
--

CREATE TABLE `userphone` (
  `PhoneID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `PhoneNumber` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`),
  ADD UNIQUE KEY `CategoryName` (`CategoryName`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`DeliveryID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`);

--
-- Indexes for table `offer`
--
ALTER TABLE `offer`
  ADD PRIMARY KEY (`OfferID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`),
  ADD UNIQUE KEY `ProductID` (`ProductID`),
  ADD KEY `BuyerID` (`BuyerID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `SellerID` (`SellerID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `productimage`
--
ALTER TABLE `productimage`
  ADD PRIMARY KEY (`ImageID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ReviewerID` (`ReviewerID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `userphone`
--
ALTER TABLE `userphone`
  ADD PRIMARY KEY (`PhoneID`),
  ADD KEY `UserID` (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `DeliveryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `offer`
--
ALTER TABLE `offer`
  MODIFY `OfferID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `productimage`
--
ALTER TABLE `productimage`
  MODIFY `ImageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `userphone`
--
ALTER TABLE `userphone`
  MODIFY `PhoneID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `offer`
--
ALTER TABLE `offer`
  ADD CONSTRAINT `offer_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE,
  ADD CONSTRAINT `offer_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`BuyerID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`SellerID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`CategoryID`) REFERENCES `category` (`CategoryID`) ON DELETE SET NULL;

--
-- Constraints for table `productimage`
--
ALTER TABLE `productimage`
  ADD CONSTRAINT `productimage_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`ReviewerID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `userphone`
--
ALTER TABLE `userphone`
  ADD CONSTRAINT `userphone_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
