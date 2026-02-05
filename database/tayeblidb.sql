-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 10:35 PM
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
-- Database: `tayeblidb`
--
CREATE DATABASE IF NOT EXISTS `tayeblidb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tayeblidb`;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE IF NOT EXISTS `favorites` (
  `SaveID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `RecipeID` int(11) NOT NULL,
  `SavedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`SaveID`),
  UNIQUE KEY `unique_user_recipe` (`UserID`,`RecipeID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`SaveID`, `UserID`, `RecipeID`, `SavedAt`) VALUES
(21, 2, 4, '2026-01-31 17:14:29'),
(22, 2, 2, '2026-01-31 17:14:31'),
(23, 2, 5, '2026-01-31 18:39:02');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE IF NOT EXISTS `ingredients` (
  `IngredientID` int(11) NOT NULL AUTO_INCREMENT,
  `RecipeID` int(11) DEFAULT NULL,
  `Iname` varchar(100) DEFAULT NULL,
  `Amount` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`IngredientID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`IngredientID`, `RecipeID`, `Iname`, `Amount`) VALUES
(1, 1, 'idk', 'dik'),
(2, 2, 'jk', 'iijef'),
(3, 2, 'ojefm', 'kkneqelkn'),
(4, 3, 'ihlz', 'leefkdqsnk'),
(5, 3, 'ihzefihi', 'lnhlzef'),
(6, 4, 'jbhkb', 'bvvhkbkh'),
(7, 5, 'hilda&#039;s dad', '1'),
(8, 5, 'hilda&#039;s mom', '1'),
(36, 25, 'eggs', '6'),
(37, 25, 'tomatos', '6'),
(38, 26, 'eggs', '6'),
(39, 26, 'tomatos', '6'),
(40, 27, 'eggs', '6'),
(41, 28, 'eggs', '6'),
(42, 29, 'eggs', '55453'),
(43, 30, 'eggs', '44'),
(44, 31, 'eggs', '44'),
(45, 32, 'eggs', '5');

-- --------------------------------------------------------

--
-- Table structure for table `instructions`
--

CREATE TABLE IF NOT EXISTS `instructions` (
  `InstructionID` int(11) NOT NULL AUTO_INCREMENT,
  `RecipeID` int(11) DEFAULT NULL,
  `Step` int(11) DEFAULT NULL,
  `TimeNeeded` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`InstructionID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructions`
--

INSERT INTO `instructions` (`InstructionID`, `RecipeID`, `Step`, `TimeNeeded`, `Description`) VALUES
(1, 1, 1, 5, 'idk'),
(2, 2, 1, 6, 'kqefnkc'),
(3, 3, 1, 85, 'ezbflnd'),
(4, 3, 2, 85, 'knknqdkm'),
(5, 4, 1, 5, 'gvbhgyij'),
(6, 5, 1, 2147483647, 'love her'),
(26, 25, 1, 5565, 'joejzsùl;s'),
(27, 26, 1, 5565, 'joejzsùl;s'),
(28, 27, 1, 64546, 'bjcnlj&quot;ndza'),
(29, 28, 1, 64546, 'bjcnlj&quot;ndza'),
(30, 29, 1, 5535, 'lklnsrsv,'),
(31, 30, 1, 53, '4'),
(32, 31, 1, 53, '44'),
(33, 32, 1, 655, 'ljmznlkznrgk');

-- --------------------------------------------------------

--
-- Table structure for table `plannedrecipes`
--

CREATE TABLE IF NOT EXISTS `plannedrecipes` (
  `PlannedID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `PlanDay` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `MealType` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `RecipeID` int(11) NOT NULL,
  `AddedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`PlannedID`),
  UNIQUE KEY `unique_daily_meal_slot` (`UserID`,`PlanDay`,`MealType`) USING BTREE,
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plannedrecipes`
--

INSERT INTO `plannedrecipes` (`PlannedID`, `UserID`, `PlanDay`, `MealType`, `RecipeID`, `AddedAt`) VALUES
(74, 2, 'monday', 'snack', 2, '2026-01-14 11:49:31'),
(76, 2, 'monday', 'lunch', 4, '2026-01-31 18:52:38'),
(77, 2, 'monday', 'breakfast', 5, '2026-01-31 18:52:40'),
(78, 2, 'monday', 'dinner', 3, '2026-01-31 18:54:27');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE IF NOT EXISTS `recipes` (
  `RecipeID` int(11) NOT NULL AUTO_INCREMENT,
  `Rtitle` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `PrepTime` int(11) DEFAULT NULL,
  `CookTime` int(11) DEFAULT NULL,
  `TotalTime` int(11) GENERATED ALWAYS AS (`PrepTime` + `CookTime`) STORED,
  `Serving` int(11) DEFAULT NULL,
  `Category` enum('Breakfast','Lunch','Dinner','Snack','Dessert') DEFAULT NULL,
  `Difficulty` enum('Easy','Medium','Hard') DEFAULT NULL,
  `ChefID` int(11) DEFAULT NULL,
  `ImageFilename` varchar(255) DEFAULT NULL,
  `ImagePath` varchar(500) DEFAULT NULL,
  `vegetarian` tinyint(1) DEFAULT 0,
  `vegan` tinyint(1) DEFAULT 0,
  `gluten_free` tinyint(1) DEFAULT 0,
  `dairy_free` tinyint(1) DEFAULT 0,
  `has_nuts` tinyint(1) DEFAULT 0,
  `low_carb` tinyint(1) DEFAULT 0,
  `Carbs` int(11) NOT NULL DEFAULT 0,
  `Proteins` int(11) NOT NULL DEFAULT 0,
  `Calories` int(11) NOT NULL DEFAULT 0,
  `Fat` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`RecipeID`),
  KEY `ChefID` (`ChefID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`RecipeID`, `Rtitle`, `Description`, `PrepTime`, `CookTime`, `Serving`, `Category`, `Difficulty`, `ChefID`, `ImageFilename`, `ImagePath`, `vegetarian`, `vegan`, `gluten_free`, `dairy_free`, `has_nuts`, `low_carb`, `Carbs`, `Proteins`, `Calories`, `Fat`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'tst', 'tst', 12, 20, 3, 'Dinner', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/ac/98/01/ac9801cd9833cc153194c0691d03892b.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-06 14:20:28', '2026-01-31 18:31:54'),
(2, 'tst2', 'tst2', 2, 55, 2, 'Breakfast', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/79/f9/d0/79f9d0f2f88fb5a8d49aab120e2b63a4.jpg', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '2026-01-06 14:30:46', '2026-01-29 21:05:27'),
(3, 'tst3', 'tst3', 5, 3, 8, 'Breakfast', 'Hard', 2, NULL, 'https://i.pinimg.com/736x/ce/df/28/cedf28a582f2354e016d686b2743e67d.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-06 14:33:35', '2026-01-29 21:31:20'),
(4, 'tst111ljb', 'nlnln', 962, 985, 9, 'Breakfast', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/f5/ca/eb/f5caeb858049f8449f5fe7bac4931aaf.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-29 21:29:56', '2026-01-29 21:30:48'),
(5, 'hilda2026', 'hildaaaaa', 5367, 6786, 1, 'Breakfast', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/4d/6b/a4/4d6ba417648275422e38486cd819a34b.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-31 18:34:02', '2026-01-31 18:45:27'),
(25, 'nipzjz,ezfe', 'pozeff,ezf', 545, 656, 646, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 17:58:28', '2026-02-03 17:58:28'),
(26, 'nipzjz,ezfe', 'pozeff,ezf', 545, 656, 646, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:01:36', '2026-02-03 18:01:36'),
(27, 'nipzjz,ezfe', 'pozeff,ezf', 545, 656, 646, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:02:00', '2026-02-03 18:02:00'),
(28, 'nipzjz,ezfe', 'pozeff,ezf', 545, 656, 646, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:03:27', '2026-02-03 18:03:27'),
(29, 'nipzjz,ezfe', 'pozeff,ezf', 545, 656, 646, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:04:02', '2026-02-03 18:04:02'),
(30, 'eirjgk', 'qieqhqgoiihi', 4645, 3435, 64, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:09:11', '2026-02-03 18:09:11'),
(31, 'eirjgk', 'qieqhqgoiihi', 4645, 3435, 64, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:09:12', '2026-02-03 18:09:12'),
(32, ',h,vv,h', 'lklblkn', 974, 484, 684, 'Breakfast', 'Easy', 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-03 18:15:50', '2026-02-03 18:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `UserID` int(11) NOT NULL,
  `Comm` varchar(255) DEFAULT NULL,
  `Rating` decimal(3,2) DEFAULT NULL,
  `RecipeID` int(11) NOT NULL,
  `ReviewDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`UserID`,`RecipeID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`UserID`, `Comm`, `Rating`, `RecipeID`, `ReviewDate`) VALUES
(2, 'HILDAAAAA', 5.00, 2, '2026-01-06 21:50:39'),
(2, 'OMG HOW COOL IS SHEEEEEEEEEE <3', 5.00, 4, '2026-01-31 18:47:20'),
(2, 'I LOOOOOOOOOOOVE HER', 5.00, 5, '2026-01-31 18:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `savedforplanner`
--

CREATE TABLE IF NOT EXISTS `savedforplanner` (
  `UserID` int(11) NOT NULL,
  `RecipeID` int(11) NOT NULL,
  `SavedTime` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`UserID`,`RecipeID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `savedforplanner`
--

INSERT INTO `savedforplanner` (`UserID`, `RecipeID`, `SavedTime`) VALUES
(2, 2, '2026-01-08 16:38:30'),
(2, 3, '2026-01-08 17:47:30'),
(2, 4, '2026-01-31 18:52:23'),
(2, 5, '2026-01-31 18:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `Passwords` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `PreferedDifficulty` enum('Easy','Medium','Hard','Any Difficulty') DEFAULT 'Medium',
  `visibility` enum('Public','Friends','Private') DEFAULT 'Private',
  `is_vegetarian` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `is_dairy_free` tinyint(1) DEFAULT 0,
  `has_nut_allergy` tinyint(1) DEFAULT 0,
  `is_low_carb` tinyint(1) DEFAULT 0,
  `TotalRecipes` int(11) NOT NULL,
  `max_time` enum('15 minutes','30 minutes','45 minutes','1 hour','1.5 hours','Any duration') NOT NULL DEFAULT 'Any duration',
  `cooking_skill` enum('Beginner - Just starting out','Intermediate - Comfortable in the kitchen','Advanced - Experienced cook') NOT NULL DEFAULT 'Intermediate - Comfortable in the kitchen',
  `fav_serving` enum('1 person','2 people','4 people','6 people','8 people') NOT NULL DEFAULT '2 people',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Passwords`, `Email`, `PreferedDifficulty`, `visibility`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_dairy_free`, `has_nut_allergy`, `is_low_carb`, `TotalRecipes`, `max_time`, `cooking_skill`, `fav_serving`) VALUES
(2, 'Samir_Korbas', '$2y$10$5RHdEfjsUvFIwpSdqtCZeuonIvV3vLxCgD9QeYx5vcUQpINRAs8y.', 'korbassamirou@gmail.com', 'Medium', 'Private', 0, 0, 0, 0, 0, 0, 32, 'Any duration', 'Intermediate - Comfortable in the kitchen', '2 people');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Constraints for table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Constraints for table `plannedrecipes`
--
ALTER TABLE `plannedrecipes`
  ADD CONSTRAINT `plannedrecipes_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `plannedrecipes_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`ChefID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Constraints for table `savedforplanner`
--
ALTER TABLE `savedforplanner`
  ADD CONSTRAINT `savedforplanner_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `savedforplanner_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
