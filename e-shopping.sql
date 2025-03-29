-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Mar 29, 2025 at 06:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-shopping`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecentSales` (IN `supplierID` INT)   BEGIN
    SELECT 
        o.TotalPrice,
        o.Quantity
    FROM orders o
    WHERE o.Supplier_id = supplierID 
    AND o.OrderDate >= CURDATE() - INTERVAL 10 DAY;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessReturnWithCursor` (IN `p_order_id` INT, IN `p_customer_id` INT, IN `p_return_reason` VARCHAR(255), IN `p_return_date` DATE)   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE prod_id INT;
    DECLARE qty INT;
    DECLARE cur CURSOR FOR SELECT Product_id, Quantity FROM orders WHERE Order_id = p_order_id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- İlgili sipariş kaydını güncelle
    UPDATE orders
    SET ReturnStatus = 1, ReturnReason = p_return_reason, ReturnDate = p_return_date
    WHERE Order_id = p_order_id AND Customer_id = p_customer_id;

    -- Cursor işlemi başlat
    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO prod_id, qty;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Ürün stok miktarını artır
        UPDATE product
        SET ProductStock = ProductStock + qty
        WHERE Product_id = prod_id;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `campaign`
--

CREATE TABLE `campaign` (
  `Campaign_id` int(11) NOT NULL,
  `CampaignCategory` varchar(100) DEFAULT NULL,
  `CampaignDate` date DEFAULT NULL,
  `Discount` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaign`
--

INSERT INTO `campaign` (`Campaign_id`, `CampaignCategory`, `CampaignDate`, `Discount`) VALUES
(1, 'null', '2024-12-03', 0.00),
(3, NULL, NULL, 10.00),
(4, NULL, NULL, 50.00),
(5, NULL, NULL, 20.00),
(6, NULL, NULL, 20.00),
(7, NULL, NULL, 10.00),
(8, NULL, NULL, 10.00),
(9, NULL, NULL, 50.00),
(10, NULL, NULL, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_id` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Product_id` int(11) DEFAULT NULL,
  `Customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashondelivery`
--

CREATE TABLE `cashondelivery` (
  `CoD_id` int(11) NOT NULL,
  `Payment_id` int(11) DEFAULT NULL,
  `PaymentCode` varchar(50) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashondelivery`
--

INSERT INTO `cashondelivery` (`CoD_id`, `Payment_id`, `PaymentCode`, `Amount`) VALUES
(23, 48, '632999', 8100.00),
(24, 49, '812453', 5000.00),
(25, 51, '707881', 10600.00);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `Category_id` int(11) NOT NULL,
  `CategoryName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`Category_id`, `CategoryName`) VALUES
(1, 'ELEKTRONİK'),
(2, 'GİYİM');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_id` int(11) NOT NULL,
  `User_id` int(11) DEFAULT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `BirthDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_id`, `User_id`, `FirstName`, `LastName`, `Phone`, `Address`, `BirthDate`) VALUES
(6, 1, 'Ahmet emre ', 'Akın', '05465657109', 'Adıyaman Besni ', '2002-06-21'),
(7, 3, 'volkan', 'yalvarıcı', '05892362178', 'izmir', '2001-09-15'),
(8, 7, 'Ali', 'İntaş', '5555555554', 'Ordu altınordu ', '2024-12-01');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `Favorite_id` int(11) NOT NULL,
  `Product_id` int(11) DEFAULT NULL,
  `AddedDate` date DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`Favorite_id`, `Product_id`, `AddedDate`, `Notes`, `Customer_id`) VALUES
(1, 8, '2024-12-29', NULL, 6),
(3, 8, '2024-12-31', NULL, 8);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_id` int(11) NOT NULL,
  `Message` varchar(255) NOT NULL,
  `Product_id` int(11) NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onlinepayment`
--

CREATE TABLE `onlinepayment` (
  `Op_id` int(11) NOT NULL,
  `Payment_id` int(11) DEFAULT NULL,
  `CardNumber` varchar(16) DEFAULT NULL,
  `ExpiryDate` varchar(5) DEFAULT NULL,
  `CVV` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onlinepayment`
--

INSERT INTO `onlinepayment` (`Op_id`, `Payment_id`, `CardNumber`, `ExpiryDate`, `CVV`) VALUES
(21, 47, '123123', '1323', '132');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_id` int(11) NOT NULL,
  `Supplier_id` int(11) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `TotalPrice` decimal(10,2) DEFAULT NULL,
  `ReturnStatus` tinyint(1) DEFAULT NULL,
  `ReturnReason` text DEFAULT NULL,
  `ReturnDate` date DEFAULT NULL,
  `Payment_id` int(11) DEFAULT NULL,
  `Product_id` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`Order_id`, `Supplier_id`, `OrderDate`, `TotalPrice`, `ReturnStatus`, `ReturnReason`, `ReturnDate`, `Payment_id`, `Product_id`, `Quantity`, `Customer_id`) VALUES
(32, 3, '2025-01-01', 8100.00, NULL, NULL, NULL, 47, 8, 1, 6),
(33, 3, '2025-01-01', 8100.00, NULL, NULL, NULL, 48, 8, 1, 6),
(34, 1, '2025-01-03', 5000.00, 1, 'asdasd', '2025-01-03', 49, 27, 1, 6),
(35, 3, '2025-01-03', 8100.00, NULL, NULL, NULL, 51, 8, 1, 6),
(36, 1, '2025-01-03', 2500.00, NULL, NULL, NULL, 51, 27, 1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_id` int(11) NOT NULL,
  `Cart_id` int(11) DEFAULT NULL,
  `PaymentStatus` varchar(50) DEFAULT NULL,
  `Customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_id`, `Cart_id`, `PaymentStatus`, `Customer_id`) VALUES
(47, NULL, '1', 6),
(48, NULL, '1', 6),
(49, NULL, '1', 6),
(50, NULL, '1', 6),
(51, NULL, '1', 6);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_id` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `ProductDescription` text DEFAULT NULL,
  `ProductPrice` decimal(10,2) DEFAULT NULL,
  `ProductStock` int(11) DEFAULT NULL,
  `Category_id` int(11) DEFAULT NULL,
  `Supplier_id` int(11) DEFAULT NULL,
  `Campaign_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_id`, `ProductName`, `ProductDescription`, `ProductPrice`, `ProductStock`, `Category_id`, `Supplier_id`, `Campaign_id`) VALUES
(8, 'victus', '512gb', 9000.00, 43, 1, 3, 3),
(22, 'rog', 'asdasda', 15000.00, 59, 1, 1, 3),
(23, 'asd', 'asdasda', 50000.00, 4998, 1, 1, 4),
(24, 'addaddadada', 'asdasda', 50000.00, 20, 1, 1, 5),
(27, 'çorap', 'kalındır', 5000.00, 51, 2, 1, 9),
(28, 'alsdöa', 'asda', 1231.00, 8, 1, 3, 10);

--
-- Triggers `product`
--
DELIMITER $$
CREATE TRIGGER `StockTrigger` AFTER UPDATE ON `product` FOR EACH ROW BEGIN
    IF NEW.ProductStock < 10 AND OLD.ProductStock >= 10 THEN
        INSERT INTO Notifications(Message, Product_id) 
        VALUES ('Stok azaldı', NEW.Product_id);
    ELSEIF NEW.ProductStock >= 10 AND OLD.ProductStock < 10 THEN
        DELETE FROM Notifications WHERE Product_id = NEW.Product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_view`
-- (See below for the actual view)
--
CREATE TABLE `product_view` (
`Product_id` int(11)
,`ProductName` varchar(255)
,`ProductPrice` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `Service_id` int(11) NOT NULL,
  `ServiceName` varchar(100) DEFAULT NULL,
  `ServicePrice` decimal(10,2) DEFAULT NULL,
  `ServiceDescription` text DEFAULT NULL,
  `Supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`Service_id`, `ServiceName`, `ServicePrice`, `ServiceDescription`, `Supplier_id`) VALUES
(1, 'ders', 120.00, 'asdasd', 1),
(2, 'sim kart', 6000.00, '5sa', 1);

-- --------------------------------------------------------

--
-- Table structure for table `shipment`
--

CREATE TABLE `shipment` (
  `Shipment_id` int(11) NOT NULL,
  `Order_id` int(11) DEFAULT NULL,
  `TrackingNumber` varchar(50) DEFAULT NULL,
  `Supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment`
--

INSERT INTO `shipment` (`Shipment_id`, `Order_id`, `TrackingNumber`, `Supplier_id`) VALUES
(18, 35, '13819023', 3),
(19, 32, '2323', 3),
(20, 33, '123132123', 3);

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `Supplier_id` int(11) NOT NULL,
  `User_id` int(11) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `CompanyPhone` varchar(15) DEFAULT NULL,
  `CompanyAddress` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`Supplier_id`, `User_id`, `CompanyName`, `CompanyPhone`, `CompanyAddress`) VALUES
(1, 2, 'turkcell', '05464654701', 'erzurum'),
(3, 5, 'hp', '05465657109', 'erzurum'),
(4, 6, 'hp1', '12345', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_id` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_id`, `Email`, `Password`) VALUES
(1, 'emreakin@gmail.com', '12345'),
(2, 'turkcell@gmail.com', '12345'),
(3, 'volkanyalvarici@gmail.com', '12345'),
(5, 'hp@gmail.com', '12345'),
(6, 'hp1@gmail.com', '12345'),
(7, 'ali@intas.com', '123');

-- --------------------------------------------------------

--
-- Structure for view `product_view`
--
DROP TABLE IF EXISTS `product_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_view`  AS SELECT `product`.`Product_id` AS `Product_id`, `product`.`ProductName` AS `ProductName`, `product`.`ProductPrice` AS `ProductPrice` FROM `product` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaign`
--
ALTER TABLE `campaign`
  ADD PRIMARY KEY (`Campaign_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_id`),
  ADD KEY `Product_id` (`Product_id`),
  ADD KEY `CC_Customer_id` (`Customer_id`);

--
-- Indexes for table `cashondelivery`
--
ALTER TABLE `cashondelivery`
  ADD PRIMARY KEY (`CoD_id`),
  ADD KEY `Payment_id` (`Payment_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`Category_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_id`),
  ADD KEY `User_id` (`User_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`Favorite_id`),
  ADD KEY `Product_id` (`Product_id`),
  ADD KEY `FC_Customer_id` (`Customer_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_id`),
  ADD KEY `notifications_ibfk_1` (`Product_id`);

--
-- Indexes for table `onlinepayment`
--
ALTER TABLE `onlinepayment`
  ADD PRIMARY KEY (`Op_id`),
  ADD KEY `Payment_id` (`Payment_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_id`),
  ADD KEY `Supplier_id` (`Supplier_id`),
  ADD KEY `OP_Payment_id` (`Payment_id`),
  ADD KEY `OC_Customer_id` (`Customer_id`),
  ADD KEY `OP_Product_Order` (`Product_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_id`),
  ADD KEY `Cart_id` (`Cart_id`),
  ADD KEY `PC_Customer_id` (`Customer_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_id`),
  ADD KEY `PS_Suplier_id` (`Supplier_id`),
  ADD KEY `CC_Campaign_id` (`Campaign_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`Service_id`),
  ADD KEY `SSS_Supplier_id` (`Supplier_id`);

--
-- Indexes for table `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`Shipment_id`),
  ADD KEY `Supplier_id` (`Supplier_id`),
  ADD KEY `shipment_ibfk_1` (`Order_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`Supplier_id`),
  ADD KEY `User_id` (`User_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaign`
--
ALTER TABLE `campaign`
  MODIFY `Campaign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `cashondelivery`
--
ALTER TABLE `cashondelivery`
  MODIFY `CoD_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `Category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `Favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `onlinepayment`
--
ALTER TABLE `onlinepayment`
  MODIFY `Op_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `Order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `Service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipment`
--
ALTER TABLE `shipment`
  MODIFY `Shipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `Supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `CC_Customer_id` FOREIGN KEY (`Customer_id`) REFERENCES `customer` (`Customer_id`),
  ADD CONSTRAINT `Product_id` FOREIGN KEY (`Product_id`) REFERENCES `product` (`Product_id`);

--
-- Constraints for table `cashondelivery`
--
ALTER TABLE `cashondelivery`
  ADD CONSTRAINT `cashondelivery_ibfk_1` FOREIGN KEY (`Payment_id`) REFERENCES `payment` (`Payment_id`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `FC_Customer_id` FOREIGN KEY (`Customer_id`) REFERENCES `customer` (`Customer_id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`Product_id`) REFERENCES `product` (`Product_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Product_id`) REFERENCES `product` (`Product_id`) ON DELETE CASCADE;

--
-- Constraints for table `onlinepayment`
--
ALTER TABLE `onlinepayment`
  ADD CONSTRAINT `onlinepayment_ibfk_1` FOREIGN KEY (`Payment_id`) REFERENCES `payment` (`Payment_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `OC_Customer_id` FOREIGN KEY (`Customer_id`) REFERENCES `customer` (`Customer_id`),
  ADD CONSTRAINT `OP_Payment_id` FOREIGN KEY (`Payment_id`) REFERENCES `payment` (`Payment_id`),
  ADD CONSTRAINT `OP_Product_Order` FOREIGN KEY (`Product_id`) REFERENCES `product` (`Product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`Supplier_id`) REFERENCES `supplier` (`Supplier_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `PC_Customer_id` FOREIGN KEY (`Customer_id`) REFERENCES `customer` (`Customer_id`),
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Cart_id`) REFERENCES `cart` (`Cart_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `CC_Campaign_id` FOREIGN KEY (`Campaign_id`) REFERENCES `campaign` (`Campaign_id`),
  ADD CONSTRAINT `PS_Suplier_id` FOREIGN KEY (`Supplier_id`) REFERENCES `supplier` (`Supplier_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `SSS_Supplier_id` FOREIGN KEY (`Supplier_id`) REFERENCES `supplier` (`Supplier_id`);

--
-- Constraints for table `shipment`
--
ALTER TABLE `shipment`
  ADD CONSTRAINT `Supplier_id` FOREIGN KEY (`Supplier_id`) REFERENCES `supplier` (`Supplier_id`),
  ADD CONSTRAINT `shipment_ibfk_1` FOREIGN KEY (`Order_id`) REFERENCES `orders` (`Order_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `user` (`User_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
