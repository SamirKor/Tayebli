-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 06 jan. 2026 à 20:30
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tayeblidb`
--

-- --------------------------------------------------------

--
-- Structure de la table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `SaveID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `RecipeID` int(11) NOT NULL,
  `SavedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`SaveID`),
  UNIQUE KEY `unique_user_recipe` (`UserID`,`RecipeID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE IF NOT EXISTS `ingredients` (
  `IngredientID` int(11) NOT NULL AUTO_INCREMENT,
  `RecipeID` int(11) DEFAULT NULL,
  `Iname` varchar(100) DEFAULT NULL,
  `Amount` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`IngredientID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ingredients`
--

INSERT INTO `ingredients` (`IngredientID`, `RecipeID`, `Iname`, `Amount`) VALUES
(1, 1, 'idk', 'dik'),
(2, 2, 'jk', 'iijef'),
(3, 2, 'ojefm', 'kkneqelkn'),
(4, 3, 'ihlz', 'leefkdqsnk'),
(5, 3, 'ihzefihi', 'lnhlzef');

-- --------------------------------------------------------

--
-- Structure de la table `instructions`
--

DROP TABLE IF EXISTS `instructions`;
CREATE TABLE IF NOT EXISTS `instructions` (
  `InstructionID` int(11) NOT NULL AUTO_INCREMENT,
  `RecipeID` int(11) DEFAULT NULL,
  `Step` int(11) DEFAULT NULL,
  `TimeNeeded` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`InstructionID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `instructions`
--

INSERT INTO `instructions` (`InstructionID`, `RecipeID`, `Step`, `TimeNeeded`, `Description`) VALUES
(1, 1, 1, 5, 'idk'),
(2, 2, 1, 6, 'kqefnkc'),
(3, 3, 1, 85, 'ezbflnd'),
(4, 3, 2, 85, 'knknqdkm');

-- --------------------------------------------------------

--
-- Structure de la table `plannedrecipes`
--

DROP TABLE IF EXISTS `plannedrecipes`;
CREATE TABLE IF NOT EXISTS `plannedrecipes` (
  `PlannedID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `PlanDate` date NOT NULL,
  `MealType` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `RecipeID` int(11) NOT NULL,
  `AddedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`PlannedID`),
  UNIQUE KEY `unique_daily_meal_slot` (`UserID`,`PlanDate`,`MealType`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prefcuisines`
--

DROP TABLE IF EXISTS `prefcuisines`;
CREATE TABLE IF NOT EXISTS `prefcuisines` (
  `UserID` int(11) DEFAULT NULL,
  `CuisineName` varchar(100) DEFAULT NULL,
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
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
  `TotalReviews` int(11) NOT NULL,
  `averageReview` decimal(3,2) NOT NULL,
  PRIMARY KEY (`RecipeID`),
  KEY `ChefID` (`ChefID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recipes`
--

INSERT INTO `recipes` (`RecipeID`, `Rtitle`, `Description`, `PrepTime`, `CookTime`, `Serving`, `Category`, `Difficulty`, `ChefID`, `ImageFilename`, `ImagePath`, `vegetarian`, `vegan`, `gluten_free`, `dairy_free`, `has_nuts`, `low_carb`, `Carbs`, `Proteins`, `Calories`, `Fat`, `CreatedAt`, `UpdatedAt`, `TotalReviews`, `averageReview`) VALUES
(1, 'tst', 'tst', 12, 20, 3, 'Lunch', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/ac/98/01/ac9801cd9833cc153194c0691d03892b.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-06 14:20:28', '2026-01-06 14:28:39', 0, 0.00),
(2, 'tst2', 'tst2', 2, 55, 2, 'Breakfast', 'Easy', 2, NULL, 'https://i.pinimg.com/1200x/79/f9/d0/79f9d0f2f88fb5a8d49aab120e2b63a4.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-06 14:30:46', '2026-01-06 17:42:43', 0, 0.00),
(3, 'tst3', 'tst3', 5, 3, 8, 'Dinner', 'Hard', 2, NULL, 'https://i.pinimg.com/736x/ce/df/28/cedf28a582f2354e016d686b2743e67d.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-06 14:33:35', '2026-01-06 14:34:33', 0, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `UserID` int(11) NOT NULL,
  `Comm` varchar(255) DEFAULT NULL,
  `Rating` decimal(3,2) DEFAULT NULL,
  `RecipeID` int(11) NOT NULL,
  `ReviewDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`UserID`,`RecipeID`),
  KEY `RecipeID` (`RecipeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
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
  `email_notifications` tinyint(1) DEFAULT 1,
  `recipe_of_day` tinyint(1) DEFAULT 1,
  `new_recipe_alerts` tinyint(1) DEFAULT 1,
  `meal_planning_reminders` tinyint(1) DEFAULT 1,
  `weekly_digest` tinyint(1) DEFAULT 1,
  `TotalRecipes` int(11) NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Passwords`, `Email`, `PreferedDifficulty`, `visibility`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_dairy_free`, `has_nut_allergy`, `is_low_carb`, `email_notifications`, `recipe_of_day`, `new_recipe_alerts`, `meal_planning_reminders`, `weekly_digest`, `TotalRecipes`) VALUES
(2, 'Samir_Korbas', '$2y$10$5RHdEfjsUvFIwpSdqtCZeuonIvV3vLxCgD9QeYx5vcUQpINRAs8y.', 'korbassamirou@gmail.com', 'Medium', 'Private', 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 3);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plannedrecipes`
--
ALTER TABLE `plannedrecipes`
  ADD CONSTRAINT `plannedrecipes_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `plannedrecipes_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prefcuisines`
--
ALTER TABLE `prefcuisines`
  ADD CONSTRAINT `prefcuisines_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`ChefID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`RecipeID`) REFERENCES `recipes` (`RecipeID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
