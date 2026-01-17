-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 06:55 PM
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
-- Database: `nafas`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(1, 'wissal', 'wissalkhraifi.kh@gmail.com', 'qwertz', '2025-11-30 14:28:48', NULL),
(2, 'doua', 'douaahlel@gmail.com', 'qwertzu', '2025-11-30 14:29:44', NULL),
(3, 'aziz', 'azizhkili1@gmail.com', 'qwertzui', '2025-11-30 14:30:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brochures`
--

CREATE TABLE `brochures` (
  `brochure_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brochures`
--

INSERT INTO `brochures` (`brochure_id`, `title`, `file_path`, `created_by`, `created_at`) VALUES
(6, 'sensibilisation', 'uploads/brochures/brochure_693d37d3b2cfc1.47933115.pdf', 1, '2025-12-13 10:54:27'),
(7, 'warning', 'uploads/brochures/brochure_693d38249379f0.38103532.pdf', 1, '2025-12-13 10:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `brochure_downloads`
--

CREATE TABLE `brochure_downloads` (
  `download_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `brochure_id` int(11) NOT NULL,
  `downloaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brochure_downloads`
--

INSERT INTO `brochure_downloads` (`download_id`, `member_id`, `brochure_id`, `downloaded_at`) VALUES
(1, 18, 6, '2026-01-07 16:27:12'),
(2, 18, 6, '2026-01-07 16:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `encouragements`
--

CREATE TABLE `encouragements` (
  `message_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `trigger_type` enum('quiz','story','download') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `username`, `email`, `password`, `created_at`, `last_login`, `profile_image`) VALUES
(18, 'ahmed', 'ahmed@gmail.com', '$2y$10$03JuZ8O16MJTePKVaZBow.MeOtQCclho0pdehrCU8HZoSPbEFk6my', '2025-12-13 10:35:44', NULL, 'uploads/profiles/user_18_1767803484.jpg'),
(19, 'roua', 'roua@gmail.com', '$2y$10$YIeY91aN171o/3HJ2Dg9Ye/axRytm/O3VhBBzOmKWWU/8RQHOpC7G', '2025-12-13 10:37:21', NULL, NULL),
(22, 'hela', 'hela@gmail.com', '$2y$10$L8gnemcjWJ9Ch892Dz3G4.bie9V/SqZlxVMb4s5tVy6PZlio3ufJq', '2025-12-19 18:54:25', NULL, NULL),
(24, 'imen', 'imen@gmail.com', '$2y$10$PCP.PxqQrSFDKpXt8A0WJeYdoj5EBLi3i5mYkfXEo/g23AWtJ0a4u', '2025-12-19 18:55:16', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `opp_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `category` enum('formation','emploi','stage','projet') NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opportunities`
--

INSERT INTO `opportunities` (`opp_id`, `title`, `description`, `link`, `category`, `region`, `created_by`, `created_at`) VALUES
(5, 'Formation Data Scientist - Certifiée NVIDIA', 'Opte pour la meilleure formation pour te reconvertir dans le domaine de la Data Science. En 20 semaines, deviens expert en analyse de données et développe un portfolio solide.', 'https://gomycode.com/tn/fr/courses/data-scientist-bootcamp/', 'formation', 'boumhal', 1, '2025-12-13 10:38:54'),
(6, 'Stage PFE – Développeur Mobile React Native', 'Pycsu , un projet d’IA créative pour enfants : une app mobile où l’imagination se transforme en histoires illustrées grâce à l’intelligence artificielle . Le produit est pensé dès le départ pour un public international – avec un focus particulier sur les USA.\r\n\r\nOn ne cherche pas juste un(e) “stagiaire dev” pour corriger des bugs.\r\nOn cherche quelqu’un qui veut construire une vraie app from scratch, poser des bases propres, et repartir avec un projet solide dans son portfolio.', 'https://www.tanitjobs.com/job/1956926/stage-pfe-d%C3%A9veloppeur-mobile-react-native/?backPage=&searchID=1765618819.4161', 'stage', 'centre ville', 1, '2025-12-13 10:41:58'),
(7, 'Stage Marketing & Optimisation de l’expérience client', 'Opportunité de stage pour travailler sur l\'amélioration du parcours client et la mise en place de stratégies marketing. Idéal pour les étudiants cherchant à acquérir une expérience pratique dans l\'optimisation de la satisfaction client.', 'https://hi-interns.com/internships/marketing-optimisation-de-lexperience-client', 'stage', 'Tunis', 1, '2026-01-05 19:56:47');

-- --------------------------------------------------------

--
-- Table structure for table `opportunity_favorites`
--

CREATE TABLE `opportunity_favorites` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `opp_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opportunity_favorites`
--

INSERT INTO `opportunity_favorites` (`id`, `member_id`, `opp_id`, `created_at`) VALUES
(3, 18, 7, '2026-01-07 16:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quiz_id`, `title`, `content`, `created_by`, `created_at`) VALUES
(8, 'quiz entrepreneur', 'L’entrepreneur voit le problème comme une opportunité, pas comme un obstacle.', 1, '2025-12-13 10:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(8, 8, 'Quand tu vois un problème autour de toi, tu…', 'L’ignores, ce n’est pas ton rôle', 'T’en plains avec les autres', 'Cherches une solution réaliste', 'Imagines une idée innovante', 'B'),
(9, 8, 'Le risque, pour toi, c’est…', 'Quelque chose à éviter à tout prix', 'Source de stress', 'Inévitable pour avancer', 'Une chance d’apprendre et de grandir', 'C'),
(10, 8, 'Ton rapport au travail est plutôt…', 'Faire le minimum demandé', 'Suivre strictement les consignes', 'Donner le meilleur de toi-même', 'Créer ta propre façon de travailler', 'D');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_responses`
--

CREATE TABLE `quiz_responses` (
  `response_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` enum('A','B','C','D') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_responses`
--

INSERT INTO `quiz_responses` (`response_id`, `member_id`, `question_id`, `selected_option`, `created_at`) VALUES
(13, 18, 8, 'B', '2025-12-20 15:28:36'),
(14, 18, 9, 'C', '2025-12-20 15:28:36'),
(15, 18, 10, 'C', '2025-12-20 15:28:36'),
(16, 18, 8, 'A', '2025-12-20 15:29:13'),
(17, 18, 9, 'A', '2025-12-20 15:29:13'),
(18, 18, 10, 'C', '2025-12-20 15:29:13'),
(19, 18, 8, 'A', '2025-12-27 13:48:21'),
(20, 18, 9, 'D', '2025-12-27 13:48:21'),
(21, 18, 10, 'B', '2025-12-27 13:48:21'),
(22, 18, 8, 'B', '2025-12-29 19:12:41'),
(23, 18, 9, 'C', '2025-12-29 19:12:41'),
(24, 18, 10, 'A', '2025-12-29 19:12:41'),
(25, 18, 8, 'B', '2025-12-29 19:14:39'),
(26, 18, 9, 'C', '2025-12-29 19:14:39'),
(27, 18, 10, 'A', '2025-12-29 19:14:39'),
(28, 18, 8, 'A', '2026-01-07 16:58:59'),
(29, 18, 9, 'B', '2026-01-07 16:58:59'),
(30, 18, 10, 'A', '2026-01-07 16:58:59'),
(31, 18, 8, 'B', '2026-01-07 17:04:38'),
(32, 18, 9, 'A', '2026-01-07 17:04:38'),
(33, 18, 10, 'D', '2026-01-07 17:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `sensibilisation`
--

CREATE TABLE `sensibilisation` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_publication` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensibilisation`
--

INSERT INTO `sensibilisation` (`id`, `titre`, `description`, `image_path`, `created_by`, `date_publication`) VALUES
(5, '1. Les Facteurs de Départ', 'Crise Économique : L\'inflation galopante, la baisse du pouvoir d\'achat et le chômage des jeunes (qui dépasse souvent 35-40% dans certaines régions intérieures).\r\n\r\nLe \"Blocage\" Social : Le sentiment que l\'ascenseur social est en panne. Même avec un diplôme, l\'accès à un emploi digne semble impossible sans \"piston\".\r\n\r\nLa Pression des Pairs : Le succès apparent de ceux qui ont réussi à partir et qui renvoient une image de richesse (voitures, argent, vêtements) via les réseaux sociaux.', 'uploads/sensibilisation/sensi_admin_1766168466.jpg', 1, '2025-12-19 18:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `storytelling`
--

CREATE TABLE `storytelling` (
  `story_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storytelling`
--

INSERT INTO `storytelling` (`story_id`, `member_id`, `content`, `parent_id`, `created_at`, `status`) VALUES
(27, 22, '\"Voici mon histoire...\"', NULL, '2025-12-19 19:03:09', 'approved'),
(28, 19, 'Quand j’étais encore étudiant à la Faculté de Tunis, je pensais que mon master en économie serait ma clé pour ouvrir les portes du monde. Je me voyais dans un bureau, une cravate droite, rendant ma mère fière dans notre quartier d\'Ettadhamen.', NULL, '2025-12-19 19:06:28', 'approved'),
(29, 18, 'Aujourd\'hui, quand on me demande ce que je fais, je baisse les yeux. Je n\'ai pas atteint l\'autre rive ; les gardes-côtes nous ont interceptés à l\'aube.', 28, '2025-12-19 19:07:11', 'approved'),
(30, 18, 'c\'est triste', 27, '2025-12-19 19:13:49', 'approved'),
(45, 18, 'hfgb', 27, '2026-01-02 20:54:37', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `story_likes`
--

CREATE TABLE `story_likes` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `story_likes`
--

INSERT INTO `story_likes` (`id`, `member_id`, `story_id`, `created_at`) VALUES
(3, 18, 39, '2025-12-27 18:09:08'),
(18, 18, 28, '2026-01-07 16:59:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `brochures`
--
ALTER TABLE `brochures`
  ADD PRIMARY KEY (`brochure_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `brochure_downloads`
--
ALTER TABLE `brochure_downloads`
  ADD PRIMARY KEY (`download_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `brochure_id` (`brochure_id`);

--
-- Indexes for table `encouragements`
--
ALTER TABLE `encouragements`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`opp_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `opportunity_favorites`
--
ALTER TABLE `opportunity_favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `opp_id` (`opp_id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `sensibilisation`
--
ALTER TABLE `sensibilisation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sensibilisation_admin` (`created_by`);

--
-- Indexes for table `storytelling`
--
ALTER TABLE `storytelling`
  ADD PRIMARY KEY (`story_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `story_likes`
--
ALTER TABLE `story_likes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `brochures`
--
ALTER TABLE `brochures`
  MODIFY `brochure_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `brochure_downloads`
--
ALTER TABLE `brochure_downloads`
  MODIFY `download_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `encouragements`
--
ALTER TABLE `encouragements`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `opp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `opportunity_favorites`
--
ALTER TABLE `opportunity_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `sensibilisation`
--
ALTER TABLE `sensibilisation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `storytelling`
--
ALTER TABLE `storytelling`
  MODIFY `story_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `story_likes`
--
ALTER TABLE `story_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brochures`
--
ALTER TABLE `brochures`
  ADD CONSTRAINT `brochures_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `brochure_downloads`
--
ALTER TABLE `brochure_downloads`
  ADD CONSTRAINT `brochure_downloads_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brochure_downloads_ibfk_2` FOREIGN KEY (`brochure_id`) REFERENCES `brochures` (`brochure_id`) ON DELETE CASCADE;

--
-- Constraints for table `encouragements`
--
ALTER TABLE `encouragements`
  ADD CONSTRAINT `encouragements_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `opportunity_favorites`
--
ALTER TABLE `opportunity_favorites`
  ADD CONSTRAINT `opportunity_favorites_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opportunity_favorites_ibfk_2` FOREIGN KEY (`opp_id`) REFERENCES `opportunities` (`opp_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD CONSTRAINT `quiz_responses_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_responses_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `sensibilisation`
--
ALTER TABLE `sensibilisation`
  ADD CONSTRAINT `fk_sensibilisation_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `storytelling`
--
ALTER TABLE `storytelling`
  ADD CONSTRAINT `storytelling_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `storytelling_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `storytelling` (`story_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
